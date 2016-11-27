define(['knockout', 'cms/modules/bootstrap-markdown', 'marked'], function(ko, bootstrapMarkdown, marked) {

  ko.bindingHandlers.markdownEditor = {
    init: function(element, valueAccessor, allBindings, deprecated, bindingContext) {
      $(element).markdown({
        autofocus: false,
        savable: false,
        resize: '',
        iconlibrary: 'fa',
        hiddenButtons: ['cmdImage'],
        parser: marked
      });

      ko.bindingHandlers.textInput.init(element, valueAccessor, allBindings, deprecated, bindingContext);
    }
  };
});