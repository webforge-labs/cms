define(['require', 'knockout', 'knockout-collection', 'knockout-dragdrop', 'text!./multiple-files-chooser.html', 'cms/modules/dispatcher', 'cms/ko-bindings/uk-sortable'], function(require, ko, KnockoutCollection, koDragdrop, htmlString, dispatcher) {

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

    that.reorder = function(item, newIndex, type) {
      //var oldIndex = filesObservableArray.indexOf(item);
      //console.log('moved from ', oldIndex, 'to ', newIndex);
      filesObservableArray.remove(item);
      filesObservableArray.splice(newIndex, 0, item);
    };

  };

  return { viewModel: filesChooser, template: htmlString };
});