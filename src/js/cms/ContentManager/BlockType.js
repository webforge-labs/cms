define(['knockout'], function(ko) {

  return function(name, label, componentName, contentManager, options) {
    var that = this;

    if (!options) options = {};
    if (!options.icon) {
      options.icon = 'plus-square';
    }

    if (!options.params.propertyName) {
      options.params.propertyName = name;
    }

    that.label = label;
    that.name = name;
    that.component = {
      name: componentName,
      params: options.params
    };

    that.hasIcon = true;
    that.iconClass = 'fa fa-fw fa-'+options.icon;

    that.add = function(options) {
      var blockData = {
        type: ko.observable(name),
        label: ko.observable(label) // can be customized from user for this specific block
      };

      contentManager.addBlock(blockData, options);
    };

    that.addAfter = function(block) {
      return that.add({ position: 'after-block', block: block});
    }

    that.getPropertyName = function() {
      return that.component.params.propertyName;
    };

    that.createComponentDefinition = function(block) {

      var initHelper = function(componentVM, spec) {
        var propertyName = that.getPropertyName();

        if ((spec.hasOwnProperty('defaultValue') || options.hasOwnProperty('default')) && !block.hasOwnProperty(propertyName)) {
          block[propertyName] = spec.hasOwnProperty('defaultValue') ? spec.defaultValue : options.default;
        }

        if (!ko.isObservable(block[propertyName])) {
          block[propertyName] = _.isArray(block[propertyName]) ? ko.observableArray(block[propertyName]) : ko.observable(block[propertyName]);
        }

        componentVM.block = block;
        componentVM[spec.property] = block[propertyName];

        componentVM.options = {};

        ko.utils.objectForEach(spec.options || {}, function(prop, value) {
          componentVM.options[prop] = that.component.params.hasOwnProperty(prop) ? that.component.params[prop] : value;
        });
      };

      return {
        name: that.component.name,
        params: _.extend({}, that.component.params, {
          contentManager: contentManager,
          blockType: that,
          block: block,
          init: initHelper
        })
      };
    };
  };

});