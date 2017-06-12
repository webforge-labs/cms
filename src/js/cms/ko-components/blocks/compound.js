define(function(require) {
  var ko = require('knockout');

  var vm = function CompoundComponent(params) {
    var that = this;

    that.compounds = params.compounds;
    that.block = params.block;
  };

  return { viewModel: vm, template: require('text!./compound.html') };

});