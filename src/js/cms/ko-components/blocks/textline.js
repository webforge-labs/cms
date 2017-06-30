define(['knockout'], function(ko) {

  var vm = function(params) {

    params.init(this, {
      property: 'text',
      options: {
        placeholder: ""
      }
    });

  };

  var template = '<input type="text" data-bind="value: text, attr: { placeholder: options.placeholder }" class="form-control">';

  return { viewModel: vm, template: template };

});