var boot = require('./bootstrap');
var expect = boot.expect;
var cukedZombie = require('cuked-zombie');
var _ = require('lodash');
var Dragger = require('../dragger');

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

  it('should serialize the fixture', function () {
    //https://github.com/webforge-labs/webforge-testdata-repository/blob/master/lib/Webforge/TestData/NestedSet/Hgdrn.php
    var navigation = this.koMain().navigation;
    var nodes = navigation.serialize();

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
9         model
10        win
11   Kunden
*/

    expect(nodes).to.have.length(12);

    nodes.forEach(function(node) {
      expect(node).to.have.property('guid');
    });

    expect(nodes[0]).to.have.property('title', 'Startseite');
    expect(nodes[0]).to.have.property('parent', null);
    expect(nodes[4]).to.have.property('title', 'Lösungen');
    expect(nodes[4]).to.have.property('parent', nodes[0].id);
    expect(nodes[8]).to.have.property('title', 'container');
    expect(nodes[8]).to.have.property('parent', nodes[7].id);
    expect(nodes[8]).to.have.property('depth', 3);
    expect(nodes[11]).to.have.property('depth', 1);
  });

  it.skip("should serialize the right parent if nodes are moved by mouse", function() {
    var navigation = this.koMain().navigation;
    
    // move container to Lösungen
    // 
    // this is not possible because elements aren't drawed in zombie (looks like this)
    var dragger = new Dragger(this.getjQuery(), this.browser.window);
    var containerItem = this.css('.dd-item:has(.btn:contains("container"))').exists().get();

    dragger.simulateDrag(containerItem, { dx: 0, dy: -100});

    var nodes = navigation.serialize();
  });

});