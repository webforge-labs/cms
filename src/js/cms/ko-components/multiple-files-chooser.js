define(['knockout', 'knockout-collection', 'knockout-dragdrop', 'text!./multiple-files-chooser.html', 'cms/modules/dispatcher'], function(ko, KnockoutCollection, koDragdrop, htmlString, dispatcher) {

  var filesChooser = function(params) {
    var that = this;

    var filesObservableArray = params.model[params.name];

    that.files = new KnockoutCollection(filesObservableArray, {key: 'key', reference: true});
    that.processing = ko.observable(false);
    that.isLoading = ko.observable(false);

    that.chooseFiles = function() {
      require(['cms/modules/main'], function(main) {
        main.openFileManager({
          success: function(files) {
            ko.utils.arrayForEach(files, function(file) {
              that.files.add(file);
            });
          },
          spinner: that.isLoading,
          choosingMode: true
        });
      });
    };

    that.addedFiles = ko.computed(function() {
      return ko.utils.arrayFilter(that.files.toArray(), function(file) {
        return ko.unwrap(file.isExisting);
      });
    });

    that.removeFromAddedFiles = function(file) {
      that.files.remove(file);
    };

    that.dragStart = function(item) {
    };
    that.dragEnd = function(item) {
    };

    that.reorder = function(event, dragData, zoneData) {
      if (dragData !== zoneData.item) {
        var sortables = filesObservableArray;
        var zoneDataIndex = sortables.indexOf(zoneData.item);
        sortables.remove(dragData);
        sortables.splice(zoneDataIndex, 0, dragData);
      }
    };

  };

  return { viewModel: filesChooser, template: htmlString };
});