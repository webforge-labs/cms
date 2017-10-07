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
      if (!tab.isLoading()) {
        tab.isLoading(true);

        dispatcher.sendPromised('GET', tab.url(), undefined, 'text/html')
          .then(
            function(response) {
              tab.isLoading(false);
              tab.contentLoaded(response);
            },
            function(err) {
              tab.isLoading(false);

              if (!err.response) throw err;

              tab.contentLoadingError(err.response);
            }
          );
      }
    };

    this.open = function(tab, e) {
      if (!tabs.contains(tab)) {
        amplify.publish('cms.tabs.open', tab, e);
        that.add(tab);
      }

      that.select(tab, e);

      return tab;
    };

    this.select = function(tab, e) {
      var activeTab = that.activeTab();

      if (activeTab) {
        activeTab.deactivate();
      }

      var newProps = { label: tab.label(), url: tab.url() };

      // get the tab that is already attached, to avoid duplicate objects
      tab = tabs.get(tab.id());

      ko.utils.objectForEach(newProps, function(key, value) {
        if (value && tab[key]() != value) {
          tab[key](value);
        }
      });

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

    this.closeById = function(tabId) {
      ko.utils.arrayForEach(that.openedTabs(), function(tab) {
        if (tab.id() == tabId) {
          that.close(tab);
          return false;
        }
      });
    };

    this.close = function(tab) {
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