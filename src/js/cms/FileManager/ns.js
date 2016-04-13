define(['knockout', 'knockout-mapping', 'lodash', 'cms/modules/ui', 'cms/modules/dropbox-chooser', 'cms/modules/dispatcher', 'amplify'], function(ko, koMapping, lodash, ui, Dropbox, dispatcher, amplify) {
   var FileManager = {};

   FileManager.Sync = function() {
     var that = this;

     this.removeFile = function(item) {
       console.log('dispatching removal of: '+item.name());
     };
   };

   FileManager.Item = function(data, parentItem) {
     var that = this;

     var mapping = {
       items: {
         create: function(options) {
           return new FileManager.Item(options.data, options.parent);
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

       path = '/'+path;

       return path;
     });
   };

   FileManager.Manager = function(data) {
     var that = this;

     this.sync = new FileManager.Sync();
     this.breadcrumbs = ko.observableArray([]);
     this.selection =  ko.observableArray([]);

     var mapping = {
       root: {
         create: function(options) {
           return new FileManager.Item(options.data, null);
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
         }
       }
     };

     this.removeItems = function() {
       var selection = that.selection();
       var items = that.currentItem().items; // reference to koArray

       if (selection.length) {
         ko.utils.arrayForEach(selection, function(item) {
           that.removeItem(item);
           items.remove(item);
         });

         that.selection.removeAll();
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

     this.processing = ko.observable(false);

     this.addFilesFromDropbox = function() {
       Dropbox.choose({
         success: function(files) {
           var ci = that.currentItem();
           var items = [];

           ko.utils.arrayForEach(files, function(dbFile) {

             if (!dbFile.isDir) {
               var item = new FileManager.Item(
                 {
                   name: dbFile.name,
                   type: 'file',
                   unsynced: true
                 },
                 ci
               );

               ci.addFile(item);
               items.push(item);
             }
           });

           that.processing(true);
 
           dispatcher.send('POST', '/cms/media/dropbox', { dropboxFiles: files, path: ci.path() }, 'json')
            .done(function(response) {
               koMapping.fromJS(response.body, mapping, that);
               
               ko.utils.arrayForEach(items, function(item) {
                 item.unsynced(false);
               });

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
          extensions: ['images']
       });
     };

     this.bindTo = function($element) {
       ko.applyBindings(that, $element.get(0));
     };
   };

   return FileManager;
});