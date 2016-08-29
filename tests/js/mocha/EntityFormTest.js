var bootstrap = require('./bootstrap');
var boot = bootstrap({ context: __filename });

var expect = boot.expect;
var _ = require('lodash');

// we replace amplify with a fake
boot.define('amplify', function() {
  return {
    publish: function() {
    }
  }
});


// we fake jQuery
boot.define('jquery', function() {
  return require('jquery-deferred');
});

boot.define('bootstrap-notify', ['jquery'], function($) {
  $.notifications = [];
  $.notify = function(message, type) {
    $.notifications.push({  message: message, type: type });
  };
});

// we fake bootstrap/alert
boot.define('bootstrap/alert', function() {
  return {};
});

boot.define('cms/modules/main', function() {
  return {
    tabs: {
      open: function() {},
      select: function() {},
      closeById: function() {},
    }
  }
});


boot.define('cms/test/post-model', ['knockout', 'knockout-mapping'], function(ko, koMapping) {

  function Post(data) {
    var that = this;
    var mapping = {};

    that.id = ko.observable();

    koMapping.fromJS(data, mapping, this);

    this.editTab = ko.computed(function() {
      if (that.id() > 0) {
        return {
          label: 'Post: '+that.name(),
          id: 'posts-edit-'+that.id(),
          url: '/cms/posts/'+that.id()
        };
      }
    });

    this.serialize = function() {
      return koMapping.toJS(that);
    };
  };

  Post.createTab = ko.computed(function() {
    return {
      label: 'Neuen Post hinzufügen',
      id: 'post-create',
      url: '/cms/posts'
    };
  });

  Post.map = function(data) {
    data.wasMapped = true;
    return new Post(data);
  };

  return Post;
});

var dispatcher = boot.injectFakeDispatcher();
var EntityFormMixin = boot.requirejs('cms/entity-form-mixin');
var Post = boot.requirejs('cms/test/post-model');

describe('EntityFormMixin', function() {

  before(function() { // execute once
    this.newForm = {};

    var newData = {
      isNew: true,
      entity: {
        name: 'the name of the second post',
        content: 'edited content'
      }
    };

    EntityFormMixin(this.newForm, newData, {
      EntityModel: Post,
      create: function() {
        return new Post({
          name: 'the name of the first post',
          content: 'some content',
          wasCreated: true
        });
      }
    });

    this.editForm = {};

    var editData = {
      isNew: false,
      entity: {
        name: 'the name of the second post',
        content: 'edited content'
      }
    };

    EntityFormMixin(this.editForm, editData, {
      EntityModel: Post,
      create: function() {
        return new Post({
          name: 'the name of the second post',
          content: 'edited content'
        });
      }
    });
  });

  describe('when new', function() {

    it('uses the created function of the model-class for the entity', function () {
      expect(this.newForm.entity).to.be.ok;
      expect(this.newForm.entity.id).to.be.ok;
      expect(this.newForm.entity.wasCreated(), 'wasCreated in Post').to.be.true;
    });

    it('manages an isProcessing observable while saving the entity', function(done) {
      var that = this;
      expect(this.newForm.isProcessing).to.be.ok;
      expect(this.newForm.isProcessing()).to.be.false;

      dispatcher.expect('POST', '/cms/posts')
        .to.respond(201, {
          name: 'the name of the backend-response post',
          content: 'backend content'
        })
        .whileProcessing(function() {
          expect(that.newForm.isProcessing()).to.be.true;
        });

      this.newForm.save()
        .then(function() {
          expect(that.newForm.isProcessing()).to.be.false;
          done(); // end the test
        })
    });

    it('shows a success notification if entity is successfully created', function(done) {
      var $ = boot.requirejs('jquery');
      $.notifications = [];

      dispatcher.expect('POST', '/cms/posts')
        .to.respond(201, {
          name: 'the name of the backend-response post',
          content: 'backend content'
        });

      this.newForm.save()
        .then(function() {
          process.nextTick(function() { // in the .then handler a require(['cms/modules/main']) call is made, which is asynchron in turn
            expect($.notifications).to.have.length(1);
            done(); // end the test
          });
        });
    });

    it('displays the big error in the error-observable as html, when 500 occurs', function(done) {
      var form = this.newForm;
      var html = '<html><head><title>This is bad</title>/head><body>Reason why its bad</body></html>';

      dispatcher.expect('POST', '/cms/posts').to.respond(500, html, { format: 'text'});

      form.save()
        .then(function(response) {
          expect(response, 'it should not resolve with a response, because an error occured').to.be.undefined;
          expect(form.error()).to.be.ok.and.to.contain(html);

          done();
        });
    });
  });

  describe('when edited', function() {

    it('calls the static map function of the model-class for the entity in question', function () {
      expect(this.editForm.entity).to.be.ok;
      expect(this.editForm.entity.id).to.be.ok;
      expect(this.editForm.entity.wasMapped(), 'wasMapped in Post').to.be.true;
    });

  });
});