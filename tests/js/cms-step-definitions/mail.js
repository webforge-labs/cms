module.exports = function() {

  this.World.prototype.retrieveMailSpool = function() {
    return this.cli(
      ['mail:spool']
    ).then(function(prc) {
      return JSON.parse(prc.stdout);
    });
  };

  this.World.prototype.clearMailSpool = function() {
    return this.cli(['mail:spool', '--clear']);
  };

  this.Given(/^the mail spool is empty$/, function () {
    return this.clearMailSpool();
  });
};
