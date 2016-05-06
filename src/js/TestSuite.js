module.exports = function(options) {
  var that = this;

  if (!options.root) {
    throw new Error('provide root as array of parts of an relative path e.g.: [__dirname, \'..\', \'..\'] ');
  }

  var path = require('path');
  var os = require('os');
  var root = path.join.apply(this, options.root);

  var rpath = function(relative) {
    return path.resolve(root+'/'+relative);
  };

  if (!options.cli) {
    options.cli = rpath('/bin/cli.'+(os.platform() === 'win32' ? 'bat' : 'sh'));
  }

  this.cucumber = {};

  this.cucumber.extendWith = function(functions) {
    var fn;

    if (typeof(functions) === 'string') {
      fn = require(root+'/'+functions);
    } else {
      fn = functions;
    }

    Object.keys(fn).forEach(function(key) {
      that.cucumber.World.prototype[key] = fn[key];
    });
  };

  this.cucumber.injectWorld = function(World) {
    that.cucumber.World = World;

    var Promise = require('bluebird');

    World.prototype.directory = rpath;

    that.cucumber.extendWith(require('../../tests/js/test-fn-utils.js'));

    World.prototype.debug = require('debug')('cucumber-world');

    World.prototype.cli = function(parameters) {
      var that = this;
      var execFile = require('child_process').execFile;

      this.debug('call cli command');
      this.debug(parameters);

      return new Promise(function (resolve, reject) {
        var child = execFile(options.cli, parameters, function(error, stdout, stderr) {
          if (error) {
            that.debug(stderr, stdout);
            error += "\nstdout: \n"+stdout;

            reject(error);
          } else {
            resolve(stdout);
          }
        });
      });
    };
  };

  this.cucumber.useStepDefinitions = function(stepNames, scope) {
    stepNames.forEach(function(name) {
      var stepDefinitions = require('../../tests/js/'+name+'-step-definitions.js');

      stepDefinitions.call(scope);
    });
  };
};