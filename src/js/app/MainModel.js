define(['knockout', 'knockout-mapping', './TabsModel', './TabModel'], function(ko, koMapping, Tabs, Tab) {
  
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

    this.tabs.add(dashboard);
    this.tabs.select(dashboard);
    
  };

});