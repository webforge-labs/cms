define(['jquery', 'knockout', './MainModel', './ko-bindings/cms-tab', './ko-bindings/moment', 'bootstrap/button', 'bootstrap/transition', 'bootstrap/collapse', 'bootstrap/dropdown', 'bootstrap-notify'], function($, ko, Main) {

  return function(data) {
    var main = new Main(data);
    define('cms/modules/main', main);

    ko.applyBindings(main);

    main.loadStoredTabs();
    main.loaded();

    // initialize only ones: this won't update on resize (because this is very inperformant)
    $(document).ready(function() {
      var $tabsContainer = $('.tabs-container');
      var $scroller = $tabsContainer.find('.tabs-scroller');
      $tabsContainer.on('click', '.btn-group .btn', function() {
        var offset = 79;
        var direction = $(this).is('.left') ? 'left' : 'right';
        var position = $scroller.scrollLeft();

        if (direction == 'right') {
          var scrollerWidth = 0;

          /*
          @TODO don't scroll too much
          var $lastTab = $scroller.find('ul > li:last');
          var scrollerWidth = $lastTab.position().left + $lastTab.outerWidth(); // the width of all tabs combined in the tab-bar
          */

          $scroller.animate({scrollLeft: position+offset}, 150);
        } else {
          offset = offset * -1;
          $scroller.animate({scrollLeft: position+offset}, 150);
        }
      });
    });
  };
});