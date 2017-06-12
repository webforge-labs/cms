define(function(require) {
  var ko = require('knockout');
  var koMapping = require('knockout-mapping');
  var _ = require('lodash');
  var KnockoutCollection = require('knockout-collection');
  var htmlString = require('text!./content-manager.html');
  var BlockType = require('cms/ContentManager/BlockType');
  var config = require('admin/config');

  require('cms/ko/BlocksComponentLoader');
  require('knockout-dragdrop');
  require('cms/ko-bindings/uk-sortable');
  require('cms/ko-bindings/markdown-editor');
  require('bootstrap/dropdown');

  var generateUUID = function() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
      var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
      return v.toString(16);
    });
  };

  var internalComponents = ['markdown', 'textline'];

  var contentManager = function(params) {
    var that = this;

    var contents = params.model[params.name];

    var blocks = this.initBlocks(contents);

    that.sortedBlocks = ko.computed(function() {
      return ko.utils.arrayFilter(blocks.toArray(), function(file) {
        return true;
      });
    });

    that.reorder = function(item, newIndex, type) {
      contents.blocks.remove(item);
      contents.blocks.splice(newIndex, 0, item);
    };

    this.createBlockType = function(type) {
      type.component = type.component || type.name;
      type.params = type.params || {};

      // expand namespace
      if (type.component.indexOf(':') === -1) {
        if (_.includes(internalComponents, type.component)) {
          type.component = 'cms:'+type.component;
        } else {
          type.component = 'admin:'+type.component;
        }
      }

      if (_.isArray(type.compounds)) {
        type.component = 'cms:compound';
        type.params.compounds = _.map(type.compounds, function(compoundType) {
          return that.createBlockType(compoundType);
        });
      }

      return new BlockType(type.name, type.label, type.component, that, type);
    };

    that.blockTypes = new KnockoutCollection(ko.observableArray([]), { key: 'name', reference: true });

    _.each(config.contentManager.blockTypes, function(type) {
      that.blockTypes.add(that.createBlockType(type));
    });

    that.availableBlockTypes = ko.computed(function() {
      return that.blockTypes.toArray();
    });

    that.addBlock = function(block) {
      block.uuid = generateUUID();
      blocks.add(block);
    }

    that.removeBlock = function(block) {
      blocks.remove(block);
    }

    this.createComponentDefinition = function(block) {
      var blockType = that.blockTypes.get(block.type());

      return blockType.createComponentDefinition(block);
    };
  };

  contentManager.prototype.initBlocks = function(contents) {
    if (ko.isObservable(contents.blocks)) {
      ko.utils.arrayForEach(contents.blocks, this.addBlockUUID);
    } else {
      contents.blocks = ko.observableArray(ko.utils.arrayMap(contents.blocks, this.addBlockUUID));
    }

    return new KnockoutCollection(contents.blocks, { key: 'uuid', reference: true});
  };

  contentManager.prototype.createBlockUUID = function(block) {
    if (!block.uuid) {
      block.uuid = ko.observable();
    }

    if (!ko.unwrap(block.uuid)) {
      block.uuid(generateUUID());
    }

    return block;
  };

  return { viewModel: contentManager, template: htmlString };
});