define(['knockout'], function(ko) {

  var vm = function(params) {

    if (!ko.isObservable(params.data.markdown)) {
      params.data.markdown = ko.observable(params.data.markdown);
    }

    this.markdown = params.data.markdown;
  };
      
  var template = '<textarea data-bind="markdownEditor: markdown" class="form-control" rows="14"></textarea>';

  return { viewModel: vm, template: template };

});