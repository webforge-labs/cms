define(['knockout', 'knockout-mapping', 'lodash', '../PostModel', 'cms/list-mixin'], function(ko, koMapping, _, Post, ListMixin) {

  return function(data, main, $context) {

    var Model = function() {
      var that = this;

      ListMixin(that, {'restName': 'posts', EntityModel: Post});

      var mapping = {
        'posts': {
          create: function(options) {
            return Post.map(options.data);
          }
        }
      };

      koMapping.fromJS(data, mapping, that);

      that.sortedPosts = ko.computed(function() {
        return _.orderBy(that.posts(), function(post) {
          return post.published();
        }, ['desc']);
      });
    };

    var model = new Model();

    main.createContext('postsList', model, $context);
  };
});