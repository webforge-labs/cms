define(['knockout', 'cms/modules/moment'], function(ko, moment) {

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

});