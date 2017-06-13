module.exports = function() {

  require('./setup')(this);

  // see tests/js/cms-step-definitions/ui.js
  // see tests/js/cms-step-definitions/basics.js
  require('./file-manager-step-definitions').apply(this);
  require('./content-manager-step-definitions').apply(this);

  /*
  this.After(function(result) {

    if (result.isFailed()) {
      this.screenshot();
    }
  });
  */

  this.Given(/^the alice fixtures were loaded:$/, function (string) {
    var that = this, fixtures = string.split(/\r?\n/g);

    fixtures = fixtures.map(function(fixture) {
      return that.path('tests/files/alice/'+fixture+'.yml');
    });

    return this.cli(
      ['testing:load-alice-fixtures', '--manager=default', '--env=dev', '--purge'].concat(fixtures)
    );
  });

};