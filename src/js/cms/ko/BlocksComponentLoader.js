define(['knockout'], function(ko) {

  blocksLoader = {
    getConfig: function(name, callback) {

      if (name.substring(0,4) === 'cms:') {
        callback({ require: 'cms/ko-components/blocks/'+name.substring(4) });
      } else if (name.substring(0,6) === 'admin:') {
        callback({ require: 'admin/blocks/'+name.substring(6) });
      } else {
        callback(null);
      }
    }
  };

  ko.components.loaders.unshift(blocksLoader);

});