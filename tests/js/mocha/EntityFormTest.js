var bootstrap = require('./bootstrap');
var boot = bootstrap({ context: __filename });

var expect = boot.expect;
var _ = require('lodash');

GLOBAL.window = {
  location: {
    search: ''
  }
};

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

// we fake the dispatcher
boot.define('cms/modules/dispatcher', function() {
  var FakeDispatcher= function () {
    var that = this;

    that.promiseBodies = [];

    that.onSend = function(promiseBody) {
      that.promiseBodies.push(promiseBody);
    };

    that.sendPromised = function(method, url, body) {
      return new Promise(that.promiseBodies.shift());
    };

    that.reset = function() {
      that.promiseBodies = []
    };

  };

  return new FakeDispatcher();
});

boot.define('cms/modules/main', function() {
  return {
    tabs: {
      open: function() {},
      select: function() {},
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


var EntityFormMixin = boot.requirejs('cms/entity-form-mixin');
var Post = boot.requirejs('cms/test/post-model');
var Promise = boot.requirejs('bluebird');
var dispatcher = boot.requirejs('cms/modules/dispatcher');

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

    this.expect201Response = function(whileProcessing) {
      dispatcher.onSend(function(fulfill, reject) {

        setTimeout(function() {
          if (whileProcessing) {
            whileProcessing.call();
          }

          var response201 = {
            body: {
              name: 'the name of the backend-response post',
              content: 'backend content'
            }
          };

          fulfill(response201);
        }, 40);

      });
    };
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

      this.expect201Response(function() {
        expect(that.newForm.isProcessing()).to.be.true;
      });

      setTimeout(function() {
        expect(that.newForm.isProcessing()).to.be.false;
        done(); // end the test
      }, 45);

      this.newForm.save();
    });

    it('shows a success notification if entity is successfully created', function(done) {
      var $ = boot.requirejs('jquery');
      $.notifications = [];

      setTimeout(function() {
        expect($.notifications).to.have.length(1);

        done(); // end the test
      }, 45);

      this.expect201Response();
      this.newForm.save();
    });

    it('displays the big error in the error-observable as html, when 500 occurs', function(done) {
      var form = this.newForm;
      var html = '<html><head><title>This is bad</title>/head><body>Reason why its bad</body></html>';
      var rejectError = new Error();

      rejectError.status = 500;
      rejectError.html = html;

      rejectError.response = {
        ok: false,
        status: 500,
        body: html,
        html: html
      };

      dispatcher.onSend(function(fulfill, reject) {

        setTimeout(function() {
          reject(rejectError);
        }, 10);

      });

      setTimeout(function() {
        expect(form.error()).to.be.ok
          .and.to.contain(html);
        done();
      }, 30);

      form.save();
    })
  });

  describe('when edited', function() {

    it('calls the static map function of the model-class for the entity in question', function () {
      expect(this.editForm.entity).to.be.ok;
      expect(this.editForm.entity.id).to.be.ok;
      expect(this.editForm.entity.wasMapped(), 'wasMapped in Post').to.be.true;
    });

  });
});