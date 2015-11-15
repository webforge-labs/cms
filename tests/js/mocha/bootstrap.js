var expect = require('chai').expect;
var path = require('path');
var requirejs = require('requirejs');

requirejs.config({
  nodeRequire: require,
  baseUrl: path.resolve(__dirname+'/../../../www/assets/js'),
  paths: {
    'app': path.resolve(__dirname+'/../../../src/js/app'),
  }
});

module.exports = {
  expect: expect,
  requirejs: requirejs,
  root: path.join(__dirname, '..', '..', '..'),

  fn: require('../test-fn-utils'),

  file: function (sub) {
    return path.resolve(this.root+'/'+sub);
  }
};