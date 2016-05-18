define(['knockout', 'knockout-collection', 'knockout-mapping', 'lodash', 'cms/modules/ui', 'cms/modules/dropbox-chooser', 'cms/modules/dispatcher', 'amplify'], function(ko, KnockoutCollection, koMapping, lodash, ui, Dropbox, dispatcher, amplify) {
   var FileManager = {};

   FileManager.Sync = function() {
     var that = this;

     this.removeFile = function(item) {
       that.batch.push(ko.unwrap(item.key));
     };

     this.beginRemoveBatch = function() {
       that.batch = [];
     };

     this.commitRemoveBatch = function(processing) {
       if (that.batch.length) {
         processing(true);
         return dispatcher.send('DELETE', '/cms/media', { keys: that.batch }, 'json')
          .done(function(response) {
             processing(false);
           })
          .fail(function(err, response) {
             processing(false);
             amplify.publish('cms.ajax.error', response);
          });
       }
     };
   };

   FileManager.Item = function(data, parentItem) {
     var that = this;

     var mapping = {
       items: {
         create: function(options) {
           return new FileManager.Item(options.data, options.parent);
         },
         key: function(data) {
           return ko.unwrap(data.key);
         }
       }
     };

     koMapping.fromJS(data, mapping, that);

     if (!that.type) {
       that.type = ko.observable('file');
     }

     if (!that.unsynced) {
       that.unsynced = ko.observable(false);
     }

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
             koMapping.fromJS(response.body, mapping, that);
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
         that.currentItem().addDirectory(
           new FileManager.Item({
             name: name,
             type: 'directory',
             items: [],
             unsynced: true
           }, that.currentItem())
         );
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

           that.processing(true);
 
           dispatcher.send('POST', '/cms/media/dropbox', { dropboxFiles: files, path: ci.path() }, 'json')
            .done(function(response) {
               koMapping.fromJS(response.body, mapping, that);
               that.processing(false);
             })
            .fail(function(err, response) {
               that.processing(false);
               amplify.publish('cms.ajax.error', response);
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

     };

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