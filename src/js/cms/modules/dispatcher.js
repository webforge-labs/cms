define(['superagent', 'jquery', 'lodash', 'bluebird'], function(request, $, _, Promise) {

  var Dispatcher = function() {
    var that = this;

    this.sendPromised = function(method, url, body, accept, withRequest) {
      if (_.isArray(url)) {
        url = '/'+url.join('/');
      }

      var req = request(method, url)
        .accept(accept || 'json');

      if (withRequest) {
        withRequest(req);
      }

      var promise = new Promise(function (fulfill, reject) {

        req
          .send(body)
          .end(function(err, response) {
            if (response.ok) {
              fulfill(response);
            } else {
              that.handleError(response, err);
              err.response = response;
              reject(err);
            }
         });
        
      });

      return promise;
    };

    this.send = function(method, url, body, accept, withRequest) {
      var d = $.Deferred();

      if (_.isArray(url)) {
        url = '/'+url.join('/');
      }

      var req = request(method, url)
        .accept(accept || 'json');

      if (withRequest) {
        withRequest(req);
      }

      req
        .send(body)
        .end(function(err, response) {
          if (response.ok) {
            d.resolve(response);
          } else {
            d.reject(err, response);
            that.handleError(response, err);
          }
       });

      return d.promise();
    };

    this.handleError = function(info, err) {
      /* globals alert, console */
      if (console && console.log) {
        console.log('Fehler aufgetreten im Dispatcher: ', info, err);
      } else {
        alert('Bei der Kommunikation mit dem Backend ist ein Fehler aufgetreten: '+err);
      }
    };
  };

  return new Dispatcher();
});
