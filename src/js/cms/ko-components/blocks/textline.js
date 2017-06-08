define(['knockout'], function(ko) {

  var vm = function(params) {
    if (!ko.isObservable(params.block[params.propertyName])) {
      console.log('created', params.propertyName, params.block);
      params.block[params.propertyName] = ko.observable(params.block[params.propertyName]);
    }

    this.text = params.block[params.propertyName];
    this.placeholder = params.placeholder || "";
  };

  var template = '<input type="text" data-bind="value: text, attr: { placeholder: placeholder }" class="form-control">';

  return { viewModel: vm, template: template };

});