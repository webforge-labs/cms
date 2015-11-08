module.exports = function(expect) {
  /* jshint expr:true */
  var that = this;

  this.fn.findTab = function(label) {
    return this.css('ul.nav-tabs:first').exists()
      .css('li:contains("'+label+'")').exists();
  };

  this.fn.activeTab = function() {
    return this.css('[role=tab-content].active').exists();
  };

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
    this.findTab(label);
    callback();
  });

  this.When(/^I (activate|select) the tab "([^"]*)"$/, function (nulll, label, callback) {
    var tab = this.findTab(label);

    this.util.clickLink(tab.css('a').get(), callback);
  });

  this.Then(/^the content from the active tab contains a headline "([^"]*)"$/, function (headlineText, callback) {
    this.activeTab().css('h2:contains("'+headlineText+'")').exists();
    callback();
  });
};