module.exports = function(expect, commons, fn) {
  /* jshint expr:true */

  this.fn = fn;

  expect = require('chai').expect;

  this.When(/^I visit "([^"]*)"$/, function (url, callback) {
    var world = this;
    world.browser.visit(url, function() {
      world.context = world.css('body').exists();
      callback();
    });
  });

  this.Given(/^I am logged in as "([^"]*)"$/, { timeout: 8000 }, function (email, callback) {
    var world = this;

    this.browser.on('authenticate', function(authentication) {
      authentication.username = email;
      authentication.password = 'secret';
    });

    world.browser.visit('/cms', function() {
      //world.waitForjQuery(function() {
        world.context = world.css('body').exists();
        callback();
      //});
    });
  });

  this.Then(/^a tab with title "([^"]*)" is added$/, function (label, callback) {
    this.findTab(label);
    callback();
  });

  this.When(/^I (activate|select) the tab "([^"]*)"$/, function (nulll, label, callback) {
    var world = this;
    world.test = true;
    this.util.clickLink(this.findTabLink(label).get(), function() {
      world.context = world.activeTabContent();
      console.log('assign context to world');
      callback();
    });
  });

  this.When(/^I goto the tab "([^"]*)" in section "([^"]*)" in the sidebar$/, function (label, section, callback) {
    this.gotoTabInSidebar(label, section, callback);
  });

  this.When(/^I click on the x on the tab "([^"]*)"$/, function (label, callback) {
    var $close = this.findTab(label).css('[role=close]').exists().get();

    this.util.clickLink($close, callback);
  });

  this.Then(/^the tab with title "([^"]*)" is removed$/, function (label) {
    this.findTab(label, { shouldExist: false });
  });

  this.When(/^I reload the tab$/, function (callback) {
    var amplify = this.browser.window.require('amplify');
    amplify.publish('cms.tabs.reload');
    callback();
  });

  this.When(/^I click on "([^"]*)" in section "([^"]*)" in the sidebar$/, function (link, section, callback) {
    var $a = this.findSidebarLink(link, section).get();
    
    this.util.clickLink($a, callback);
  });

  this.Then(/^the content from the active tab contains a headline "([^"]*)"$/, function (headlineText, callback) {
    this.activeTabContent().css('h2:contains("'+headlineText+'")').exists();
    callback();
  });

  this.When(/^I press "([^"]*)"$/, function (text, callback) {
    this.util.pressButton(
      this.util.textButton(text),
      callback
    );
  });

  this.When(/^I click (?:on\s+)?"([^"]*)" in context$/, function (text, callback) {
    this.util.clickLink(
      this.context.css('a:contains("'+text+'")').exists().get(),
      callback
    );
  });

  this.Then(/^I see "([^"]*)" as loggedin user$/, function (name) {
    this.context.css('[role="username"]:contains("'+name+'")').exists();
  });

  this.Then(/^I should see the table with pages:$/, function (dataTable, callback) {
    var nav = this.activeTabContent().css('.dd:first').exists();
    var $ = this.browser.window.jQuery;

    var nodes = [];
    nav.get().find('.dd-item').each(function() {
      var $li = $(this);

      var indent = new Array($li.parents('.dd-list').length).join('--');

      nodes.push({
        title: indent+$li.find('.dd-handle:first > span').text()
      })
    });


    try {
      expect(nodes, delta).to.be.deep.equal(dataTable.hashes());
      callback();
    } catch (assertion) {
      var jsondiff = require('json-diff');
      var delta = jsondiff.diffString(assertion.expected, assertion.actual);
      assertion.message = assertion.message + "\n" + delta;
      throw assertion;
    }
  });

  this.When(/^I press the delete\-button from "([^"]*)"$/, function (arg1, callback) {
    // note that the first nav item might contain in .dd-item the text from arg1 (because the text is contained in the children nested somewhere)
    var button = this.activeTabContent().css('.dd-item .dd-handle:has(span:contains("'+arg1+'")) .btn[role=delete]').exists().get();

    this.util.pressButton(button, callback);
  });
};