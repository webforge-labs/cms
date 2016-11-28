define(['knockout'], function(ko) {

  var vm = function(params) {
    if (!ko.isObservable(params.val.markdown)) {
      params.val.markdown = ko.observable(params.val.markdown);
    }

    this.markdown = params.val.markdown;
  };
      
  var template = '<textarea data-bind="markdownEditor: markdown" class="form-control" rows="14"></textarea>';

  return { viewModel: vm, template: template };

});