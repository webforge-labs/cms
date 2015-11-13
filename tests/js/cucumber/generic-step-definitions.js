module.exports = function(expect, commons) {
  /* jshint expr:true */
  var that = this;

  this.fn.getContext = function() {
    if (!this.context) {
      this.context = this.css('body').exists();
    }

    return this.context;
  };

  this.Before(function(scenario) {
    var world = this;

    // in case a button/something submits a form and the page changes
    world.browser.on('submit', function() {
      // reset because it isnt valid anymore (no matter what)
      world.context = null;
    });

    world.browser.on('loaded', function() {
      // reset because it isnt valid anymore (no matter what)
      world.context = null;
    });
  });

  // i see something
  this.Then(/^I should see a headline "([^"]*)"$/, { withCSS: true }, function (arg1, callback) {
    this.getContext().css('h1:contains("'+arg1+'"), h2:contains("'+arg1+'"), h3:contains("'+arg1+'"), h4:contains("'+arg1+'"), h5:contains("'+arg1+'"), h6:contains("'+arg1+'")').exists();
    callback(); // if i dont use this callback it does not work?
  });

  this.Then(/^I should see an alert with "([^"]*)"$/, function (content) {
    this.context.css('.alert:contains("'+content+'")').exists();
  });

  // forms stuff

  this.When(/^I fill in "([^"]*)" for "([^"]*)"$/, function (value, inputLabel, callback) {
    var that = this;
    var selectors = ['input[placeholder="'+inputLabel+'"]'];

    var $field;
    selectors.forEach(function(selector) {
      $field = that.context.css(selector).get();

      if ($field.length === 1) {
        return false; // break
      }
    });

    expect($field).to.be.ok.and.to.have.length(1);

    this.util.fill($field, value, callback);
  });

  // action stuff

  this.When(/^I click on "([^"]*)"$/, function (arg1, callback) {
    this.util.clickLink(this.util.textLink(arg1), callback);
  });

  this.When(/^I press the button "([^"]*)"$/, function (label, callback) {
    this.util.pressButton(
      this.util.textButton(label),
      callback
    );
  });
};