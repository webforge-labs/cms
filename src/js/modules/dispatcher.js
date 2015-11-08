define(['superagent', 'jquery', 'lodash'], function(request, $, _) {

  var Dispatcher = function() {
    var that = this;

    this.send = function(method, url, body, accept) {
      var d = $.Deferred();

      if (_.isArray(url)) {
        url = '/'+url.join('/');
      }

      request(method, url)
        .send(body)
        .accept(accept || 'json')
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
