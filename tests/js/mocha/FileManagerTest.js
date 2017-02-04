var bootstrap = require('./bootstrap');
var boot = bootstrap({ context: __filename });

var expect = boot.expect;

var clone = function(source) {
  return JSON.parse(JSON.stringify(source));
};

// we replace amplify with a fake
boot.define('amplify', function() {
  return {
    publish: function() {
    },
    subscribe: function() {
    }
  }
});

// we fake jQuery
boot.define('jquery', function() {
  $ = require('jquery-deferred');

  $.notifications = [];
  $.notify = function(message, type) {
    $.notifications.push({  message: message, type: type });
  };

  return $;
});
boot.define('bootstrap-notify', ['jquery'], function($) {
  return $;
});

boot.define('cms/modules/dropbox-chooser', function() {
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

var ui = {
  prompt: function() {
  }
}
boot.define('cms/modules/ui', function() {
  return ui;
});


var dispatcher = boot.injectFakeDispatcher();
var FileManager = boot.requirejs('cms/FileManager/ns');
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
                'name': 'Foto 27.03.16, 16 14 18.jpg',
                'key': '0b65b7a3-3b4f-4b41-8a06-db148bb77b24'
              },
              {
                'name': 'Foto 27.03.16, 16 14 21 (1).jpg',
                'key': '0d9ae567-f598-43ba-a6ad-ff9f5944d02f'
              },
              {
                'name': 'Foto 27.03.16, 16 14 21.jpg',
                'key': '0d21f563-c09b-4abc-9f4d-6cf5b5660c99'
              },
              {
                'name': 'Foto 27.03.16, 16 14 54.jpg',
                'key': '1e3a3e68-130a-4f14-85d8-6bfba5ffed90'
              }
            ]
          },
          {
            'name': 'neuer-ordner',
            'type': 'directory',
            'items': []
          }
        ]
      }
    };

    this.fm = new FileManager.Manager(this.rootResponse);

    this.findItem = function(name) {
      return this.fm.sortedItems().find(function(item) {
        return item.name() === name;
      });
    };

    this.selectItem = function(name) {
      var item = that.findItem(name);
      expect(item, 'finding '+name).to.be.ok;

      that.fm.selection.push(item);
      return item;
    };

    this.promptAnswer = function(value) {
      ui.prompt = function() {
        var d = require('jquery-deferred').Deferred();

        process.nextTick(function() {
          d.resolve(value);
        });

        return d.promise();
      };
    }

    this.changeFolder = function(name) {
      var folder = this.findItem(name);
      expect(folder, 'folder with name "'+name+'"').to.be.ok;

      // set as current item
      this.fm.clickItem(folder);
      return folder;
    }.bind(this);
  });

  beforeEach(function() {
    this.fm.setCurrentItem(this.fm.root);
    dispatcher.reset();
  });

  it('creates a new folder and normalizes its name to an urlsafe one', function(done) {
    var that = this;

    this.promptAnswer('Neuer Ordner');

    this.fm.newFolder().then(function() {
      var item = that.fm.currentItem();
      expect(item).to.be.ok;
      expect(item.label()).to.be.equal('neuer-ordner');
      done();
    });
  });

  it('uploads files from dropbox, which are then displayed', function (done) {
    var fm = this.fm, that = this;

    var uploadedRootResponse = clone(this.rootResponse);
    uploadedRootResponse.root.items[1].items.push({
      'name': 'uploaded.png'
    });

    // this is indeed not used, yet. Its just the response from the POST call (which returns the index as well)
    dispatcher.expect('POST').to.respond(201, uploadedRootResponse);
    dispatcher.expect('GET').to.respond(200, uploadedRootResponse);
    
    fm.addFilesFromDropbox();

    setTimeout(function() {
      that.changeFolder('neuer-ordner');

      var items = [];
      fm.sortedItems().forEach(function(item) {
        items.push({ name: item.name() });
      });

      expect(items, 'items in uploaded folder').to.have.length(1);
      expect(items[0], 'uploaded file').to.have.property('name', 'uploaded.png');

      done();
    }, 60);

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

    dispatcher.expect('POST').to.respond(201, uploadWithWarnings);
    dispatcher.expect('GET').to.respond(200, uploadedRootResponse);

    fm.addFilesFromDropbox();

    setTimeout(function() {
      that.changeFolder('neuer-ordner');

      expect(fm.error(), 'fm.error').to.be.ok
        .and.to.have.property('message').to.contain(msg);

      expect(fm.sortedItems(), 'items in uploaded folder').to.have.length(1);

      done();
    }, 60);

  });

  it('marks an item not selected/selected, when added to the selection/the selection is resetted', function() {
    var fm = this.fm;

    this.changeFolder('2016-03-27');
    var item = this.findItem('Foto 27.03.16, 16 14 18.jpg');
    var item2 = this.findItem('Foto 27.03.16, 16 14 21.jpg');

    expect(item.selected(), 'item.selected').to.be.false;
    expect(item2.selected(), 'item.selected').to.be.false;

    fm.selection.push(item);
    fm.selection.push(item2);

    expect(item.selected(), 'item.selected').to.be.true;
    expect(item2.selected(), 'item.selected').to.be.true;

    fm.reset({});

    expect(item.selected(), 'item.selected after reset').to.be.false;
    expect(item2.selected(), 'item.selected after reset').to.be.false;
  });

  it('marks all items if selectAll is used', function() {
    var fm = this.fm;

    this.changeFolder('2016-03-27');
    fm.selectAll();

    expect(fm.selection()).to.have.length(4);
  });

  it('renames a folder', function(done) {
    var that = this;
    var fm = this.fm;
    var item = this.selectItem('neuer-ordner');

    this.promptAnswer('renamed');

    /*
    var renamedRootResponse = clone(this.rootResponse);
    renamedRootResponse.root.items[1].name = 'renamed';
    */

    dispatcher.expect('POST').to.respond(200, {});

    fm.renameItem().then(function(response) {
      expect(that.findItem('renamed')).to.be.ok;

      expect(dispatcher.getExpectations()).to.have.length(0);
      done();
    }).catch(function(exc) {
      done(exc);
    });
  });

  it('renames not, if server error occurs', function(done) {
    var that = this;
    var fm = this.fm;
    var item = this.selectItem('renamed');

    this.promptAnswer('renamed');

    dispatcher.expect('POST').to.respond(500, { msg: 'this is the error' });

    fm.renameItem().then(function(response) {
      expect(false, 'this should not be called').to.be.true;
    }).catch(function(error) {
      expect(error).to.be.ok;
      expect(error.response.ok).to.be.false;
      done();
    });
  });

  it('renames a file with extension', function(done) {
    var that = this;
    var fm = this.fm;
    this.changeFolder('2016-03-27');
    var item = this.selectItem('Foto 27.03.16, 16 14 54.jpg');

    this.promptAnswer('foto-27-03-16.jpg');

    dispatcher.expect('POST').to.respond(200, {});

    fm.renameItem().then(function(response) {
      expect(that.findItem('foto-27-03-16.jpg')).to.be.ok;

      expect(dispatcher.getExpectations()).to.have.length(0);
      done();
    }).catch(function(exc) {
      done(exc);
    });
  });

  it('can delete a non synced folder', function(done) {
    var that = this;

    this.promptAnswer('new');

    this.fm.newFolder().then(function() {
      that.fm.setCurrentItem(that.fm.root);
      var item = that.selectItem('new');
      that.fm.removeItem(item);

      expect(that.findItem('new')).to.be.undefined;
      done();
    }).catch(done);
  });

});