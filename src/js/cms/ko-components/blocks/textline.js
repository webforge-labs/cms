define(['knockout'], function(ko) {

  var vm = function(params) {
    this.block = params.block;

    if (!ko.isObservable(this.block[params.propertyName])) {
      this.block[params.propertyName] = ko.observable(this.block[params.propertyName]);
    }

    this.text = params.block[params.propertyName];
    this.placeholder = params.placeholder || "";
  };



  var template = '<input type="text" data-bind="value: text, attr: { placeholder: placeholder }" class="form-control">';

  return { viewModel: vm, template: template };

});