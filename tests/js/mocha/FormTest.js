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
  return {
    notify: function() {

    }
  };
});

boot.define('bootstrap-notify', function() {
  return {};
});

// we fake bootstrap/alert
boot.define('bootstrap/alert', function() {
  return {};
});

var dispatcher = boot.injectFakeDispatcher();
var FormMixin = boot.requirejs('cms/form-mixin');

describe('FormMixin', function() {

  before(function() { // execute once
    this.form = {};

    FormMixin(
      this.form,
      {
        dispatcher: dispatcher
      }
    );
  });

  beforeEach(function() {
    dispatcher.reset();
  });

  it('exposes an error observable', function () {
    expect(this.form.error).to.be.an.observable;
  });

  it('exposes an isProcessing observable', function () {
    expect(this.form.isProcessing).to.be.an.observable;
  });

  it('sets isProcessing and resets error on success, when it is currently saving', function (done) {
    var form = this.form, response200 = { body: null };
    expect(form.isProcessing()).to.be.false;

    var intermediateProcessing;
    dispatcher.expect('POST', '/saving-point')
      .respond(200, { id: 7, content: 'createed post'})
      .whileProcessing(function() {
        intermediateProcessing = form.isProcessing();
      });

    form.error('a value that should be reset');

    form.save('POST', '/saving-point', { custom: 'data' })
      .then(function() {
        expect(intermediateProcessing, 'processing while saving').to.be.true;
        expect(form.isProcessing(), 'processing after saving').to.be.false;
        expect(form.error(), 'error after sucessful saving').to.be.undefined;
        done();
      }, function(err) {
        expect(err, 'should not be rejected').to.be.undefined;
        done();
      });
  });

  it('sets an error when saving fails with validation', function (done) {
    var form = this.form;
    var body400 = { 
      "validation": {
        "errors":[
          {
            "message":"Dieser Wert sollte nicht leer sein.",
            "field": {"path":"title","name":"data"},
            "params": {"{{ value }}":"null"}
          }
        ]
      }
    };

    expect(form.isProcessing()).to.be.false;

    dispatcher.expect('POST', '/saving-point').to.respond(400, body400)

    form.save('POST', '/saving-point', { custom: 'data' })
      .catch(function() {
        // thats okay;
      })
        .then(function() {
          expect(form.isProcessing(), 'processing after saving').to.be.false;
          expect(form.error(), 'error').to.be.ok;

          expect(form.error()).to.contain('<strong>title</strong>').and.to.contain('Dieser Wert sollte nicht leer sein');
          done();
        });
  });

  it('does not resolve the returned promise, when an error occurs', function (done) {
    var form = this.form;
    var html = '<html><head><title>This is bad</title>/head><body>Reason why its bad</body></html>';

    dispatcher.expect('POST', '/saving-point').to.respond(500, html, { format: 'html' });

    var thenCalled = false, rejectCalled = false;
    form.save('POST', '/saving-point', { custom: 'data' })
      .then(function() {
        thenCalled = true;
      }, function(err) {
        rejectCalled = true;
      })
        .then(function(errOrResponse) {
          expect(thenCalled,'promise from save() should never be resolved, when errors occur').to.be.false;
          expect(rejectCalled,'promise from save() should  be rejected, when errors occur, ALLTHOUGH they are process in error()').to.be.true;
          expect(form.error(), 'form.error()').to.contain('<title>This is bad</title>');
          done();
        })
  });

  it('sets error with response without html but text', function (done) {
    var form = this.form;

    var text = '<html><head><title>This is bad</title>/head><body>Reason why its bad</body></html>';

    dispatcher.expect('POST', '/saving-point').to.respond(500, text, { format: 'text' });

    var rejectCalled = false;
    form.save('POST', '/saving-point', { custom: 'data' })
      .catch(function(err) {
        rejectCalled = true;
      })
        .then(function() {
          expect(rejectCalled,'promise from save() should  be rejected, when errors occur, ALLTHOUGH they are process in error()').to.be.true;
          expect(form.error(), 'form.error observable').to.be.ok;
          expect(form.error(), 'form.error()').to.contain('<title>This is bad</title>');
          done();
        });
  });  

});