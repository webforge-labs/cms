define(['knockout', 'jquery'], function(ko, $) {
  /* displays the loaded element without flashing the non-loaded js (needs .invisible { visibility: invisible }) */
  ko.bindingHandlers.withVisible = {
    init: function(element, valueAccessor, allBindings, viewModel, bindingContext) {
      var ret = ko.bindingHandlers['with'].init(element, valueAccessor, allBindings, viewModel, bindingContext);
      $(element).removeClass('invisible');

      return ret;
    }
  };
});