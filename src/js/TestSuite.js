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

    World.prototype.setOption = function(name, value) {
      options[name] = value;
    };

    World.prototype.cli = function(parameters, execOptions) {
      var that = this;
      var execFile = require('child_process').execFile;

      this.debug('call cli command');
      this.debug(parameters);

      if (execOptions.extendEnv) {
        var env = {}, e;
        for (e in process.env) {
          env[e] = process.env[e];
        }
        for (e in execOptions.extendEnv) {
          env[e] = execOptions.extendEnv[e];
        }
        execOptions.env = env;
        delete execOptions.extendEnv;
      }

      return new Promise(function (resolve, reject) {
        var child = execFile(options.cli, parameters, execOptions, function(error, stdout, stderr) {
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

    // options: manager
    World.prototype.dql = function(dql, parameters, options, execOptions) {
      if (!options) options = {};
      if (!options.manager) options.manager = 'default';

      /* globals Buffer */
      var jsonParameters = JSON.stringify(parameters);
      var encodedParameters = new Buffer(jsonParameters).toString('base64');

      return this.cli(['testing:dql', '--manager='+options.manager, '--base64', dql, encodedParameters], execOptions)
        .then(function(stdout) {
          var result = JSON.parse(stdout);

          return result;
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