define(['knockout'], function(ko) {

  var vm = function(params) {

    params.init(this, {
      property: 'value',
      options: {
        rows: 14
      }
    });

  };
      
  var template = '<textarea data-bind="markdownEditor: value, attr: options" class="form-control"></textarea>';

  return { viewModel: vm, template: template };

});