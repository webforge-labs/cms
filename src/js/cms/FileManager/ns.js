define(['knockout', 'knockout-collection', 'knockout-mapping', 'lodash', 'cms/modules/ui', 'cms/modules/dropbox-chooser', 'cms/modules/dispatcher', 'amplify', 'bluebird'], function(ko, KnockoutCollection, koMapping, _, ui, Dropbox, dispatcher, amplify) {
   var Promise = require("bluebird");

   var FileManager = {};

   FileManager.Sync = function() {
     var that = this;

     that.concurrentFiles = ko.observable(2); // the number of files per request
     that.concurrentConnections = ko.observable(4); // the number of requests simultanously

     this.removeFile = function(item) {
       that.batch.push(ko.unwrap(item.key));
     };

     this.beginRemoveBatch = function() {
       that.batch = [];
     };

     this.commitRemoveBatch = function(processing) {
       var d = $.Deferred();
       if (that.batch.length) {
         processing(true);
         dispatcher.send('DELETE', '/cms/media', { keys: that.batch }, 'json')
          .done(function(response) {
             processing(false);
             d.resolve(response);
           })
          .fail(function(err, response) {
             processing(false);
             amplify.publish('cms.ajax.error', response);
             d.reject(err, response);
          });
       }

       return d.promise();
     };

     this.uploadFromDropbox = function(ci, files, processing, progress, afterwards) {
       var path = ci.path();

       processing(true);
       progress(0);

       var warnings = [];

       Promise.map(_.chunk(files, that.concurrentFiles()), function(chunkOfFiles) {
         var sendPromise = dispatcher.sendPromised('POST', '/cms/media/dropbox', { dropboxFiles: chunkOfFiles, path: path }, 'json');

         sendPromise.reflect().then(function(inspection) {
          if (inspection.isFulfilled()) {
            var response = inspection.value();

            if (response.body && response.body.warnings && response.body.warnings.length) {
              warnings = _.concat(warnings, response.body.warnings);
            }

            progress(progress()+chunkOfFiles.length);
          }
         });

         return sendPromise;

       }, {concurrency: that.concurrentConnections()}).then(function() {
         return dispatcher.sendPromised('GET', '/cms/media', undefined, 'json');
       })
       .then(function(response) {
         processing(false);
         return afterwards(response, warnings);
       })
       .catch(function(fault) {
         processing(false);

         if (fault.response) {
           amplify.publish('cms.ajax.error', fault.response);
         } else {
           throw fault;
         }
       });
     }
   };

   FileManager.Item = function(data, parentItem) {
     var that = this;

     that.isDragging = ko.observable(false);
     that.type = ko.observable('file');
     that.unsynced = ko.observable(false);
     that.items = ko.observableArray([]);

     var mapping = {
       items: {
         create: function(options) {
           return new FileManager.Item(options.data, options.parent);
         },
         arrayChanged: function(event, item) {
           if (event === 'deleted') {
             amplify.publish('fileManager.deleted', item);
           }
         },
         key: function(data) {
           return ko.unwrap(data.key);
         }
       }
     };

     koMapping.fromJS(data, mapping, that);

     this.label = ko.computed(function() {
       return that.name();
     });

     this.isRoot = ko.computed(function() {
       return that.type() === 'ROOT';
     });

     this.isFile = ko.computed(function() {
       return that.type() === 'file';
     });

     this.isImage = ko.computed(function() {
       if (!that.isFile()) return false;

       var mime = ko.unwrap(that.mimeType);

       return mime && mime.indexOf('image/') === 0;
     });

     this.isDirectory = ko.computed(function() {
       return that.type() === 'directory' || that.isRoot();
     });

     this.parentItem = parentItem;

     this.hasItems = ko.computed(function() {
       return that.items && that.items().length > 0;
     });

     this.addDirectory = function(item) {
       that.items.push(item);
     };

     this.addFile = function(item) {
       that.items.push(item);
     };

     // returns the path as string seperated with / starting with /
     this.path = ko.computed(function() {
       var pathItem = that, path = '';

       while (pathItem && !pathItem.isRoot()) {
         path = pathItem.name()+'/'+path;

         pathItem = pathItem.parentItem;
       }

       if (!that.isDirectory()) {
         path = path.substr(0, path.length-1);
       }

       path = '/'+path;

       return path;
     });

     this.key = ko.computed(function() {
       return that.path().substr(1);
     });
   };

   FileManager.Manager = function(data) {
     var that = this;

     this.sync = new FileManager.Sync();
     this.breadcrumbs = ko.observableArray([]);
     this.selection =  ko.observableArray([]); // selection on one page
     this.chosenFiles = new KnockoutCollection([], {key: 'path'});
     this.processing = ko.observable(false);
     this.error = ko.observable();
     this.filesProgress = ko.observable(0);
     this.filesTotal = ko.observable(0);
     this.isInChoosingMode = ko.observable(true);

     var mapping = {
       root: {
         create: function(options) {
           return new FileManager.Item(options.data, null);
         },
         key: function(data) {
           return ko.unwrap(data.path);
         }
       }
     };

     koMapping.fromJS(data, mapping, that);

     this.currentItem = ko.observable();

     this.setCurrentItem = function(item) {
       that.currentItem(item);

       that.selection.removeAll();

       var bcItem = item, breadcrumbs = [];
       while (!bcItem.isRoot()) {
         breadcrumbs.unshift(bcItem);

         bcItem = bcItem.parentItem;
       }

       breadcrumbs.unshift(that.root);

       that.breadcrumbs(breadcrumbs);
     };

     this.setCurrentItem(that.root);

     this.clickItem = function(item) {
       if (item.isDirectory()) {
         that.setCurrentItem(item);
       } else {
         alert('download: '+item.name());
       }
     };

     this.hasItems = ko.computed(function() {
       var ci = that.currentItem();

       return ci && ci.hasItems();
     });


     this.sortedItems = ko.computed(function() {
       var order = 'asc';

       return _.orderBy(
           that.currentItem().items(), 
           // first level: directories before files
           function(item) {
             return item.isDirectory() ? 0 : 1;
           },
           // second level: alphabetically by name
           function(item) {
             return item.name();
           },
           ['asc', order]
         );
     });

     this.removeItem = function(item) {
       if (!item.unsynced()) {
         if (item.isDirectory()) {
           ko.utils.arrayForEach(item.items(), function(childItem) {
             that.removeItem(childItem);
           });
         } else {
           that.sync.removeFile(item);
           that.removeFromChosen(item);

           if (that.currentItem() === item) {
             that.setCurrentItem(item.parentItem);
           }
         }
       }
     };

     this.removeItems = function() {
       var selection = that.selection().slice();

       if (selection.length) {
         that.sync.beginRemoveBatch();

         ko.utils.arrayForEach(selection, function(item) {
           that.removeItem(item);
         });

         that.sync.commitRemoveBatch(that.processing)
           .done(function(response) {
             that.refreshData(response.body);
           });
       }
     };

     this.moveItems = function() {
       var selection = that.selection();

       if (selection.length) {
         console.log(selection);
       }
     };

     this.newFolder = function() {
       ui.prompt("Wie soll der neue Ordner heißen?").done(function(name) {
         if (name != "") {
           name = name.replace(/ä/g, 'ae').replace(/ö/g,'oe').replace(/ü/g, 'ue').replace(/ß/g, 'ss');
           name = name.replace(/[,/\\]/g, '_');
           name = _.deburr(name);
           name = _.trim(name);

           var item;
           that.currentItem().addDirectory(
             item = new FileManager.Item({
               name: name,
               type: 'directory',
               items: [],
               unsynced: true
             }, that.currentItem())
           );

           that.setCurrentItem(item);
         }
       });
     };

     this.hasSelection = ko.computed(function () {
       return that.selection().length > 0;
     });

     this.hasChosenFiles = ko.computed(function () {
       return that.chosenFiles.toArray().length > 0;
     });

     this.trackSelection = ko.computed(function() {
       ko.utils.arrayForEach(that.selection(), function(item) {
         if (item.isFile()) {
           that.chosenFiles.add(item);
         }
       });
     });

     this.selectAll = function() {
       var ci = that.currentItem();

       if (ci) {
         ko.utils.arrayForEach(ci.items(), function(item) {
           if (item.isFile()) {
             that.selection.push(item);
           }
         });
       }
     };

     this.removeFromChosen = function(item) {
       that.selection.remove(item);
       that.chosenFiles.remove(item);
     };

     this.addFilesFromDropbox = function() {
       Dropbox.choose({
         success: function(files) {
           var ci = that.currentItem();

           that.filesTotal(files.length);
           that.sync.uploadFromDropbox(ci, files, that.processing, that.filesProgress, function(response, warnings) {
             that.refreshData(response.body);

             if (warnings.length) {
               that.error({message: _.join(warnings, "<br>\n")});
             }
           });
          },
          linkType: "direct",
          multiselect: true,
 
          // file types, such as "video" or "images" in the list. For more information,
          // see File types below. By default, all extensions are allowed.
          extensions: []
       });
     };

     this.refreshData = function(data) {
       koMapping.fromJS(data, mapping, that);
     };

     amplify.subscribe('fileManager.deleted', function(item) {
       var ci = that.currentItem();
       if (ci && item === ci) {
         that.setCurrentItem(ci.parentItem);
       }
     });

     this.confirmChosenFiles = function () {
       var chosen = that.chosenFiles.toArray();
       if (chosen.length && that.options && that.options.success) {
         that.options.success.call(that, chosen);
       }
     };

     this.reset = function(options) {
       that.options = options;
       that.selection.removeAll();
       that.chosenFiles.removeAll();
     }
   };

   return FileManager;
});