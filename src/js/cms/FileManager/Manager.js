define(function(require) {
  var ko = require('knockout');
  var KnockoutCollection = require('knockout-collection');
  var koMapping = require('knockout-mapping');
  var _ = require('lodash');
  var ui = require('cms/modules/ui');
  var urlify = require('urlify');
  var Dropbox = require('cms/modules/dropbox-chooser');
  var FileManagerSync = require('./Sync');
  var FileManagerItem = require('./Item');
  var FileManagerFolderPicker = require('./FolderPicker');
  var Promise = require('Bluebird');
  require('bootstrap-notify');

  return function FileManagerManager(data) {
    var that = this;

    this.sync = new FileManagerSync();
    this.breadcrumbs = ko.observableArray([]);
    this.selection =  ko.observableArray([]); // selection on one page
    this.chosenFiles = new KnockoutCollection([], {key: 'path'});
    this.processing = ko.observable(false);
    this.view = ko.observable("navigator");
    this.error = ko.observable();
    this.filesProgress = ko.observable(0);
    this.filesTotal = ko.observable(0);
    this.isInChoosingMode = ko.observable(true);
    this.folderPicker = new FileManagerFolderPicker(that);

    var mapping = {
     root: {
       create: function(options) {
         return new FileManagerItem(options.data, null);
       },
       key: function(data) {
         return ko.unwrap(data.path);
       }
     }
    };

    koMapping.fromJS(data, mapping, that);

    this.currentItem = ko.observable();

    this.selection.subscribe(function(changes) {
       _.each(changes, function(change) {
         if (change.status === 'added') {
           change.value.selected(true);
         } else if (change.status === 'deleted') {
           change.value.selected(false);
         }
 
       });
    }, null, "arrayChange");

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
      } 
      // we have the click function free for items (e.g. open details?)
    };

    this.hasItems = ko.computed(function() {
      var ci = that.currentItem();

      return ci && ci.hasItems();
    });

    this.path = ko.computed(function() {
      var ci = that.currentItem();

      return ci && ci.path();
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
   
    var moveFiles;
    this.moveItems = function() {
      var selection = that.selection();
 
      if (selection.length) {
        moveFiles = [];
        var items = [];
        ko.utils.arrayForEach(selection, function(item) {
          items.push(item);
          moveFiles.push(item.path());
        });

        that.folderPicker.reset(items);
        that.view("folder-picker");
      }
    };

    this.confirmMoveItems = function() {
      if (that.folderPicker.hasValidDirectory()) {
        var targetDir = that.folderPicker.selected();

        that.sync.moveFiles(moveFiles, targetDir.path(), function(response) {
          $.notify({
            message: "Okay, die Dateien habe ich verschoben."
          },{
            type: 'success'
          });

          that.refreshData(response.body);
        });
      }
    }

    this.createDirectoryIn = function(directory) {
      return Promise.resolve(ui.prompt("Wie soll der neue Ordner heißen?").then(function(name) {
        if (name != "") {
          name = _.trim(name);
          name = urlify(name, 120, false);
 
          var item;
          directory.addDirectory(
            item = new FileManagerItem({
              name: name,
              type: 'directory',
              items: [],
              unsynced: true
            }, directory)
          );

          return item;
        }
      }));
    }

    this.newFolder = function() {
      that.createDirectoryIn(that.currentItem())
        .then(function(item) {
          that.setCurrentItem(item);
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
      if (options.hasOwnProperty('choosingMode')) {
        that.isInChoosingMode(options.choosingMode);
      }
      that.selection.removeAll();
      that.chosenFiles.removeAll();
    }

    this.toNavigator = function() {
      that.view("navigator");
    }
  };
});