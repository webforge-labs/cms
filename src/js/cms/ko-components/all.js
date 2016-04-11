define(['knockout'], function(ko) {

  var components = [
    'multiple-files-chooser'
  ];

  ko.utils.arrayForEach(components, function(componentName) {
    ko.components.register(componentName, { require: 'cms/ko-components/'+componentName });
  });

});