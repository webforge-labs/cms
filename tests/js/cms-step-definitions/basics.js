module.exports = function() {

  this.When(/^I visit "([^"]*)"$/, function (relativeUrl) {
    client.url(relativeUrl);
  });

  this.When(/^I click on "([^"]*)"$/, function (arg) {
    this.css('a:contains("'+arg+'"), .btn:contains("'+arg+'")').exists().click();
  });

  this.Then(/^I( dont)? see the button "([^"]*)"$/, function (reverse, arg) {
    var btn = this.context.css('.btn:contains("'+arg+'")');

    if (reverse) {
      btn.count(0)
    } else {
      btn.count(1);
    }
  });

  this.Then(/^I dont see the text "([^"]*)"$/, function (arg) {
    this.context.css('*:contains("'+arg+'")').count(0);
  });
};
