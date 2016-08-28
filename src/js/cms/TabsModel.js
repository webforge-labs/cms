define(['knockout', 'knockout-mapping', 'knockout-collection', 'cms/modules/dispatcher', 'amplify', './TabsScroller'], function(ko, koMapping, KnockoutCollection, dispatcher, amplify, TabsScroller) {

  return function() {
    var that = this;

    that.scroller = new TabsScroller();

    var tabs = new KnockoutCollection([], {key: 'id'});
    this.activeTab = ko.observable(undefined);

    this.openedTabs = tabs.items;

    this.loadedTabs = ko.computed(function() {
      return ko.utils.arrayFilter(that.openedTabs(), function(tab) {
        return tab.wasLoaded();
      });
    });


    this.collapsedTabs = ko.computed(function() {
      return that.openedTabs();
    });

    var loadContents = function(tab) {
      dispatcher.sendPromised('GET', tab.url(), undefined, 'text/html')
        .then(
          function(response) {
            tab.contentLoaded(response);
          },
          function(err) {
            if (!err.response) throw err;

            tab.contentLoadingError(err.response);
          }
        );
    };

    this.open = function(tab, e) {
      if (!tabs.contains(tab)) {
        amplify.publish('cms.tabs.open', tab, e);
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

      // get the tab that is already attached, to avoid duplicate objects
      tab = tabs.get(tab.id());

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
      tab.deactivate();
      tabs.remove(tab);
      
      // if tab was active we switch to the last tab in the list
      // maybe there's a better algo
      var activeTab = that.activeTab();
      if (activeTab && activeTab.id() === tab.id()) {

        if (tabs.length > 0) {
          nextTab = tabs.toArray().pop();
          that.select(nextTab);
        } else {
          that.activeTab(undefined);
        }
      }

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

    this.domLoaded = function() {
      that.scroller.init();
    };
  };
});