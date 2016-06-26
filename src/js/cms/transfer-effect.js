define(['jquery', 'bluebird'], function($, Promise) {

  return function transferEffect(o) {
    return new Promise(function (fulfill, reject) {
      var elem = $( o.from ),
        target = $( o.to ),
        targetFixed = target.css( "position" ) === "fixed",
        body = $("body"),
        fixTop = targetFixed ? body.scrollTop() : 0,
        fixLeft = targetFixed ? body.scrollLeft() : 0,
        endPosition = target.offset(),
        animation = {
          top: endPosition.top - fixTop,
          left: endPosition.left - fixLeft,
          height: target.innerHeight(),
          width: target.innerWidth()
        },
        startPosition = elem.offset(),
        transfer = $( "<div class='effects-transfer'></div>" )
          .appendTo( body )
          .css({
            top: startPosition.top - fixTop,
            left: startPosition.left - fixLeft,
            height: elem.innerHeight(),
            width: elem.innerWidth(),
            position: targetFixed ? "fixed" : "absolute"
          })
          .animate( animation, o.duration, o.easing, function() {
            transfer.remove();
            fulfill();
          });
    });
  };

});