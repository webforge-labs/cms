define(['require', 'knockout', 'jquery', 'uikit-src/uikit-core'], function(require, ko, $, UIkit) {

  var getTemplateOptions = function(options) {
    var result = {};

     result.foreach = options.foreach;

     ko.utils.arrayForEach(["afterAdd", "afterRender", "as", "beforeRemove", "includeDestroyed", "templateEngine", "templateOptions", "nodes"], function (option) {
       if (options.hasOwnProperty(option)) {
         result[option] = options[option];
       }
     });

     return result;
  };

  // note: this does not work on mobile devices!
  ko.bindingHandlers.ukSortable = {
    init: function(element, valueAccessor, allBindingsAccessor, data, context) {
      var options = ko.unwrap(valueAccessor());

      var templateOptions = getTemplateOptions(ko.unwrap(valueAccessor()));

      ko.bindingHandlers.template.init(element, function() { return templateOptions; }, allBindingsAccessor, data, context);

      require(['uikit', 'uikit-src/components/sortable'], function(UIkit) {
        var $sortable = $(element);
        var sortable = UIkit.sortable(element, {
          animation: 120,
          threshold: 10,
          handle: options.handle
        });

        if (options.change) {
          $sortable.on('change.uk.sortable', function(e, srtbl, $dragged, type) {
            if (!$dragged) return;

            // is this some nested sortable or our sortable?
            if (!srtbl.$el.is($sortable)) return;
  
            var draggedItem = ko.dataFor($dragged.get(0));
            var newIndex = $sortable.children().index($dragged);

            options.change(draggedItem, newIndex, type);

            // the foreach.update binding will add this to dom for us, if we change the underlying model in the options.change() callback
            // then we would have 2 DOM Elements for the same item in the dom (because foreach wont render everything from scratch)
            // so we remove the "duplicated" item here
            $dragged.remove();
          });
        }

        $sortable.on('start.uk.sortable', function(e, srtbl, currentlyDraggingElement, draggingPlaceholder) {
          var $current = $(currentlyDraggingElement);

          draggingPlaceholder.width($current.width());
          draggingPlaceholder.height($current.height());
        });

      });

      return { 'controlsDescendantBindings': true };
    },
    update: function(element, valueAccessor, allBindingsAccessor, data, context) {
      // means: the knockout-sortable-array is changed
      var templateOptions = getTemplateOptions(ko.unwrap(valueAccessor()));

      ko.bindingHandlers.template.update(element, function() { return templateOptions; }, allBindingsAccessor, data, context);
    }
  };

});