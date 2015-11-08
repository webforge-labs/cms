module.exports = function(expect) {
  /* jshint expr:true */
  var that = this;

  this.Given(/^I am logged in as "([^"]*)"$/, function (email, callback) {
    this.visitPage('/', function() {
      this.waitForjQuery(function() {
        callback();
      });
    });
  });

  this.When(/^I click on "([^"]*)" in section "([^"]*)" in the sidebar$/, function (link, section, callback) {
    var $a = this.css('[role="sidebar"] .panel:has(.panel-title:contains("'+section+'"))').exists()
      .css('[role=tabpanel]').exists()
        .css('a:contains("'+link+'")').exists().get();
    
    this.util.clickLink($a, callback);
  });

  this.Then(/^a tab with title "([^"]*)" is added$/, function (label, callback) {
    this.css('ul.nav-tabs:first').exists()
      .css('li:contains("'+label+'")').exists();

    callback();
  });

  this.When(/^I activate the tab "([^"]*)"$/, function (arg1, callback) {
    // Write code here that turns the phrase above into concrete actions
    callback.pending();
  });

  this.Then(/^the tab contains a headline "([^"]*)"$/, function (arg1, callback) {
    // Write code here that turns the phrase above into concrete actions
    callback.pending();
  });

};