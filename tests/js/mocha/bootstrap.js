module.exports = function(options) {
  var chai = require('chai');
  var expect = chai.expect;
  var path = require('path');
  var globalRequirejs = require('requirejs');

  console.log(options);

  var requirejs = globalRequirejs.config({
    context: options.context,
    nodeRequire: require,
    baseUrl: path.resolve(__dirname+'/../../../www/assets/js'),
    paths: {
      'app': path.resolve(__dirname+'/../../../src/js/app'),
      'cms': path.resolve(__dirname+'/../../../src/js/cms'),
    }
  });

  var ko = requirejs('knockout');

  chai.use(function(_chai, utils) {
    utils.addProperty(chai.Assertion.prototype, 'observable', function() {
      var obj = utils.flag(this, 'object');
      var negate = utils.flag(this, 'negate') ? ' not' : '';

      new chai.Assertion(
        ko.isObservable(obj),
        'expected ' + utils.inspect(obj) + negate + ' to be an ko.observable'
        ).to.be.true;
    });
  });

  return {
    expect: expect,
    requirejs: requirejs,
    define: globalRequirejs.define,
    root: path.join(__dirname, '..', '..', '..'),

    fn: require('../test-fn-utils'),

    file: function (sub) {
      return path.resolve(this.root+'/'+sub);
    }
  };

};