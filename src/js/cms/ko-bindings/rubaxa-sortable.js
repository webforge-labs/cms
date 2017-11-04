define(['require', 'knockout', 'jquery', 'sortable'], function(require, ko, $, Sortable) {

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

  ko.bindingHandlers.rubaxaSortable = {
    init: function(element, valueAccessor, allBindingsAccessor, data, context) {
      var options = ko.unwrap(valueAccessor());

      var sortableOptions = {
        ghostClass: "rubaxa-sortable-ghost",
        dragClass: "rubaxa-sortable-drag",
        fallbackOnBody: true,
        group: { name: "standard", pull:false },
        handle: options.handle,
        forceFallback: true
      };

      if (options.change) {
        sortableOptions.onUpdate = function(e) {
          //e.to;    // target list
          //e.from;  // previous list
          //e.oldIndex;  // element's old index within old parent
          //e.newIndex;  // element's new index within new parent

          if (!e.item) return;

          // is this some nested sortable or our sortable?
          // i think this does not need to be checked with sortablejs

          var $dragged = $(e.item);
          var draggedItem = ko.dataFor(e.item);

          options.change(draggedItem, e.newIndex);

          // the foreach.update binding will add this to dom for us, if we change the underlying model in the options.change() callback
          // then we would have 2 DOM Elements for the same item in the dom (because foreach wont render everything from scratch)
          // so we remove the "duplicated" item here
          $dragged.remove();
        };
      }

      /* init the sortable on dom and connect with model */
      var sortable = Sortable.create(element, sortableOptions);

      /* initialize the template binding, that makes foreach etc */
      var templateOptions = getTemplateOptions(ko.unwrap(valueAccessor()));

      return ko.bindingHandlers.template.init(element, function() { return templateOptions; }, allBindingsAccessor, data, context);
    },
    update: function(element, valueAccessor, allBindingsAccessor, data, context) {
      // means: the knockout-sortable-array is changed
      var templateOptions = getTemplateOptions(ko.unwrap(valueAccessor()));

      ko.bindingHandlers.template.update(element, function() { return templateOptions; }, allBindingsAccessor, data, context);
    }
  };

});