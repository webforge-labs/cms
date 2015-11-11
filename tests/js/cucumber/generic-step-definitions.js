module.exports = function(expect, commons) {
  /* jshint expr:true */
  var that = this;

  // i see something
  this.Then(/^I should see a headline "([^"]*)"$/, function (arg1) {
    this.context.css('h1:contains("'+arg1+'"), h2:contains("'+arg1+'"), h3:contains("'+arg1+'"), h4:contains("'+arg1+'"), h5:contains("'+arg1+'"), h6:contains("'+arg1+'")').exists();
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


  this.Then(/^I should see an alert with "([^"]*)"$/, function (content) {
    this.context.css('.alert:contains("'+content+'")').exists();
  });

  // action stuff

  this.When(/^I press the button "([^"]*)"$/, function (label, callback) {
    this.util.pressButton(
      this.util.textButton(label),
      callback
    );
  });
};