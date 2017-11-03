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

  this.Then(/^an password reset mail should be mailed to "([^"]*)"$/, function(realRecipient) {
    var world = this;

    return this.retrieveMailSpool().then(function(spool) {
      expect(spool, 'assume one email to be send').to.have.length(1);

      var mail = spool[0];

      expect(mail).to.have.property('to').to.be.equal(realRecipient);

      expect(mail).to.have.property('subject').to.have.string('Passwort zurücksetzen');

      expect(mail).to.have.property('body').to.have.string('Bitte besuche die folgende Seite');

      var linkRegexp = /^https?:\/\/(.*?)\/(.*)$/m;
      expect(mail).to.have.property('body').to.match(linkRegexp);

      world.passwordResetLink = mail.body.match(linkRegexp)[2];

    });
  });

  this.When(/^I follow the link from the reset mail$/, function() {
    expect(this.passwordResetLink).to.not.be.empty;

    this.context = undefined;
    return client.url(this.passwordResetLink);
  });
};
