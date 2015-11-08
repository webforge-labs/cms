/* globals __dirname */
var chai = require('chai');
var path = require('path');
var _ = require('lodash');

module.exports = function() {
  var cucumberStep = this;
  var cukedZombie = require('cuked-zombie');

  chai.config.includeStack = true;

  var root = path.join(__dirname, '..', '..', '..');
  var commons = {
    dir: function (sub) {
      return path.resolve(root+'/'+sub);
    },

    file: function (sub) {
      return path.resolve(root+'/'+sub);
    },

    hashTable: function(table, keyName, valueName) {
      if (!table) return {};
      if (!keyName) keyName = 'name';
      if (!valueName) valueName = 'value';

      var hashes = table.hashes();
      return _.zipObject(_.pluck(hashes, keyName), _.pluck(hashes, valueName));
    }
  };

  var infected = cukedZombie.infect(cucumberStep, {
    world: require('../world-config'),
    steps: {
      arguments: [chai.expect, commons],
      dir: __dirname
    }
  });

  cukedZombie.Zombie.extend(function(browser) {
    browser.on('authenticate', function(authentication) {
      authentication.username = 'tvs';
      authentication.password = '1891';
    });
  });

  infected.World.prototype.init = function(Browser) {
    //Browser.dns.localhost('tvstt.laptop.ps-webforge.net');
    this.filled = {};
  };
};