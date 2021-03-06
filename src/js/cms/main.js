define(['jquery', 'knockout', './MainModel', 'amplify', './ko-bindings/cms-tab', './ko-bindings/moment', 'bootstrap/button', 'bootstrap/transition', 'bootstrap/collapse', 'bootstrap/dropdown', 'bootstrap-notify'], function($, ko, Main, amplify) {

  return function(data) {
    var main = new Main(data);
    define('cms/modules/main', main);

    ko.applyBindings(main);

    main.loadStoredTabs();
    main.loaded();

    $(document).ready(function() {
      main.domLoaded();

      //'.navbar-collapse [data-open-on-navbar-expand="true"]'
      $('body').on('shown.bs.collapse', '.navbar-collapse', function(e) {
        var $submenu = $(this).find('[data-open-on-navbar-expand="true"]').first();

        if ($submenu.length) {
          $submenu.dropdown('toggle');
        }
      });

      amplify.subscribe('cms.close-the-nav', function(e) {
        $('.navbar-collapse:first').collapse('hide');
      });


      $.notifyDefaults({
        offset: {
          x: 0,
          y: 60
        },
        placement: {
          from: 'top',
          align: 'center'
        },
        spacing: 60,
        z_index: 2000 // bootstrap-modal is like 1050
      });
    });
  };
});