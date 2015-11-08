define(['knockout', 'knockout-mapping', './KnockoutCollection', 'modules/dispatcher'], function(ko, koMapping, KnockoutCollection, dispatcher) {

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
        tabs.add(tab);
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

      if (!tab.wasLoaded()) {
        loadContents(tab);
      }
    };

    this.add = function(tab) {
      tabs.add(tab);
    };
  };
});