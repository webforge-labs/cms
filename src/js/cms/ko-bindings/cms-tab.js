define(['knockout', '../TabModel', 'amplify'], function(ko, Tab, amplify) {
  ko.bindingHandlers.cmsTab = {
    init: function(element, valueAccessor, allBindingsAccessor, viewModel, context) {

      var wrappedValueAccessor = function() {
        return function(data, e) {
          e.preventDefault();
          //e.stopImmediatePropagation(); // does not work on zombie, with jquery 2.x

          var tab = new Tab(valueAccessor());
          var cmsMain = context.$root;

          cmsMain.tabs.open.call(cmsMain, tab, e);

          if (allBindingsAccessor().cmsTabCloseNav) {
            amplify.publish('cms.close-the-nav');
          }
        };
      };

      // use original click binding
      ko.bindingHandlers.click.init(element, wrappedValueAccessor, allBindingsAccessor, viewModel, context);
   },
   update: ko.bindingHandlers.click.update
  };
});