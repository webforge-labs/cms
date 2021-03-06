define(['knockout'], function(ko) {

  return function FileManagerFolderPicker(fileManager) {
    var that = this;

    that.selected = ko.observable();
    that.itemsToMove = ko.observable();

    this.hasDirectory = ko.computed(function() {
      return !!that.selected();
    });

    this.hasValidDirectory = ko.computed(function() {
      var selected = that.selected();

      if (!selected) return false;

      var targetPath = selected.path();
      // we cannot move an directory deeper into it's own sub-directories
      // files can be moved to anywhere
      var movingAllowed = true;
      ko.utils.arrayForEach(that.itemsToMove(), function(item) {
        if (item.isFile()) {
          if (targetPath == item.parentItem.path()) {
            movingAllowed = false;
            return false;
          }
        } else { // isDirectory
          if (targetPath === item.path() || targetPath == item.parentItem.path() || that.isSubdirectory(item.path(), targetPath)) {
            movingAllowed = false;
            return false;
          }
        }
      });

      return movingAllowed;
    });

    this.isSubdirectory = function(directory, subDirectory) {
      return _.startsWith(subDirectory, directory);
    };

    this.selectDirectory = function(directory) {
      that.selected(directory);
    };

    this.newDirectory = function() {
      var selected = that.selected();
      if (!selected) return false;

      fileManager.createDirectoryIn(selected).then(function(directory) {
        that.selected(directory);
      });
    };

    this.isActive = function(dir) {
      return ko.computed(function() {
        var selected = that.selected();
        if (!selected) return false;

        return selected.path() === dir.path();
      });
    };

    this.reset = function(itemsToMove) {
      that.itemsToMove(itemsToMove);
      that.selected(undefined);
    };

  };

});