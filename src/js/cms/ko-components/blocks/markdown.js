define(['knockout'], function(ko) {

  var vm = function(params) {
    if (!ko.isObservable(params.block[params.propertyName])) {
      params.block[params.propertyName] = ko.observable(params.block[params.propertyName]);
    }

    this.markdown = params.block[params.propertyName];
  };
      
  var template = '<textarea data-bind="markdownEditor: markdown" class="form-control" rows="14"></textarea>';

  return { viewModel: vm, template: template };

});