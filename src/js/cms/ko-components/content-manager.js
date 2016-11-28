define(['require', 'knockout', 'knockout-mapping', 'lodash', 'knockout-collection', 'knockout-dragdrop', 'text!./content-manager.html', 'cms/ContentManager/BlockType', 'cms/modules/dispatcher', 'cms/ko-bindings/uk-sortable', 'cms/ko-bindings/markdown-editor'], function(require, ko, koMapping, _, KnockoutCollection, koDragdrop, htmlString, BlockType, dispatcher, sortableBinding, markdownEditorBinding) {

  var config = require('admin/config');
  var generateUUID = function() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
      var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
      return v.toString(16);
    });
  };

  var contentManager = function(params) {
    var that = this;

    var contents = params.model[params.name];

    contents.blocks = ko.observableArray(
      ko.utils.arrayMap(ko.unwrap(contents.blocks), function(block) {
        if (!block.uuid) {
          block.uuid = ko.observable();
        }

        if (!ko.unwrap(block.uuid)) {
          block.uuid(generateUUID());
        }

        return block;
      })
    );

    var blocks = new KnockoutCollection(contents.blocks, { key: 'uuid', reference: true});

    that.sortedBlocks = ko.computed(function() {
      return ko.utils.arrayFilter(blocks.toArray(), function(file) {
        return true;
      });
    });

    that.reorder = function(item, newIndex, type) {
      contents.blocks.remove(item);
      contents.blocks.splice(newIndex, 0, item);
    };

    that.blockTypes = new KnockoutCollection(ko.observableArray([]), { key: 'name', reference: true });

    _.each(config.contentManager.blockTypes, function(type) {
      that.blockTypes.add(new BlockType(type.name, type.label, that, type));
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

    this.componentNameForBlockType = function(blockTypeName) {
      var blockType = that.blockTypes.get(blockTypeName);

      return blockType.component.name;
    };
  };

  return { viewModel: contentManager, template: htmlString };
});