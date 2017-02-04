define(['jquery', 'bluebird', 'lodash'], function($, Promise, _) {

  var ANYVALUE = 'self::ANY';

  var DispatchExpectation = function(req, expect) {
    var that = this;

    that.options = {
      networkDelay: 7, // ms
      whileProcessing: function() {}
    };

    this.req = req;
    this.res = {};

    var isOK = function() {
      return that.res.status >= 200 && that.res.status < 300;
    }

    var createResponse = function() {
      var response = {
        status: that.res.status || 200,
        ok: isOK(),
        body: that.res.body
      };

      if (that.res.headers.format == 'html') {
        response.html = response.body;
      } else if(that.res.headers.format == 'text') {
        response.text = response.body;
      }

      return response;
    };

    var createError = function() {
      var error = new Error('FakeDispatcher-Response-Error');
      error.response = createResponse();

      return error;
    };

    this.accept = function(params) {
      if (that.req.method != ANYVALUE) {
        expect(params.method, 'method of expecation').to.be.equal(that.req.method);
      }

      if (that.req.url != ANYVALUE) {
        expect(params.url, 'url of expectation').to.be.equal(that.req.url);
      }

      if (that.req.body) {
        throw new Error('body matching is not yet implemented');
      }
    };

    this._onSend = function(fulfill, reject) {
      setTimeout(
        function() {
          that.options.whileProcessing();

          if (isOK()) {
            fulfill(createResponse());
          } else {
            reject(createError());
          }
        }, 
        that.options.networkDelay
      );
    };

    this.whileProcessing = function(callback) {
      if (!_.isFunction(callback)) {
        throw new Error('provide a function to whileProcessing()');
      }

      that.options.whileProcessing = callback;

      return this;
    };

    this.respond = function(status, body, headers) {
      if (!headers) {
        headers = {
          format: 'json'
        };
      }

      if (headers.type) {
        throw new Error('there is no option "type" in headers for respond()');
      }

      if (!_.isInteger(status)) {
        throw new Error('Provide status as an integer');
      }

      if (!_.isPlainObject(body) && !_.isString(body)) {
        throw new Error('Provide body as plain object or string');
      }

      that.res.status = status;
      that.res.body = body;
      that.res.headers = headers;

      return this;
    };

    // sugar
    this.to = this;
  };

  DispatchExpectation.ANY = ANYVALUE;

  var FakeDispatcher = function (options) {
    var that = this;

    expectations = [];

    that.sendPromised = function(method, url, body) {
      if (!expectations.length) {
        throw new Error('FakeDispatcher: sendPromised() was called with '+method+' '+url+', but the call wasnt expected or was called to many times');
      }

      var expectation = expectations.shift();

      expectation.accept({
        method: method,
        url: url,
        body: body
      });

      return new Promise(expectation._onSend);
    };

    that.expect = function(method, url) {

      var expectation = new DispatchExpectation(
        {
          method: method,
          url: url || DispatchExpectation.ANY
        }, 
        options.expect
      );

      expectations.push(expectation);

      return expectation;
    };

    that.reset = function() {
      expectations = [];
    };

    that.getExpectations = function() {
      return expectations;
    };
  };

  return FakeDispatcher;

});