define(['jquery', 'knockout', './MainModel', './ko-bindings/cms-tab', './ko-bindings/moment', 'bootstrap/button', 'bootstrap/transition', 'bootstrap/collapse', 'bootstrap/dropdown', 'bootstrap-notify'], function($, ko, Main) {

  return function(data) {
    var main = new Main(data);
    define('cms/modules/main', main);

    ko.applyBindings(main);

    main.loadStoredTabs();
  };
});