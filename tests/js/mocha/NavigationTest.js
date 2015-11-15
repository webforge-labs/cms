var boot = require('./bootstrap');
var expect = boot.expect;
var koMapping = boot.requirejs('knockout-mapping');
var cukedZombie = require('cuked-zombie');
var _ = require('lodash');

describe('Navigation', function() {
  before(function(done) { // execute once
    this.timeout(20000);
    var that = this;

    cukedZombie.infectWorld(this, require('../world-config'));

    this.world = new this.World();

    _.merge(this, this.world);
    _.merge(this, boot.fn);

    this.loadFixtures = function(fixtures, callback) {
      fixtures = fixtures.map(function(fixture) {
        return boot.file('tests/files/alice/'+fixture+'.yml');
      });

      that.cli(['h4cc_alice_fixtures:load:files', '--env=dev', '--drop'].concat(fixtures), callback);
    };

    this.loadFixtures(['users', 'nestedset.hgdrn'], function() {

      that.browser.on('authenticate', function(authentication) {
        authentication.username = 'petraplatzhalter';
        authentication.password = 'secret';
      });

      that.browser.visit('/cms', function() {
        that.gotoTabInSidebar('Seiten verwalten', 'Webseite', done);
      });
    });
  });

  var flat = function(serializedNavigation) {
    var flatNodes = [];

    var DFS = function (nav, parent, depth) {
      nav.forEach(function(node) {
        flatNodes.push({
          title: node.title,
          parent: node.parent ? node.parent.title : undefined,
          depth: depth
        });

        if (node.children.length) {
          DFS(node.children, node, depth+1);
        }
      });
    };

    expect(serializedNavigation).to.be.a('Array').and.to.have.length(1);

    DFS(serializedNavigation, null, 0);

    return flatNodes;
  };

  it('should serialize the fixture', function () {
    //https://github.com/webforge-labs/webforge-testdata-repository/blob/master/lib/Webforge/TestData/NestedSet/Hgdrn.php
    var navigation = this.koMain().navigation;

    var nodes = flat(navigation.serialize());

/*
0  Startseite
1    Unternehmen
2    Produkte
3    Dienstleistungen
4    Lösungen
5      HMS
6      HTS
7      INT
8         container
9          model
10          win
11   Kunden
*/

    expect(nodes).to.have.length(12);

    expect(nodes[0]).to.have.property('title', 'Startseite');
    expect(nodes[4]).to.have.property('title', 'Lösungen');
    expect(nodes[8]).to.have.property('title', 'container');
    expect(nodes[8]).to.have.property('depth', 3);
    expect(nodes[11]).to.have.property('depth', 1);
  });

});