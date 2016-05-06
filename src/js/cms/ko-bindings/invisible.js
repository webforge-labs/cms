define(['knockout', 'jquery'], function(ko, $) {
  /* displays an element after it was initial hidden with .invisible */
  ko.bindingHandlers.invisible = {
    update: function (element, valueAccessor) {
      var value = ko.utils.unwrapObservable(valueAccessor());
      var $element = $(element);
      if (!value) {
        $element.removeClass('invisible');
        $element.show();
      } 
    }
  };
});