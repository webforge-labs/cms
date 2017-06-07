define(['knockout', 'knockout-mapping', 'text!./interview-qa.html'], function(ko, koMapping, htmlTemplate) {

  var vm = function(params) {
    var that = this;

    ko.utils.objectForEach({ question: '', answer: ''}, function(key, defaultValue) {
      if (!params.val.hasOwnProperty(key)) {
        params.val[key] = ko.observable(defaultValue);
      } else if (!ko.isObservable(params.val[key])) {
        ko.observable(params.val[key]);
      }

      that[key] = params.val[key];
    });
  };

  return { viewModel: vm, template: htmlTemplate };

});