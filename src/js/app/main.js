define(['jquery', 'knockout', './MainModel', './TabModel', 'bootstrap/button', 'bootstrap/transition', 'bootstrap/collapse', 'bootstrap/dropdown'], function($, ko, Main, Tab) {

  ko.bindingHandlers.moment = {
    init: function(element, valueAccessor) {
      return ko.bindingHandlers.text.init(element, valueAccessor);
    },
    update: function(element, valueAccessor, allBindings) {
      var bindings = allBindings();

      var dateValueAccessor = function() {
        var observable = valueAccessor();
        var m = ko.unwrap(observable);

        if (moment.isMoment(m)) {
          var fmt = bindings['momentFormat'] || 'DD.MM.YYYY HH:mm:ss';

          if (fmt == 'fromNow') {
            return m.fromNow();
          } else {
            return m.format(fmt);
          }
        } else {
          return '';
        }
      };

      return ko.bindingHandlers.text.update(element, dateValueAccessor);
    }
  };  
  ko.virtualElements.allowedBindings.moment = true;

  ko.bindingHandlers.cmsTab = {
    init: function(element, valueAccessor, allBindingsAccessor, viewModel, context) {
      var wrappedValueAccessor = function() {
        return function(data, e) {
          e.preventDefault();
          e.stopImmediatePropagation();

          var tab = new Tab(valueAccessor());

          viewModel.tabs.open.call(viewModel, tab, e);
        };
      };

      // use original click binding
      ko.bindingHandlers.click.init(element, wrappedValueAccessor, allBindingsAccessor, viewModel, context);
   },
   update: ko.bindingHandlers.click.update
  };

  return function(data) {
    var main = new Main(data);

    ko.applyBindings(main);
  };
});