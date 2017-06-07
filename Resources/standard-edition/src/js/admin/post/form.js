define([
    'jquery',
    '../PostModel',
    'cms/entity-form-mixin',
    'cms/modules/moment',
  ],
  function($, Post, EntityFormMixin, moment) {

  return function(data) {
    var that = {};

    EntityFormMixin(that, data, { 
      EntityModel: Post,
      create: function() {
        return new Post({
          title: '',
          teaserMarkdown: '',
          images: [],
          created: moment().toISOString(),
          published: moment().toISOString(),
          categories: [],
          contents: {
            blocks: []
          }
        });
      }
    });

    that.areImagesUploadable = that.entity.areImagesUploadable;

    that.afterOptionsHTML = function(option, item) {
      var $option = $(option);
      $option.attr('data-icon', 'fa-'+$option.attr('value'));
    };

    Post.prototype.categoriesOptions = data.categories;
    Post.prototype.allPosts = Post.prototype.relationsOptions = data.allPosts;

    // nextTick
    window.setTimeout(function() {
      $(window).trigger('load.bs.select.data-api');
    }, 20);

    return that;
  };
});