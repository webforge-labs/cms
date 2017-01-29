define(['require', './Sync', './Item', './Manager'], function(require) {
   var FileManager = {};

   FileManager.Sync = require('./Sync');
   FileManager.Item = require('./Item');
   FileManager.Manager = require('./Manager');

   return FileManager;
});