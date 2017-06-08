module.exports = function() {

  this.World.prototype.openContentManagerMenu = function() {
    this.context.css('.btn:contains("Inhalt hinzufügen")').waitForVisible().click()
    
    return this.context.css('.btn-group:has(.btn:contains("Inhalt hinzufügen"))').waitForExist()
      .css('.dropdown-menu').waitForVisible(100);
  };

  this.World.prototype.cmPanel = function(number) {
    return this.context.css('.content-manager')
      .css('.panel:eq('+number+')').exists();
  };

  this.World.prototype.contentStream = function() {
    var result = browser.execute(function() {
      var koMapping = window.require('knockout-mapping');
      var ko = window.require('knockout');

      var root = ko.dataFor(document.getElementById('content-manager'));

      return koMapping.toJS(root.entity.contents);
    });

    expect(result).to.have.property('state').to.be.equal('success');

    return result.value;
  };

  this.World.prototype.csBlock = function(number) {
    var cs = this.contentStream();

    expect(cs).to.have.property('blocks');

    expect(cs.blocks).to.have.property(number);

    return cs.blocks[number];
  };

  this.When(/^I open the content\-manager$/, function () {
    browser.url("/prototypes/content-manager");

    this.context = this.css('#content-manager').waitForVisible();
  });  

  this.Then(/^I see the blocks to add:$/, function (table) {
    var actual = this.openContentManagerMenu()
      .css('a').getTexts();

    var expected = [];
    table.hashes().forEach(function(hash) {
      expected.push(hash.label);
    });

    expect(actual).to.be.eql(expected);
  });

  this.When(/^I add a new block "([^"]*)"$/, function (arg1) {
    this.openContentManagerMenu()
      .css('a:contains("'+arg1+'")').exists().click();
  });

  this.When(/^I write "([^"]*)" into the textblock (\d+)$/, function (text, number) {
    this.cmPanel(number)
      .css('textarea').exists().get().setValue(text);
  });

  this.Then(/^the content\-stream contains a text block (\d+) with content "([^"]*)"$/, function (number, content) {
    var block = this.csBlock(number);

    expect(block).to.have.property('type', 'markdown');
    expect(block).to.have.property('markdown', content);
    expect(block).to.have.property('uuid');
  });

  this.When(/^I write "([^"]*)" into the textarea from block (\d+)$/, function (text, number) {
    this.cmPanel(number)
      .css('.form-group textarea').exists().get().setValue(text);
  });

  this.Then(/^the content\-stream contains a block (\d+) with question "([^"]*)" and answer "([^"]*)"$/, function (arg1, arg2, arg3) {
    var block = this.csBlock(number);

    expect(block).to.have.property('type', 'markdown');
  });
}