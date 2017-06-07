define(['knockout', 'knockout-mapping', 'cms/modules/moment', 'lodash', 'urlify'], function(ko, koMapping, moment, _, urlify) {

   var titleLimit = 20;

  function Post(data) {
    var that = this;
    var mapping = {};
    koMapping.fromJS(data, mapping, this);

    if (!that.id) {
      that.id = ko.observable();
    }

    this.formattedCreateDate = ko.computed(function() {
      return moment(that.created()).fromNow();
    });

    this.formattedPublishedDate = ko.computed(function() {
      var pub = that.published();

      if (!pub) {
        return 'nicht veröffentlicht';
      } else {
        return moment(pub).fromNow();
      }
    });

    this.categoriesOptionValue = ko.computed({
      read: function() {
        return _.map(ko.unwrap(that.categories), function(category) {
          return ko.unwrap(category.id);
        });
      },
      write: function(categoryId) {
        if (!categoryId) return;

        var category = _.find(ko.unwrap(that.categoriesOptions), function(category) {
          return ko.unwrap(category.id) == categoryId;
        });

        that.categories([category]);
      }
    });

    this.relationsOptionValue = ko.observableArray([11]);

    var findPost = function(postId) {
      return _.find(ko.unwrap(that.allPosts), function(post) {
        return ko.unwrap(post.id) == postId;
      });
    };

    this.relationsOptionValue = ko.computed({
      // returns the index value of the entities connected to the entity
      read: function() {
        return _.map(ko.unwrap(that.relations), function(relation) {
          return ko.unwrap(relation.relatedPost.id);
        });
      },
      // searches the universe with the given index from read() and adds it to the entities connected to the entity
      write: function(postIds) {
        if (!postIds) return;

        var updatedRelations = [];
        // create new relations for that post ids that were not added yet
        _.each(postIds, function(postId) {
          var relation = _.find(that.relations(), function(relation) {
            return ko.unwrap(relation.relatedPost.id) == postId;
          });

          if (!relation) {
            var post = findPost(postId);

            relation = {
              relatedPost: post,
              priority: ko.observable(null),
              id: ko.observable(null)
            };
          }

          updatedRelations.push(relation);
        });

        that.relations(updatedRelations);
      }
    });

    this.generateUploadPath = function() {
      var categories = ko.unwrap(that.categories);
      if (!categories.length) return;

      var category = categories[0];
      var name = urlify(that.title(), 120, false);
      var pub = moment(that.published());
      var date = pub.format('YYYY');

      return '/bilder-uploads/'+date+'/'+ko.unwrap(category.slug)+'/'+name;
    };

    this.areImagesUploadable = ko.computed(function() {
      var title = that.title();
      var categories = ko.unwrap(that.categories);

      return $.trim(title) !== "" && categories && categories.length > 0;
    });

    this.tabLabel = ko.computed(function() {
      return '„'+(that.title().length > titleLimit ? that.title().substring(0, titleLimit)+'…' : that.title())+"“";
    });

    this.tabPreviewLabel = ko.computed(function() {
      return 'Vorschau: '+that.tabLabel();
    });

    this.tabPreviewFullLabel = ko.computed(function() {
      return 'Vorschau: '+that.title();
    });

    this.editTab = ko.computed(function() {
      if (that.id() > 0) {
        return {
          label: that.tabLabel,
          fullLabel: that.title,
          id: 'posts-edit-'+that.id(),
          url: '/cms/posts/'+that.id()
        };
      }
    });

    this.previewTab = ko.computed(function() {
      if (that.id() > 0) {
        return {
          label: that.tabPreviewLabel,
          fullLabel: that.tabPreviewFullLabel,
          id: 'posts-preview-'+that.id(),
          url: '/cms/posts/'+that.id()+'/preview'
        };
      }
    });

    this.serialize = function() {
      return koMapping.toJS(that, {
        'ignore': ["created", "updated"]
      });
    };
  };

  Post.createTab = ko.computed(function() {
    return {
      label: 'Neuen Post erstellen',
      id: 'post-create',
      url: '/cms/posts'
    };
  });

  Post.map = function(data) {
    return new Post(data);
  };

  return Post;
});