define(['knockout', 'knockout-mapping', 'jquery-nestable'], function(ko, koMapping, nulll) {

  var NavigationWidget = function(data, $tab) {
    var that = this;

    var nodes = {};

    var idKey = function(data) {
      return ko.unwrap(data.id);
    };

    var mapping = {
      navigation: {
        key: idKey,
        create: function(options) {
          var node = koMapping.fromJS(options.data, {children: this});

          nodes[node.id()] = node;
          return node;
        }
      }
    };

    koMapping.fromJS(data, mapping, this);

    var nestedOptions = {
      expandBtnHTML: '',
      collapseBtnHTML: '',
      maxDepth: 20
    };

    var $dd = $tab.find('.dd');

    $dd.nestable(nestedOptions);

    this.remove = function(node, e) {
      if (node.parent) {
        var parent = nodes[node.parent.id()];

        parent.children.mappedRemove(function(id) {
          return id === node.id();
        });
      }
    };

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