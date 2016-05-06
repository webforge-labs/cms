module.exports = function() {

  this.Given(/^the alice fixtures were loaded:$/, function (string) {
    var that = this;
    var fixtures = string.split(/\r?\n/g);

    fixtures = fixtures.map(function(fixture) {
      return that.directory('tests/files/alice/'+fixture+'.yml');
    });

    return this.cli(['h4cc_alice_fixtures:load:files', '--env=dev', '--drop'].concat(fixtures));
  });

};