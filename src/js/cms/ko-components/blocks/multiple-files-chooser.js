define(['knockout', 'cms/ko-components/multiple-files-chooser', 'lodash'], function(ko, filesChooser, _) {

  var wrappedFilesChooser = _.clone(filesChooser);

  // we will wrap around filesChooser component, because its already a component
  wrappedFilesChooser.viewModel = {
    createViewModel: function(params, componentInfo) {

      params.init(this, {
        property: 'notused',
        defaultValue: [],
        options: {
          accept: "*/*",
          generateUploadPath: params.contentManager.model.generateUploadPath
        }
      });

      var componentParams = this.options;

      componentParams.name = params.propertyName;
      componentParams.model = params.block;

      return new filesChooser.viewModel(componentParams); // extends the files-chooser component
    }
  };

  return wrappedFilesChooser;

});