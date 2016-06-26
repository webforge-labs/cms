define(['jquery', 'knockout', './MainModel', 'amplify', 'cms/transfer-effect', './ko-bindings/cms-tab', './ko-bindings/moment', 'bootstrap/button', 'bootstrap/transition', 'bootstrap/collapse', 'bootstrap/dropdown', 'bootstrap-notify'], function($, ko, Main, amplify, transferEffect) {

  return function(data) {
    var main = new Main(data);
    define('cms/modules/main', main);

    ko.applyBindings(main);

    main.loadStoredTabs();
    main.loaded();

    $(document).ready(function() {
      main.domLoaded();

      amplify.subscribe('cms.tabs.open', function(tab, e) {
        if (e && e.currentTarget) {
          var options = {
            from: e.currentTarget,
            to: '.tabs-container .dropdown-toggle',
            duration: 650,
            easing: 'swing'
          };

          transferEffect(options);
        }
      });
    });
  };
});