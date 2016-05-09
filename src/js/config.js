var require = {
  baseUrl: '/assets/js',

  paths: {
    "cms/modules/dropbox-chooser": "cms/lib/dropbox-dropins"
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