var bootstrap = require('./bootstrap');
var boot = bootstrap({ context: __filename });

var expect = boot.expect;
var _ = require('lodash');

GLOBAL.window = {
  location: {
    search: ''
  }
};

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

  before(function() { // execute once
    //this.timeout(20000);
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

  });

  it('opened tabs list should be empty initialized', function () {
    expect(this.tabs.openedTabs()).to.have.a.lengthOf(0);
  });

  it('should have a tab if it is added', function() {
    this.tabs.add(this.dashboard);
    expect(this.tabs.openedTabs()).to.have.a.lengthOf(1);
  });

  it('should activate the tab when its selected', function() {
    this.expectOkDashboardRequest();

    this.tabs.add(this.dashboard);
    this.tabs.select(this.dashboard);

    var activeTab = this.tabs.activeTab();
    expect(activeTab).to.be.ok;
    expect(activeTab.id()).to.be.equal('dashboardid');
  });

  it('should activate the next tab when its clicked twice', function() {
    this.expectOkDashboardRequest();
    this.expectOkPostTabRequest();

    this.tabs.add(this.dashboard);
    this.tabs.select(this.dashboard);

    this.tabs.open(this.postTab);
    this.tabs.open(this.postTab);

    expect(this.tabs.activeTab().id()).to.be.equal('post-7');
  });

  it('should activate and load an already opened tab, when a new tab is activated and then closed', function() {
    this.expectOkPostTabRequest();
    this.expectOkDashboardRequest();

    this.tabs.add(this.dashboard);
    this.tabs.add(this.postTab);

    this.tabs.select(this.postTab);
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
    this.tabs.add(this.postTab);

    this.tabs.select(this.postTab);

    this.tabs.close(this.postTab);
    this.tabs.close(this.dashboard);

    expect(this.tabs.openedTabs()).to.have.a.lengthOf(0);
    expect(this.tabs.activeTab()).to.be.undefined;
  });
});