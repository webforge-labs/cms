define(['knockout', 'knockout-mapping', './TabsModel', './TabModel', 'cms/FileManager/ns', 'cms/modules/dispatcher', 'amplify', 'bootstrap-notify', 'bootstrap/modal', 'cms/ko-bindings/invisible'], function(ko, koMapping, Tabs, Tab, FileManager, dispatcher, amplify, notify, bsModal) {
  
  return function(data) {
    var that = this;

    this.loading = ko.observable(true);

    koMapping.fromJS(data, {ignore:[]}, this);

    this.tabs = new Tabs();
    this.fileManager = ko.observable();

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
        that.tabs.reload();
      });
    };

    this.openFileManager = function(options) {
      // @TODO spin something
      dispatcher.send('GET', '/cms/media')
        .done(function(response) {
          if (!that.fileManager()) {
            var fileManager = new FileManager.Manager(response.body);
            that.fileManager(fileManager);
          } else {
            that.fileManager().refreshData(response.body);
          }

          that.fileManager().reset(options);
      
          var $modal = $('#file-manager-modal');
          $modal.modal({'show': true});

        }).fail(function(err, response) {
          amplify.publish('cms.ajax.error', response);
        });
    }

    this.onAjaxError = function(response) {
      var info = "";

      if (response.error && response.error.message) {
        info += "Technischer Fehler: "+response.error.message;
      }

      $.notify({
        message: "Oh mist. Hier ist was schief gegangen. "+info
      },{
        delay: 0,
        type: 'danger'
      });
    };

    this.loaded = function() {
      that.loading(false);
    };

    this.createContext = function(name, model, $context) {
      that[name] = model;
      return that.bindTo($context);
    };

    this.bindTo = function($element) {
      if (!$element.length) {
        throw new Error('you provided an empty jquery object in main.bindTo()');
      }

      amplify.subscribe('cms.ajax.error', that.onAjaxError);

      ko.applyBindings(that, $element.get(0));
    };
  };
});