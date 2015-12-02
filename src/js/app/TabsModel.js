define(['knockout', 'knockout-mapping', './KnockoutCollection', 'modules/dispatcher', 'amplify'], function(ko, koMapping, KnockoutCollection, dispatcher, amplify) {

  return function() {
    var that = this;

    var tabs = new KnockoutCollection([], {key: 'id'});
    this.activeTab = ko.observable(undefined);

    this.openedTabs = tabs.items;

    this.loadedTabs = ko.computed(function() {
      return ko.utils.arrayFilter(that.openedTabs(), function(tab) {
        return tab.wasLoaded();
      });
    });

    var loadContents = function(tab) {
      dispatcher.send('GET', tab.url(), undefined, 'text/html')
        .done(function(response) {
          tab.contentLoaded(response);
        })
        .fail(function(response) {
          tab.contentLoadingError(response);
        });
    };

    this.open = function(tab, e) {
      if (!tabs.contains(tab)) {
        that.add(tab);
      } else {
        that.select(tab, e);
      }

      return tab;
    };

    this.select = function(tab, e) {
      var activeTab = that.activeTab();

      if (activeTab) {
        activeTab.deactivate();
      }

      tab.activate();
      that.activeTab(tab);
      amplify.publish('cms.tabs.active', tab);

      if (!tab.wasLoaded()) {
        loadContents(tab);
      }
    };

    this.selectById = function(tabId) {
      ko.utils.arrayForEach(that.openedTabs(), function(tab) {
        if (tab.id() == tabId) {
          that.select(tab);
          return false;
        }
      });
    };

    this.close = function(tab, e) {
      var activeTab = that.activeTab();

      tab.deactivate();
      
      // if tab was active we have no active tab anymore
      if (activeTab.id() === tab.id()) {
        that.activeTab(undefined);
      }

      tabs.removeElement(tab);
      amplify.publish('cms.tabs.closed', tab);
    };

    this.add = function(tab) {
      tabs.add(tab);
      amplify.publish('cms.tabs.added', tab);
    };

    this.reload = function() {
      var tab = that.activeTab();

      tab.reset();
      loadContents(tab);
    };
  };
});