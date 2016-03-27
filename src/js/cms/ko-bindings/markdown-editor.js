define(['knockout'], function(ko) {
  // @TODO add pagedown or other markdown editor plugin

  ko.bindingHandlers.markdownEditor = {
    init: function(element, valueAccessor, allBindings, deprecated, bindingContext) {
      /*
      $(element).markdown({
        autofocus:false,
        savable:false,
        resize: ''
        //iconlibrary: 'fa'
      });
      */
      ko.bindingHandlers.value.init(element, valueAccessor, allBindings, deprecated, bindingContext);
    },

    update: function(element, valueAccessor, allBindings, deprecated, bindingContext) {
      ko.bindingHandlers.value.update(element, valueAccessor, allBindings, deprecated, bindingContext);
    }
  };
});