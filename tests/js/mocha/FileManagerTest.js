var boot = require('./bootstrap');
var expect = boot.expect;
var _ = require('lodash');

var clone = function(source) {
  return JSON.parse(JSON.stringify(source));
};

GLOBAL.window = {
  location: {
    search: ''
  }
};

// we replace amplify with a fake
boot.requirejs.define('amplify', function() {
  return {
    publish: function() {
    },
    subscribe: function() {
    }
  }
});

// we fake jQuery
boot.requirejs.define('jquery', function() {
  $ = require('jquery-deferred');

  $.notifications = [];
  $.notify = function(message, type) {
    $.notifications.push({  message: message, type: type });
  };

  return $;
});

// we fake the dispatcher
boot.requirejs.define('cms/modules/dispatcher', function() {
  var FakeDispatcher = function FakeDispatcher() {
    var that = this;

    that.nextPromises = [];

    this.sendPromised = function() {
      return that.nextPromises.shift();
    };
  };

  return new FakeDispatcher();
});

boot.requirejs.define('cms/modules/dropbox-chooser', function() {
  return {
    choose: function(options) {
      var files = [
          {
            "isDir": false,
            "link": "some-host-on-dropbox.com/assets/img/mini-single.png",
            "name": "DSC03281.JPG",
            "thumbnailLink": "https://api-content.dropbox.com/r11/t/DSC03281.JPG?bounding_box=75&mode=fit",
            "is_dir": false,
            "bytes": 2089395,
            "icon": "https://www.dropbox.com/static/images/icons64/page_white_picture.png"
          }
      ];

      options.success(files);
    }
  };
});

var FileManager = boot.requirejs('cms/FileManager/ns');
var dispatcher = boot.requirejs('cms/modules/dispatcher');
var Promise = boot.requirejs('bluebird');

describe('FileManager', function() {

  before(function() { // execute once
    var that = this;

    this.rootResponse = {
      root: {
        name: 'home',
        type: 'ROOT',
        items: [
          {
            name: '2016-03-27',
            type: 'directory',
            items: [
              {
                'name': 'Foto 27.03.16, 16 14 18.jpg'
              },
              {
                'name': 'Foto 27.03.16, 16 14 21 (1).jpg'
              },
              {
                'name': 'Foto 27.03.16, 16 14 21.jpg'
              },
              {
                'name': 'Foto 27.03.16, 16 14 54.jpg'
              }
            ]
          },
          {
            'name': 'Neuer Ordner',
            'type': 'directory',
            'items': []
          }
        ]
      }
    };

    this.fm = new FileManager.Manager(this.rootResponse);

    this.changeFolder = function(name) {
      var folder = this.fm.sortedItems().find(function(item) {
        return item.name() === name;
      });

      expect(folder, 'folder with name "'+name+'"').to.be.ok;

      // set as current item
      this.fm.clickItem(folder);
      return folder;
    }.bind(this);
  });

  beforeEach(function() {
    this.fm.setCurrentItem(this.fm.root);
    dispatcher.nextPromises = [];
  })

  it('uploads files from the dropbox, are then displayed', function (done) {
    var fm = this.fm, that = this;

    var uploadedRootResponse = clone(this.rootResponse);
    uploadedRootResponse.root.items[1].items.push({
      'name': 'uploaded.png'
    });

    dispatcher.nextPromises.push(new Promise(function(fulfill, reject) {
      fulfill({body: uploadedRootResponse}); // this is indeed not used, yet. Its just the response from the POST call (which returns the index as well)
    }));

    dispatcher.nextPromises.push(new Promise(function(fulfill, reject) {
      fulfill({body: uploadedRootResponse});
    }));

    fm.addFilesFromDropbox();

    setTimeout(function() {
      that.changeFolder('Neuer Ordner');

      var items = [];
      fm.sortedItems().forEach(function(item) {
        items.push({ name: item.name() });
      });

      expect(items, 'items in uploaded folder').to.have.length(1);
      expect(items[0], 'uploaded file').to.have.property('name', 'uploaded.png');

      done();
    }, 2);

  });

  it('uploads files from the dropbox, but warns when warnings are in the upload response', function (done) {
    var fm = this.fm, that = this;

    expect(fm.error()).to.be.not.ok;

    var uploadedRootResponse = clone(this.rootResponse);
    uploadedRootResponse.root.items[1].items.push({
      'name': 'uploaded.png'
    });

    var msg, uploadWithWarnings = clone(uploadedRootResponse);
    uploadWithWarnings.warnings = [msg = 'Die Datei wurde nicht überschrieben. Zum überschreiben, erst die Datei löschen']

    dispatcher.nextPromises.push(new Promise(function(fulfill, reject) {
      fulfill({body: uploadWithWarnings});
    }));

    dispatcher.nextPromises.push(new Promise(function(fulfill, reject) {
      fulfill({body: uploadedRootResponse});
    }));

    fm.addFilesFromDropbox();

    setTimeout(function() {
      that.changeFolder('Neuer Ordner');

      expect(fm.error(), 'fm.error').to.be.ok
        .and.to.have.property('message').to.contain(msg);

      expect(fm.sortedItems(), 'items in uploaded folder').to.have.length(1);

      done();
    }, 2);

  });
});