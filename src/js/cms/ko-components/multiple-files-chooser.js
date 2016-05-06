define(['knockout', 'knockout-collection', 'text!./multiple-files-chooser.html', 'cms/modules/dispatcher'], function(ko, KnockoutCollection, htmlString, dispatcher) {

  var filesChooser = function(params) {
    var that = this;

    var filesObservableArray = params.model[params.name];

    that.files = new KnockoutCollection(filesObservableArray, {key: 'key', reference: true});
    that.processing = ko.observable(false);

    that.chooseFiles = function() {
      require(['cms/modules/main'], function(main) {
        main.openFileManager({
          success: function(files) {
            ko.utils.arrayForEach(files, function(file) {
              that.files.add(file);
            });
          }
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
  };

  return { viewModel: filesChooser, template: htmlString };
});