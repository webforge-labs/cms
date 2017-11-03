module.exports = function() {
  this.World.prototype.getContext = function() {
    if (!this.context) {
      this.context = this.css('body').exists();
    }

    return this.context;
  };

  this.When(/^I visit "([^"]*)"$/, function (relativeUrl) {
    client.url(relativeUrl);
    this.context = undefined;
  });

  this.When(/^I click on "([^"]*)"$/, function (arg) {
    this.getContext().css('a:contains("'+arg+'"), .btn:contains("'+arg+'")').exists().click();
  });

  this.When(/^I click on "([^"]*)" in dropdown "([^"]*)"$/, function (arg1, arg2) {
    this.getContext().css('.dropdown-toggle:contains("'+arg2+'")').waitForVisible().click();

    this.getContext().css('.dropdown-toggle:contains("'+arg2+'") + .dropdown-menu a:contains("'+arg1+'")').waitForVisible().click();
  });

  // i see something

  this.Then(/^I should see a headline "([^"]*)"$/, function (arg1) {
    this.getContext().css('h1:contains("'+arg1+'"), h2:contains("'+arg1+'"), h3:contains("'+arg1+'"), h4:contains("'+arg1+'"), h5:contains("'+arg1+'"), h6:contains("'+arg1+'")').waitForVisible(2000);
  });

  this.Then(/^I should see an alert with "([^"]*)"$/, function (content) {
    this.getContext().css('.alert:contains("'+content+'")').waitForVisible(8000);
  });

  this.Then(/^I dont see the text "([^"]*)"$/, function (arg) {
    this.context.css('*:contains("'+arg+'")').count(0);
  });

  this.Then(/^I see the text "([^"]*)"$/, function (content) {
    this.getContext().css(':contains("'+content+'")').exists().isVisible();
  });

  this.Then(/^I( dont)? see the button "([^"]*)"$/, function (reverse, arg) {
    var btn = this.getContext().css('.btn:contains("'+arg+'")');

    if (reverse) {
      btn.count(0)
    } else {
      btn.count(1);
    }
  });

  // forms stuff
  this.Then(/^I see the edit form$/, function() {
    this.context.css('.form-horizontal').waitForExist(8000);
  });

  this.When(/^I save the form$/, function() {
    this.context.css('.btn:contains("Speichern")').click();
  });


  this.When(/^I fill in "([^"]*)" (?:for|as) "([^"]*)"$/, function (value, inputLabel) {
    var that = this;
    var selectors = ['.form-group:has(label:contains("'+inputLabel+'")) .form-control:first', 'input[placeholder="'+inputLabel+'"]', 'input[name="'+inputLabel+'"]'];

    var field;
    selectors.every(function(selector) {
      field = that.context.css(selector);

      if (field.getCount() == 1) {
        return false; // break
      }

      return true;
    });

    expect(field.getCount(), 'field matching one of the input selectors: '+selectors.join(', ')).to.be.equal(1);

    field.setValue(value);
  });

};
