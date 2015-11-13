module.exports = function(expect, commons) {
  /* jshint expr:true */

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
      world.context = world.css('body').exists();
      callback();
    });
  });

  this.Given(/^the alice fixtures were loaded:$/, {timeout: 10000}, function (string, callback) {
    var fixtures = string.split(/\r?\n/g);

    fixtures = fixtures.map(function(fixture) {
      return commons.file('tests/files/alice/'+fixture+'.yml');
    });

    this.cli(['h4cc_alice_fixtures:load:files', '--env=dev', '--drop'].concat(fixtures), callback);
  });

  this.fn.findTab = function(label, options) {
    var shouldExist = options && options.hasOwnProperty('shouldExist') ? options.shouldExist : true;

    return this.css('[role=tabs-nav]').exists()
      .css('li:contains("'+label+'")').count(shouldExist ? 1 : 0);
  };

  this.fn.findTabLink = function(label) {
    return this.findTab(label).css('a:first').exists();
  };

  this.fn.activeTabContent = function() {
    return this.css('[role=tab-content].active').exists();
  };

  this.Then(/^a tab with title "([^"]*)" is added$/, function (label, callback) {
    this.findTab(label);
    callback();
  });

  this.When(/^I (activate|select) the tab "([^"]*)"$/, function (nulll, label, callback) {
    this.util.clickLink(this.findTabLink(label).get(), callback);
  });

  this.When(/^I goto the tab "([^"]*)" in section "([^"]*)" in the sidebar$/, function (label, section, callback) {
    var that = this;
    var link = this.findSidebarLink(label, section);

    this.util.clickLink(link.get(), function() {
      that.util.clickLink(that.findTabLink(label).get(), callback);
    });
  });

  this.When(/^I click on the x on the tab "([^"]*)"$/, function (label, callback) {
    var $close = this.findTab(label).css('[role=close]').exists().get();

    this.util.clickLink($close, callback);
  });

  this.Then(/^the tab with title "([^"]*)" is removed$/, function (label) {
    this.findTab(label, { shouldExist: false });
  });

  this.fn.findSidebarLink = function(label, section) {
    return this.css('[role="sidebar"] .panel:has(.panel-title:contains("'+section+'"))').exists()
      .css('[role=tabpanel]').exists()
        .css('a:contains("'+label+'")').exists();
  };

  this.When(/^I click on "([^"]*)" in section "([^"]*)" in the sidebar$/, function (link, section, callback) {
    var $a = this.findSidebarLink(link, section).get();
    
    this.util.clickLink($a, callback);
  });

  this.Then(/^the content from the active tab contains a headline "([^"]*)"$/, function (headlineText, callback) {
    this.activeTabContent().css('h2:contains("'+headlineText+'")').exists();
    callback();
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
        title: indent+$li.find('.dd-handle:first').text()
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
};