define(['knockout', 'cms/modules/moment', 'cms/modules/bootstrap-datepicker', 'jquery'], function(ko, moment, datepicker, $) {

  ko.bindingHandlers.datePicker = {
    init: function (element, valueAccessor) {
      var $element = $(element);
      var observable = valueAccessor();

      observable.datePicker = ko.computed({
        read: function() {
          var stringValue = observable();
          return moment(stringValue);
        },

        write: function(jsDate) {
          var update = moment(jsDate);
          update.hour(12);
          update.minute(0);
          update.second(0);
          update.millisecond(0);

          observable(update.toISOString());
        }
      });

      // http://bootstrap-datepicker.readthedocs.org/en/latest/
      $element.datepicker({
        todayBtn: false,
        language: "de",
        format: "dd.mm.yyyy",
        calendarWeeks: false,
        autoclose: true,
        immediateUpdates: false,
        keyboardNavigation: false,
        templates: {
          leftArrow: '<i class="fa fa-caret-left"></i>',
          rightArrow: '<i class="fa fa-caret-right"></i>'
        }
      }).on("changeDate", function(e) {
        observable.datePicker(e.date);
      });
    },

    // from js => to "html"
    update: function(element, valueAccessor, allBindings, viewModel, bindingContext) {
      var $element = $(element);
      var observable = valueAccessor();

      if (ko.unwrap(observable)) {
        $element.datepicker('update', observable.datePicker().format('DD.MM.YYYY')); // same as for .datepicker()));
      }
    }
  };
});