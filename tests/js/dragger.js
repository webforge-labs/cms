module.exports = function($, window) {
  var that = this;
  var document = window.document;

  var rkeyEvent = /^key/;
  var rmouseEvent = /^(?:mouse|contextmenu)|click/;

  this.findCorner = function ($elem, $document) {
    var offset = $elem.offset();
    console.log(offset);
    console.log(rect = $elem.get(0).getBoundingClientRect());
    console.log(window.pageYOffset);
    console.log($elem.get(0).ownerDocument.documentElement.clientTop);

    return {
      x: offset.left - $document.scrollLeft(),
      y: offset.top - $document.scrollTop()
    };
  };

  this.simulateDrag = function($target, options) {
    var target = $target.get(0);

    var i = 0,
      eventDoc = target.ownerDocument || target._ownerDocument,
      center = that.findCorner($target, $(eventDoc)),
      x = Math.floor( center.x ),
      y = Math.floor( center.y ),
      coord = { clientX: x, clientY: y },
      dx = options.dx || ( options.x !== undefined ? options.x - x : 0 ),
      dy = options.dy || ( options.y !== undefined ? options.y - y : 0 ),
      moves = options.moves || 3;

    that.simulateEvent( target, "mousedown", coord );

    for ( ; i < moves ; i++ ) {
      x += dx / moves;
      y += dy / moves;

      coord = {
        clientX: Math.round( x ),
        clientY: Math.round( y )
      };

      that.simulateEvent( eventDoc, "mousemove", coord );
    }

    if ( $.contains( eventDoc, target ) ) {
      that.simulateEvent( target, "mouseup", coord );
      that.simulateEvent( target, "click", coord );
    } else {
      that.simulateEvent( eventDoc, "mouseup", coord );
    }
  };

  this.simulateEvent = function( elem, type, options ) {
    var event = that.createEvent( type, options );
    that.dispatchEvent( elem, type, event, options );
  },

  this.dispatchEvent = function( elem, type, event ) {
    if ( elem[ type ] ) {
      elem[ type ]();
    } else if ( elem.dispatchEvent ) {
      elem.dispatchEvent( event );
    } else if ( elem.fireEvent ) {
      elem.fireEvent( "on" + type, event );
    }
  },

  this.createEvent = function( type, options ) {
    if ( rkeyEvent.test( type ) ) {
      return that.keyEvent( type, options );
    }

    if ( rmouseEvent.test( type ) ) {
      return that.mouseEvent( type, options );
    }
  },

  this.mouseEvent = function( type, options ) {
    var event, eventDoc, doc, body;
    options = $.extend({
      bubbles: true,
      cancelable: (type !== "mousemove"),
      view: window,
      detail: 0,
      screenX: 0,
      screenY: 0,
      clientX: 1,
      clientY: 1,
      ctrlKey: false,
      altKey: false,
      shiftKey: false,
      metaKey: false,
      button: 0,
      relatedTarget: undefined
    }, options );

    if ( document.createEvent ) {
      event = document.createEvent( "MouseEvents" );
      event.initMouseEvent( type, options.bubbles, options.cancelable,
        options.view, options.detail,
        options.screenX, options.screenY, options.clientX, options.clientY,
        options.ctrlKey, options.altKey, options.shiftKey, options.metaKey,
        options.button, options.relatedTarget || document.body.parentNode );

      // IE 9+ creates events with pageX and pageY set to 0.
      // Trying to modify the properties throws an error,
      // so we define getters to return the correct values.
      if ( event.pageX === 0 && event.pageY === 0 && Object.defineProperty ) {
        eventDoc = event.relatedTarget.ownerDocument || document;
        doc = eventDoc.documentElement;
        body = eventDoc.body;

        Object.defineProperty( event, "pageX", {
          get: function() {
            return options.clientX +
              ( doc && doc.scrollLeft || body && body.scrollLeft || 0 ) -
              ( doc && doc.clientLeft || body && body.clientLeft || 0 );
          }
        });
        Object.defineProperty( event, "pageY", {
          get: function() {
            return options.clientY +
              ( doc && doc.scrollTop || body && body.scrollTop || 0 ) -
              ( doc && doc.clientTop || body && body.clientTop || 0 );
          }
        });
      }
    } else if ( document.createEventObject ) {
      event = document.createEventObject();
      $.extend( event, options );
      // standards event.button uses constants defined here: http://msdn.microsoft.com/en-us/library/ie/ff974877(v=vs.85).aspx
      // old IE event.button uses constants defined here: http://msdn.microsoft.com/en-us/library/ie/ms533544(v=vs.85).aspx
      // so we actually need to map the standard back to oldIE
      event.button = {
        0: 1,
        1: 4,
        2: 2
      }[ event.button ] || ( event.button === -1 ? 0 : event.button );
    }

    return event;
  };  

};