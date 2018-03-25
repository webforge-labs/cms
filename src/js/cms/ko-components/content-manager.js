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
  require('cms/ko-bindings/rubaxa-sortable');
  require('cms/ko-bindings/markdown-editor');
  require('bootstrap/dropdown');

  var generateUUID = function() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
      var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
      return v.toString(16);
    });
  };

  var internalComponents = require('cms/ko-components/internals-index');

  var contentManager = function(params) {
    var that = this;

    that.model = params.model;

    var contents = that.model[params.name];

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

    that.addBlock = function(block, options) {
      if (!options) {
        options = { position: 'end' };
      }

      that.createBlockProperties(block);

      if (options.position === 'after-block') {
        /* TODO: refactor this into knockout-collection */
        var blocksArr = blocks.toArray();
        var position = blocks.items.indexOf(options.block);
        blocks.items.splice(Math.max(0, position+1), 0, block);
        options.block.collapsed(true);
      } else {
        blocks.add(block);
      }
      block.collapsed(false);
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
      ko.utils.arrayForEach(ko.unwrap(contents.blocks), this.createBlockProperties);
    } else {
      contents.blocks = ko.observableArray(ko.utils.arrayMap(contents.blocks, this.createBlockProperties));
    }

    return new KnockoutCollection(contents.blocks, { key: 'uuid', reference: true});
  };

  contentManager.prototype.createBlockProperties = function(block) {
    if (!block.uuid) {
      block.uuid = ko.observable();
    }

    if (!block.collapsed) {
      block.collapsed = ko.observable(true);
    }

    if (!ko.unwrap(block.uuid)) {
      block.uuid(generateUUID());
    }
    // allow to inject a computedLabel from the component
    var originalLabel = block.label;
    block.computedLabel = ko.observable();
    block.label = ko.computed(function() {
      var computed = this.computedLabel();

      if (computed) {
        return computed();
      } else {
        return ko.unwrap(originalLabel);
      }
    }, block);

    return block;
  };

  return { viewModel: contentManager, template: htmlString };
});