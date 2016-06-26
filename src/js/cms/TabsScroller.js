define(['jquery', 'amplify'], function($, amplify) {

  return function TabsScroller() {
    var that = this;
    var animationTime = 100;

    this.init = function() {
      that.$tabsContainer = $('.tabs-container');
      that.$scroller = that.$tabsContainer.find('.tabs-scroller');

      that.$tabsContainer.on('click', '.btn-group .btn', function() {
        var offset = 79;
        var direction = $(this).is('.left') ? 'left' : 'right';
        var position = that.$scroller.scrollLeft();

        if (direction == 'right') {
          // the width of all tabs displayed in the viewport
          var tabsWidth = 0;
          that.$scroller.find('ul > li').each(function() {
            tabsWidth += $(this).outerWidth();
          });

          // the viewport width
          var scrollerWidth = that.$scroller.innerWidth();

          if (position+offset+scrollerWidth < tabsWidth) {
            // scroll more to the right
            that.$scroller.animate({scrollLeft: position+offset}, animationTime);
          } else {
            // scroll to the exact end
            that.$scroller.animate({scrollLeft: tabsWidth-scrollerWidth}, animationTime);
          }

        } else {
          offset = offset * -1;
          that.$scroller.animate({scrollLeft: position+offset}, animationTime);
        }
      });

      amplify.subscribe('cms.tabs.active', that.scrollToActiveTab);
    };

    this.scrollToActiveTab = function() {
      var $tab = that.$tabsContainer.find('[role="tabs-nav"] > li.active');

      if ($tab.length) {
        that.$scroller.animate({scrollLeft: $tab.position().left-that.$scroller.innerWidth()/2}, animationTime);
      }
    };
  };
});