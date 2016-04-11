var require = {
  baseUrl: '/assets/js',

  paths: {
    'cms': '/cms-root/src/js/cms',
    'app': '/root/src/js/app',
    'admin': '/root/src/js/admin',
    "cms/modules/dropbox-chooser": ["https://www.dropbox.com/static/api/2/dropins"]
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