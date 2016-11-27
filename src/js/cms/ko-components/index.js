define(['knockout'], function(ko) {

  var components = [
    'multiple-files-chooser',
    'content-manager'
  ];

  ko.utils.arrayForEach(components, function(componentName) {
    ko.components.register(componentName, { require: 'cms/ko-components/'+componentName });
  });

  ko.components.register('block-markdown', { require: 'cms/ko-components/blocks/markdown' });

});