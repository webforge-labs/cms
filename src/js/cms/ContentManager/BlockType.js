define(['knockout', './Block'], function(ko, Block) {

  return function(name, label, contentManager) {
    var that = this;
    that.label = label;
    that.name = name;

    that.add = function() {
      var blockData = {
        type: ko.observable(name),
        label: label // can be customized from user for this specific block
      };

      if (that.name === 'markdown' || that.name === 'intro') {
        blockData.markdown = ko.observable('');
      }

      contentManager.addBlock(blockData);
    };
  };

});