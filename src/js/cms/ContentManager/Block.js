define(['knockout'], function(ko) {

  return function(label, contentManager) {
    var that = this;
    that.label = label;

    that.add = function() {
      contentManager.addBlock();
    }
  };

});