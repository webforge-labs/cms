define(['knockout', 'jquery'], function(ko, $) {

  // note: this does not work on mobile devices!
  ko.bindingHandlers.dblClick = {
    init: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
      var handlerFunction = valueAccessor();

      $(element).dblclick(function(e) {
        var argsForHandler = ko.utils.makeArray(arguments);
        viewModel = bindingContext['$data'];
        argsForHandler.unshift(viewModel);

        var handlerReturnValue = handlerFunction.apply(viewModel, argsForHandler);
      });
    }
  };
});