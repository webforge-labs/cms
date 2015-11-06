define(['jquery', 'knockout', './MainModel', 'socket.io', 'modules/moment', 'bootstrap/button', 'bootstrap/transition', 'bootstrap/collapse'], function($, ko, MainModel, io, moment) {

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

  return function(data) {
    data.connected = false;

    var main = new MainModel({ }, data);
    ko.applyBindings(main);

    var socket = io(data.websocket);

    socket.on('connect', function () {
      main.connected(true);

      socket.on('tickerupdate', function(data) {
        main.tickerUpdate(data);
      });
    });

    socket.on('disconnect', function() {
      main.connected(false);
    });
    socket.on('error', function() {
      main.connected(false);
    });

    socket.on('reconnect', function() {
      main.connected(true);
    });
  };
});