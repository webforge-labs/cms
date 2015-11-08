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

var KnockoutCollection = requirejs('app/KnockoutCollection');
var koMapping = requirejs('knockout-mapping');

describe('KnockoutCollection', function() {
  beforeEach(function() {
    this.collection = new KnockoutCollection([], { key: 'id' });

    this.item1 = koMapping.fromJS({name: 'item1', id: 1});
    this.item2 = koMapping.fromJS({name: 'item2', id: 2});
    this.item3 = koMapping.fromJS({name: 'item3', id: 3});
  });

  it('should add items to the collection', function () {
    this.collection.add(this.item1);

    var items;

    expect(items = this.collection.toArray()).to.be.an('array');
    expect(items).to.be.eql([this.item1]);

    this.collection.add(this.item2);
    expect(items = this.collection.toArray()).to.be.an('array');
    expect(items).to.be.eql([this.item1, this.item2]);
  });

  it('should add items only once', function () {
    var items;

    this.collection.add(this.item1);
    this.collection.add(this.item1);
    this.collection.add(this.item2);

    expect(items = this.collection.toArray()).to.be.an('array');
    expect(items).to.be.eql([this.item1, this.item2]);
  });

  it('should reflect with containsKey for items', function() {
    expect(this.collection.containsKey(1)).to.be.false;

    this.collection.add(this.item1);
    expect(this.collection.containsKey(1)).to.be.true;
  });

  it('should reflect with contains for items', function() {
    expect(this.collection.contains(this.item1)).to.be.false;

    this.collection.add(this.item1);
    expect(this.collection.contains(this.item1)).to.be.true;
  });

  describe('get()', function() {
    it("should return the item for a key", function() {
      this.collection.add(this.item2);

      expect(this.collection.get(2)).to.be.eql(this.item2);
    });

    it("should return undefined for not set keys", function() {
      expect(this.collection.get(7)).to.be.undefined;
    });
  });

});