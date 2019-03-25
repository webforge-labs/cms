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


  it('throws an error when saving fails due to invalid login information', function (done) {
    /*
accepted: false
badRequest: false
body: null
charset: "UTF-8"
clientError: false
error: false
forbidden: false
header:
cache-control: "max-age=0, must-revalidate, private"
content-type: "text/html; charset=UTF-8"
date: "Mon, 25 Mar 2019 15:32:01 GMT"
server: "nginx/1.13.12"
transfer-encoding: "chunked"
x-debug-token: "f96587"
x-powered-by: "PHP/7.2.14"
__proto__: Object
headers:
cache-control: "max-age=0, must-revalidate, private"
content-type: "text/html; charset=UTF-8"
date: "Mon, 25 Mar 2019 15:32:01 GMT"
server: "nginx/1.13.12"
transfer-encoding: "chunked"
x-debug-token: "f96587"
x-powered-by: "PHP/7.2.14"
__proto__: Object
info: false
noContent: false
notAcceptable: false
notFound: false
ok: true
req: Request {_query: Array(0), method: "POST", url: "/cms/posts", header: {…}, _header: {…}, …}
serverError: false
status: 200
statusCode: 200
statusText: "OK"
statusType: 2
text: "<!DOCTYPE html>↵<html lang="en">↵  <head>↵    <meta charset="utf-8">↵    <meta http-equiv="X-UA-Compatible" content="IE=edge">↵    <meta name="viewport" content="width=device-width, initial-scale=1">↵    <meta name="description" content="">↵    <meta name="author" content="Philipp Scheit (ps-webforge.com)">↵↵    <title>Ich will ein Pony: Mini</title>↵↵    <link href="/assets/css/webforge-cms.css?v=dev" rel="stylesheet">↵↵    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->↵    <!--[if lt IE 9]>↵      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>↵      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>↵    <![endif]-->↵↵    <script src="/assets/js/load-require.js?v=dev"></script>↵↵    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png?v=rng2">↵<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=rng2">↵<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=rng2">↵<link rel="icon" type="image/png" size="192x192" href="/android-chrome-192x192.png">↵<link rel="icon" type="image/png" size="512x512" href="/android-chrome-512x512.png">↵<link rel="manifest" href="/site.webmanifest?v=rng2">↵<link rel="mask-icon" href="/safari-pinned-tab.svg?v=rng2" color="#f15a25">↵<link rel="shortcut icon" href="/favicon.ico?v=rng2">↵<meta name="apple-mobile-web-app-title" content="Ich will ein Pony">↵<meta name="application-name" content="Ich will ein Pony">↵<meta name="msapplication-TileColor" content="#da532c">↵<meta name="theme-color" content="#f15a25">  </head>↵<body>↵↵  <div class="container">↵    <div class="row">↵      <div class="col-xs-10 col-sm-8 col-md-8 col-lg-6">↵        ↵            ↵<h1>Minis geschützter Bereich</h1>↵↵<form action="/login_check" class="form-signin" method="post" role="login-form">↵            <input type="hidden" name="_csrf_token" value="h6Qq5rj6EmuiPabAhMJhmFvI_vgX5xhnmy7gUKMhdgg" />↵        ↵    <div class="form-group">↵      <input type="text" role="user" name="_username"  value="" class="form-control" placeholder="Benutzername oder E-Mail-Adresse" required="required" autofocus="autofocus">↵    </div>↵    <div class="form-group">↵      <input type="password" role="password" name="_password" class="form-control" placeholder="Passwort" required="required">↵    </div>↵    <div class="form-group checkbox">↵      <label class="checkbox">↵        <input type="checkbox" name="_remember_me" value="on"> Angemeldet bleiben↵      </label>↵    </div>↵    <p><button class="btn btn-lg btn-primary btn-block" name="_submit" type="submit">Anmelden &raquo;</button></p>↵    <p>↵      <a href="/resetting/request">Passwort vergessen?</a>↵    </p>↵</form>↵      </div>↵    </div>↵  </div>↵↵  <script type="text/javascript">↵      require(['cms/login']);↵  </script>↵↵  </body>↵</html>"
type: "text/html"
unauthorized: false
wasChained: true
*/
    var form = this.form;
    var login = '<form role="loginform"><input type="text" name="_username"/><input type="password" name="_password"/></form>';

    expect(form.isProcessing()).to.be.false;

    dispatcher.expect('POST', '/saving-point').to.respond(200, login, { type: 'html' })

    form.save('POST', '/saving-point', { custom: 'data' })
      .catch(function() {
        // thats okay;
      })
        .then(function() {
          expect(form.isProcessing(), 'processing after saving').to.be.false;
          expect(form.error(), 'error').to.be.ok;

          expect(form.error()).to.contain('Bitte logge dich erneut ein');
          done();
        });
  });

});