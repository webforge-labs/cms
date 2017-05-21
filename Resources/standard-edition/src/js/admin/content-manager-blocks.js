define(['require', 'knockout', './blocks/polaroid-stripe-horizontal', './blocks/polaroid-stripe-vertical', './blocks/interview-qa'], function(require, ko) {

  var blocks = [
    'polaroid-stripe-horizontal',
    'polaroid-stripe-vertical',
    'interview-qa'
  ];

  ko.utils.arrayForEach(blocks, function(name) {
    ko.components.register(name, require('admin/blocks/'+name));
  });

});