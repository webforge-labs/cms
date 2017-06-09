define(function(require) {

  var ko = require('knockout');

  require('cms/ko/BlocksComponentLoader']); // registers itself

  var components = [
    'multiple-files-chooser',
    'content-manager'
  ];

  ko.utils.arrayForEach(components, function(componentName) {
    ko.components.register(componentName, { require: 'cms/ko-components/'+componentName });
  });

});