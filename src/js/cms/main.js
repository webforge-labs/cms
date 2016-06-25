define(['jquery', 'knockout', './MainModel', './ko-bindings/cms-tab', './ko-bindings/moment', 'bootstrap/button', 'bootstrap/transition', 'bootstrap/collapse', 'bootstrap/dropdown', 'bootstrap-notify'], function($, ko, Main) {

  return function(data) {
    var main = new Main(data);
    define('cms/modules/main', main);

    ko.applyBindings(main);

    main.loadStoredTabs();
    main.loaded();

    $(document).ready(function() {
      var $tabsContainer = $('.tabs-container');
      var $scroller = $tabsContainer.find('.tabs-scroller');
      var animationTime = 100;
      $tabsContainer.on('click', '.btn-group .btn', function() {
        var offset = 79;
        var direction = $(this).is('.left') ? 'left' : 'right';
        var position = $scroller.scrollLeft();

        if (direction == 'right') {
          // the width of all tabs displayed in the viewport
          var tabsWidth = 0;
          $scroller.find('ul > li').each(function() {
            tabsWidth += $(this).outerWidth();
          });

          // the viewport width
          var scrollerWidth = $scroller.innerWidth();

          if (position+offset+scrollerWidth < tabsWidth) {
            // scroll more to the right
            $scroller.animate({scrollLeft: position+offset}, animationTime);
          } else {
            // scroll to the exact end
            $scroller.animate({scrollLeft: tabsWidth-scrollerWidth}, animationTime);
          }

        } else {
          offset = offset * -1;
          $scroller.animate({scrollLeft: position+offset}, animationTime);
        }
      });
    });
  };
});