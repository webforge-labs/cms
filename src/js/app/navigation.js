define(['knockout', 'knockout-mapping', 'jquery-nestable'], function(ko, koMapping, nulll) {

  var NavigationWidget = function(data, $tab) {
    var that = this;

    koMapping.fromJS(data, {}, this);

    var nestedOptions = {
      //listNodeName: 'ol',
      //itemNodeName: 'li',
      //listClass: 'dd-list',
      expandBtnHTML: '',
      collapseBtnHTML: '',
      maxDepth: 20,
      //placeClass: 'ui-state-highlight ui-corner-all'
    };

    var $dd = $tab.find('.dd');

    $dd.nestable(nestedOptions);

    this.serialize = function() {
      var data, depth = 0, list = $dd.data('nestable');

      var step  = function(level, depth, parent) {
        var array = [], items = level.children(list.options.itemNodeName);

        items.each(function() {
          var $li   = $(this);
          var item = koMapping.toJS(ko.dataFor($li.get(0)));
          item.parent = parent;
          var sub  = $li.children(list.options.listNodeName);
          
          if (sub.length) {
            item.children = step(sub, depth + 1, item);
          }

          array.push(item);
        });

        return array;
      };

      data = step(list.el.find(list.options.listNodeName).first(), depth, null);

      return data;
    };

    this.save = function() {
      console.log(that.serialize());
    }
  };

  return function(data, main, $bindTo) {
    var nav = new NavigationWidget(data, $bindTo);

    main.navigation = nav;

    main.bindTo($bindTo);

    return nav;
  };


});