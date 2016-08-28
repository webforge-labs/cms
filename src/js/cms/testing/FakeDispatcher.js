define(['jquery', 'bluebird'], function($, Promise) {

  var FakeDispatcher= function () {
    var that = this;

    that.promiseBodies = [];

    that.onSend = function(promiseBody) {
      that.promiseBodies.push(promiseBody);
    };

    that.sendPromised = function(method, url, body) {
      return new Promise(that.promiseBodies.shift());
    };

    that.reset = function() {
      that.promiseBodies = []
    };

  };

  return FakeDispatcher;

});