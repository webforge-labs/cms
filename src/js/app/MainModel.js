define(['knockout', 'knockout-mapping', './TabsModel', './TabModel', 'amplify'], function(ko, koMapping, Tabs, Tab, amplify) {
  
  return function(data) {
    var that = this;

    koMapping.fromJS(data, {ignore:[]}, this);

    this.tabs = new Tabs();

    var dashboard = new Tab({
      id: 'dashboard',
      url: '/cms/dashboard',
      icon: 'dashboard',
      label: 'Dashboard'
    });


    this.loadStoredTabs = function() {
      that.tabs.add(dashboard);

      var storedTabs = amplify.store('cms.tabs');

      if (storedTabs) {
        _.forEach(storedTabs, function(tabData) {
          var tab = new Tab(tabData);
          that.tabs.add(tab);
        });
      } else {
        amplify.store('cms.tabs', {});
      }

      var storedActiveId = amplify.store('cms.tabs.active');
      if (storedActiveId) {
        that.tabs.selectById(storedActiveId);
      } else {
        that.tabs.select(dashboard);
      }

      amplify.subscribe('cms.tabs.added', function(tab) {
        var storedTabs = amplify.store('cms.tabs'); // get current stored tabs (always)

        storedTabs[tab.id()] = tab.serialize();

        amplify.store('cms.tabs', storedTabs);
      });

      amplify.subscribe('cms.tabs.closed', function(tab) {
        var storedTabs = amplify.store('cms.tabs'); // get current stored tabs (always)

        if (storedTabs[tab.id()]) {
          delete storedTabs[tab.id()];
          amplify.store('cms.tabs', storedTabs);
        }
      });

      amplify.subscribe('cms.tabs.active', function(tab) {
        amplify.store('cms.tabs.active', tab.id());
      });

      amplify.subscribe('cms.tabs.reload', function(tab) {
        console.log('reloading tabs');
        that.tabs.reload();
      });
    };

    this.bindTo = function($element) {
      if (!$element.length) {
        throw new Error('you provided an empty jquery object in main.bindTo()');
      }

      ko.applyBindings(that, $element.get(0));
    };
  };
});