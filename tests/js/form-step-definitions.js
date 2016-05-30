module.exports = function() {

  var xpath = require('./xpath');

  this.When(/^I fill in "([^"]*)" for "([^"]*)"$/, function (value, field) {
    var selector = 
      xpath('.form-group')
        .find('label').contains(field)
        .parent()
          .find('input')
       .toString();

    this.context.setValue(selector, value);
  });

  this.Then(/^I see a success alert$/, function () {
    var selector = xpath('*').role('alert').contains('Alles klar').toString();

    client.waitForVisible(selector, 4000);

    expect(client.isExisting(selector), 'ok alert').to.be.true;
  });

};

