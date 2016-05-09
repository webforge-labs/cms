var require = {
  baseUrl: '/assets/js',

  paths: {
  },

  shim: {
    "cms/modules/dropbox-chooser": {
      deps: ['admin/config'],
      exports: "Dropbox",
      init: function (config) {
        window.Dropbox.appKey = config.Dropbox.appKey;
        return window.Dropbox;
      }
    }
  }
};