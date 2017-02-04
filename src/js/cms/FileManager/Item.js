define(['knockout', 'knockout-mapping', 'amplify', 'lodash'], function(ko, koMapping, amplify, _) {

  return function FileManagerItem(data, parentItem) {
    var that = this;

    that.isDragging = ko.observable(false);
    that.type = ko.observable('file');
    that.unsynced = ko.observable(false);
    that.items = ko.observableArray([]);
    that.selected = ko.observable(false);

    var mapping = {
      items: {
        create: function(options) {
          return new FileManagerItem(options.data, options.parent);
        },
        arrayChanged: function(event, item) {
          if (event === 'deleted') {
            amplify.publish('fileManager.deleted', item);
          }
        },
        key: function(data) {
          return ko.unwrap(data.path);
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

    this.subDirectories = ko.computed(function() {
      if (!that.items) return [];

      return _.filter(that.items(), function(item) {
        return item.isDirectory();
      });
    });

    this.addDirectory = function(item) {
      that.items.push(item);
    };

    this.addFile = function(item) {
     that.items.push(item);
    };

    this.rename = function(newName) {
      that.name(newName);
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
  };
});