module.exports = function() {

  require('./setup')(this);

  this.When(/^I open the file-manager$/, function () {
    console.log('step definition');
    client.url("/prototypes/file-manager");

    this.context = this.css('.file-manager').waitForVisible();
  });

  this.When(/^I click on the folder "([^"]*)"$/, function (foldername) {
    this.css('.folder-row .thumbnail:has(.filename:contains("'+foldername+'"))').waitForVisible()
      .css('a.top').waitForVisible().click();
  });

  this.When(/^I create a folder "([^"]*)"$/, function (foldername) {
    this.onPrompt(foldername);
    client.saveScreenshot('.screenshots/before.png');

    this.css('.btn:contains("Neuer Ordner")').isVisible().click();

    client.saveScreenshot('.screenshots/oink.png');
    this.css('ul.root:contains("'+foldername+'")').waitForExist(100);

    /* this is only valid for creating a folder in navigator mode:
    this.css('.well:contains("ist leer")').waitForExist();
    this.css('.breadcrumb').exists().css('a:last').exists().text(foldername);
    */
  });

  this.When(/^I click on "([^"]*)" in the crumbbar$/, function (arg1) {
    this.css('.breadcrumb').exists()
      .css('a:contains("'+arg1+'")').click();
  });

  this.When(/^I select the file "([^"]*)"$/, function (file) {
    this.css('.folder-row .thumbnail:has(.filename:contains("'+file+'"))').exists()
      .css('.checkbox-inline').click();
  });

  this.Then(/^I see the directory tree with data$/, function () {
    this.css('ul.root').isVisible()
      .css('li:contains(imme-usa.jpg)').count(0).end()
      .css('li:has(> a:first:contains("About"))').exists().end()
      .css('li:has(> a:first:contains("montpellier"))').exists()
        .css('li:contains("traeumen-nach-disney")').exists()
      .end();
  });

  this.Then(/^there is the file "([^"]*)"$/, function (arg1) {
    this.css('.folder-row .thumbnail:has(.filename:contains("'+arg1+'"))').exists()
  });

  this.When(/^I click on "([^"]*)" \/ "([^"]*)" \/ "([^"]*)" from tree$/, function (arg1, arg2, arg3) {
    this.css('ul.root').exists()
      .css('li:has(> a:first:contains("'+arg1+'"))')
        .css('li:has(> a:first:contains("'+arg2+'"))')
          .css('li > a:first:contains("'+arg3+'")').click();
  });

  this.When(/^I click on "([^"]*)" \/ "([^"]*)" from tree$/, function (arg1, arg2) {
    this.css('ul.root').exists()
      .css('li:has(> a:first:contains("'+arg1+'"))')
        .css('li > a:first:contains("'+arg2+'")').click();
  });
};