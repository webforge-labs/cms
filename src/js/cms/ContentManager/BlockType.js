define(['knockout'], function(ko) {

  return function(name, label, contentManager, options) {
    var that = this;
    that.label = label;
    that.name = name;
    that.component = {
      name: options.component || name,
      params: options.params || {}
    };

    if (!options) options = {};

    if (!options.icon) {
      options.icon = 'plus-square';
    }

    that.hasIcon = true;
    that.iconClass = 'fa fa-fw fa-'+options.icon;

    that.add = function() {
      var blockData = {
        type: ko.observable(name),
        label: label // can be customized from user for this specific block
      };

      contentManager.addBlock(blockData);
    };
  };

});