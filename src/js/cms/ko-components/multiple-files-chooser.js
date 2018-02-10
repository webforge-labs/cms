 define(['require', 'knockout', 'jquery', 'knockout-collection', 'knockout-dragdrop', 'text!./multiple-files-chooser.html', 'cms/modules/dispatcher', 'cms/ko-bindings/rubaxa-sortable'], function(require, ko, $, KnockoutCollection, koDragdrop, htmlString, dispatcher) {

  var filesChooser = function(params) {
    var that = this;

    var filesObservableArray = params.model[params.name];

    that.files = new KnockoutCollection(filesObservableArray, {key: 'key', reference: true});
    that.processing = ko.observable(false);
    that.isLoading = ko.observable(false);
    that.accept = ko.observable(params.accept);

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

    that.uploadPath = ko.computed(function() {
      return params.generateUploadPath.call(null);
    });

    that.addUploadedFiles = function(files) {
      ko.utils.arrayForEach(files, function(exportedFile) {
        that.files.add(exportedFile);
      });
    }

    that.reorder = function(item, newIndex) {
      var oldIndex = filesObservableArray.indexOf(item);
      //console.log('moved from ', oldIndex, 'to ', newIndex);
      
      filesObservableArray.remove(item);
      filesObservableArray.splice(newIndex, 0, item);
    };
  };

  return { viewModel: filesChooser, template: htmlString };
});