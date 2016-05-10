define(['dropbox-dropins', 'admin/config'], function(dropins, config) {
  window.Dropbox.appKey = config.Dropbox.appKey;

  return window.Dropbox;
});