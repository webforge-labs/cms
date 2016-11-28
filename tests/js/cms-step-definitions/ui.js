module.exports = function() {

  this.World.prototype.clickTabLink = function(label) {
    // open the dropdown menu
    this.css('.tabs-container .dropdown .dropdown-toggle').waitForExist().click();

    var tabLink = this.css('.tabs-container ul.dropdown-menu li:contains("'+label+'") a').waitForExist(4000);

    tabLink.click();
  };

  this.World.prototype.findActiveTab = function() {
    browser.waitForExist('[role=tab-content].active', 2400);
    return this.css('[role="tab-content"].active');
  };

  this.Given(/^I am logged in as "([^"]*)"$/, function (email) {
    browser.url('/cms');

    if (browser.isExisting('h1*=Zugang zum CMS')) {
      browser.setValue('[name="_username"]', email);
      browser.setValue('[name="_password"]', 'secret');
      browser.submitForm('form');
    }

    browser.waitForVisible('#content-container', 1200);
  });

  this.When(/^I goto the tab "([^"]*)" in section "([^"]*)" in the sidebar$/, function (label, section) {
    var sidebar = this.css('[role="sidebar"]').exists();
    var sidebarLink = sidebar.css('.panel:has(.panel-title:contains("'+section+'"))').exists()
      .css('[role=tabpanel]').exists()
        .css('a:contains("'+label+'")').exists();

    if (!sidebarLink.isVisible()) {
      sidebar.css('.panel-heading:contains("'+section+'")').exists().click();
      sidebarLink.waitForVisible();
    }

    sidebarLink.click();

    this.clickTabLink(label);

    this.context = this.findActiveTab();
  });
  
  this.When(/^I activate the tab with "([^"]*)"$/, function(title) {  
    this.clickTabLink(title);
    this.context = this.findActiveTab();
  });

  this.Then(/^I( can\'t)? see the text "([^"]*)"$/, function (reversed, text) {
    var text = this.context.css(':contains("'+text+'")')

    if (reversed) {
      text.count(0)
    } else {
      text.atLeast(1);
    }
  });

  this.When(/^I wait to see the text "([^"]*)"$/, function (text) {
    this.context.css(':contains("'+text+'")').waitForExist(2000);
  });

  this.When(/^I click on "([^"]*)" in context$/, function (linktext) {
    this.context.css('a:contains("'+linktext+'"), button:contains("'+linktext+'")').click();
  });

  this.Then(/^the list table has (\d+) rows$/, function (number) {
    this.context.css('table.table tbody tr').count(parseInt(number, 10));
  });

  this.When(/^I select the row from the list table with "([^"]*)"$/, function (title) {
    this.context.css('table.table tr:contains("'+title+'")').exists()
      .css('td input[type="checkbox"]').click();
  });

  this.When(/^I click on the link in the row from the list table with "([^"]*)"$/, function (title) {
    this.context.css('table.table tr:contains("'+title+'")').waitForExist(3000)
      .css('td a:first').click();
  });

  this.Then(/^a message is shown "([^"]*)"$/, function (text) {
    this.css('div[role=alert]:contains("'+text+'")').waitForVisible(3000);
  });
};