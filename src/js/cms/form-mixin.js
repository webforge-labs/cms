define(['knockout', 'knockout-mapping', 'jquery', 'lodash', 'cms/TabModel', 'bootstrap-notify', 'bootstrap/alert', 'cms/ko-components/index', 'bluebird', 'cms/ko-bindings/with-visible'], function(ko, koMapping, $, _, Tab, notify, bsAlert, componentsIndex, Promise) {

  return function FormMixin(formModel, options) {
    var that = formModel;
    var dispatcher = options.dispatcher;

    that.isProcessing = ko.observable(false);
    that.error = ko.observable();

    that.save = function(method, url, body) {
      return that.handle(
        dispatcher.sendPromised(method, url, body)
      );
    };

    that.handle = function(promise) {
      that.error(undefined);
      that.isProcessing(true);


      return promise.then(function(response) {
          that.isProcessing(false);

          response.wasChained = true;

          return response;

        }, function(err) {
          that.isProcessing(false);

          if (err.response) {
            var res = err.response;
            err.html = '';

            // 400 error
            if (res.body && res.body.validation && res.body.validation.errors) {
              _.each(res.body.validation.errors, function(error, i) {
                err.html += "<p>";
                if (error.field && error.field.path) {
                  err.html += "<strong>"+error.field.path+"</strong>: ";
                }
                err.html += error.message+"</p>";
              });

            // 500 error and others
            } else if (res.html) {
              err.html = res.html;
            } else if (res.text) {
              err.html = res.text;
            }

            that.error(err.html);
          }

          throw err;
        });
    };
  };
});