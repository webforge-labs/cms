var fn = {};

// all these functions can be used in cucumber as well in mocha for the tests

fn.findTab = function(label, options) {
  var shouldExist = options && options.hasOwnProperty('shouldExist') ? options.shouldExist : true;

  return this.css('[role=tabs-nav]').exists()
    .css('li:contains("'+label+'")').count(shouldExist ? 1 : 0);
};

fn.findTabLink = function(label) {
  return this.findTab(label).css('a:first').exists();
};

fn.activeTabContent = function() {
  return this.css('[role=tab-content].active').exists();
};

fn.findSidebarLink = function(label, section) {
  return this.css('[role="sidebar"] .panel:has(.panel-title:contains("'+section+'"))').exists()
    .css('[role=tabpanel]').exists()
      .css('a:contains("'+label+'")').exists();
};

fn.gotoTabInSidebar = function(label, section, callback) {
  var world = this;
  var link = world.findSidebarLink(label, section);

  this.util.clickLink(link.get(), function() {
    world.util.clickLink(world.findTabLink(label).get(), callback);
  });
};

fn.koMain = function() {
  return this.koData(this.css('body').exists().get());
};

fn.css = function(selector) {
  if (!that.browser.window.jQuery) {
    throw new Error('cannot css() because jQuery is not defined. Maybe you used this.css and forgot withCSS parameter after callback?');
  }

  return new CSSTest(that.browser.window.jQuery, selector);
};

module.exports = fn;