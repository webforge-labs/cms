define(function(require) {

  var ko = require('knockout');

  ko.bindingHandlers.markdownEditor = {
    init: function(element, valueAccessor, allBindings, deprecated, bindingContext) {
      ko.bindingHandlers.textInput.init(element, valueAccessor, allBindings, deprecated, bindingContext);
    }
  };
});