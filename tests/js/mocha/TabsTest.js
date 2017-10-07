var bootstrap = require('./bootstrap');
var boot = bootstrap({ context: __filename });

var expect = boot.expect;

// we replace amplify with a fake
boot.define('amplify', function() {
  return {
    publish: function() {
    }
  }
});

boot.define('jquery', function() {
  return function() {

  };
});

var dispatcher = boot.injectFakeDispatcher();
var Tabs = boot.requirejs('cms/TabsModel');
var Tab = boot.requirejs('cms/TabModel');

describe('Tabs', function() {

  beforeEach(function() { // execute once
    var that = this;

    this.tabs = new Tabs();

    this.dashboard = new Tab({
      id: 'dashboardid',
      url: '/cms/dashboard',
      icon: 'dashboard',
      label: 'Dashboard'
    });

    this.postTab = new Tab({
      id: 'post-7',
      url: '/cms/posts/7',
      icon: 'envelope',
      label: 'Post #7'
    });

    this.ptabs = [];
    for (var i = 10; i<=20; i++) {
      this.ptabs.push(new Tab({
        id: 'post-'+i,
        url: '/cms/posts/'+i,
        icon: 'envelope',
        label: 'Post #'+i
      }));
    }

    this.expectOkDashboardRequest = function() {
      dispatcher.expect('GET', '/cms/dashboard').respond(200, '<h1>Dashboard</h1> Content', { format: 'html' });
    };

    this.expectOkPostTabRequest = function() {
      dispatcher.expect('GET', '/cms/posts/7').respond(200, '<h1>Post 7</h1> Content', { format: 'html' });
    };

    dispatcher.reset();
  });

  it('should have an empty list of opened tabs', function () {
    expect(this.tabs.openedTabs()).to.have.a.lengthOf(0);
  });

  it('should have a tab, when a tab is added', function() {
    this.tabs.add(this.dashboard);
    expect(this.tabs.openedTabs()).to.have.a.lengthOf(1);
  });

  it('should add and activate a new tab when its opened', function() {
    this.expectOkPostTabRequest();

    this.tabs.open(this.postTab);

    var activeTab = this.tabs.activeTab();
    expect(activeTab).to.be.ok;
    expect(activeTab.id()).to.be.equal('post-7');
  });

  it('should activate the next tab when its clicked once', function() {
    this.expectOkDashboardRequest();
    this.expectOkPostTabRequest();

    this.tabs.add(this.dashboard);
    this.tabs.select(this.dashboard);

    this.tabs.open(this.postTab);

    expect(this.tabs.activeTab().id()).to.be.equal('post-7');
  });

  it('should activate and load an already opened tab, when a new tab is activated and then closed', function() {
    this.expectOkPostTabRequest();
    this.expectOkDashboardRequest();

    this.tabs.add(this.dashboard);

    this.tabs.open(this.postTab);

    expect(this.tabs.activeTab().id()).to.be.equal('post-7');
    
    this.tabs.close(this.postTab);
    var activeTab = this.tabs.activeTab();
    expect(activeTab, 'a new active tab should be set').to.be.ok;
    expect(activeTab.id()).to.be.equal('dashboardid');
    expect(activeTab.isActive(), 'should be active').to.be.true;
  });

  it('should work when all the tabs added are closed', function() {
    this.expectOkPostTabRequest();
    this.expectOkDashboardRequest();

    this.tabs.add(this.dashboard);

    this.tabs.open(this.postTab);

    this.tabs.close(this.postTab);
    this.tabs.close(this.dashboard);

    expect(this.tabs.openedTabs()).to.have.a.lengthOf(0);
    expect(this.tabs.activeTab()).to.be.undefined;
  });

  it('should cache the loaded content of the tab if it is opened or activated', function(done) {
    var that = this;

    // expect only one request
    this.expectOkPostTabRequest();

    this.tabs.open(this.postTab);

    setTimeout(function() {
      // this would fail because the dispatcher does have only one expectation for the postTab request
      that.tabs.open(that.postTab);
      done();
    }, 30);
  });

  it('should do nothing while its already loading the tab', function(done) {
    var that = this;

    // expect only one request
    this.expectOkPostTabRequest();

    this.tabs.open(this.postTab);

    // trigger twice (before the first can be finished)
    this.tabs.open(this.postTab);

    setTimeout(function() {
      // this would fail because the dispatcher does have only one expectation for the postTab request
      that.tabs.open(that.postTab);
      done();
    }, 30);
  });

  it('should refresh the tab infos, if tab was added before and then reopened', function() {
    var wrongPostTab = new Tab({
      id: this.postTab.id(),
      url: this.postTab.url(),
      icon: 'envelope',
      label: 'Some older label'
    });
    this.tabs.add(wrongPostTab);

    this.expectOkPostTabRequest();
    this.tabs.open(this.postTab);

    var activeTab = this.tabs.activeTab();
    expect(activeTab).to.be.ok;
    expect(activeTab.label()).to.be.not.equal('Some older label');
    expect(activeTab.label()).to.be.equal('Post #7');
  });

});