define(['jquery', 'knockout', 'lodash', 'amplify', 'bluebird', 'cms/modules/dispatcher'], function($, ko, _, amplify, Promise, dispatcher) {

  return function FilemanagerSync() {
    var that = this;
 
    that.concurrentFiles = ko.observable(2); // the number of files per request
    that.concurrentConnections = ko.observable(4); // the number of requests simultanously
 
    this.removeFile = function(item) {
      that.batch.push(ko.unwrap(item.key));
    };
 
    this.beginRemoveBatch = function() {
      that.batch = [];
    };
 
    this.commitRemoveBatch = function(processing) {
      var d = $.Deferred();
      if (that.batch.length) {
        processing(true);
        dispatcher.send('DELETE', '/cms/media', { keys: that.batch }, 'json')
         .done(function(response) {
            processing(false);
            d.resolve(response);
          })
         .fail(function(err, response) {
            processing(false);
            amplify.publish('cms.ajax.error', response);
            d.reject(err, response);
         });
      }
 
      return d.promise();
    };
 
    this.uploadFromDropbox = function(ci, files, processing, progress, afterwards) {
      var path = ci.path();
 
      processing(true);
      progress(0);
 
      var warnings = [];
 
      Promise.map(_.chunk(files, that.concurrentFiles()), function(chunkOfFiles) {
        var sendPromise = dispatcher.sendPromised('POST', '/cms/media/dropbox', { dropboxFiles: chunkOfFiles, path: path }, 'json');
 
        sendPromise.reflect().then(function(inspection) {
         if (inspection.isFulfilled()) {
           var response = inspection.value();
 
           if (response.body && response.body.warnings && response.body.warnings.length) {
             warnings = _.concat(warnings, response.body.warnings);
           }
 
           progress(progress()+chunkOfFiles.length);
         }
        });
 
        return sendPromise;
 
      }, {concurrency: that.concurrentConnections()}).then(function() {
        return dispatcher.sendPromised('GET', '/cms/media', undefined, 'json');
      })
      .then(function(response) {
        processing(false);
        return afterwards(response, warnings);
      })
      .catch(function(fault) {
        processing(false);
 
        if (fault.response) {
          amplify.publish('cms.ajax.error', fault.response);
        } else {
          throw fault;
        }
      });
    };

    this.moveFiles = function(sourcePaths, targetPath, afterwards) {
      dispatcher.sendPromised('POST', '/cms/media/move', { sources: sourcePaths, target: targetPath }, 'json')
        .then(function() {
          return dispatcher.sendPromised('GET', '/cms/media', undefined, 'json');
        }).then(function(response) {
          return afterwards(response);
        })
       .catch(function(err) {
         if (err.response) {
           amplify.publish('cms.ajax.error', err.response);
         } else {
           throw err;
         }
      });
    };

    this.rename = function(path, newName) {
      return dispatcher.sendPromised('POST', '/cms/media/rename', { path: path, name: newName }, 'json')
        .then(function(response) {
          return {
            response: response,
            name: newName
          };
        })
       .catch(function(err) {
         if (err.response) {
           amplify.publish('cms.ajax.error', err.response);
         }

         throw err;
       });
    };
  };
}); 