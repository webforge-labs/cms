define(['knockout', 'knockout-mapping', 'lodash', 'cms/modules/ui'], function(ko, koMapping, lodash, ui) {
   var FileManager = {};

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
   };

   FileManager.Manager = function(data) {
     var that = this;

     this.breadcrumbs = ko.observableArray([]);

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
         alert('download: '+item.label());
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
           // second level: alphabetically by label
           function(item) {
             return item.label();
           },
           ['asc', order]
         );
     });

     this.newFolder = function() {
       ui.prompt("Wie soll der neue Ordner heißen?").done(function(name) {
         that.currentItem().addDirectory(
           new FileManager.Item({
             label: name,
             type: 'directory',
             items: []
           }, that.currentItem())
         );
       });
     };

     this.bindTo = function($element) {
       ko.applyBindings(that, $element.get(0));
     };
   };

   return FileManager;
});