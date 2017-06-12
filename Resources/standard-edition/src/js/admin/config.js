define(['json!etc/cms/blocktypes.json'], function(blocktypes) {

  return {
    Dropbox: {
      appKey: 'xxx' // @FIXME
    },

    contentManager: {
      blockTypes: blocktypes
    }

  };

});