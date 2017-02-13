define(['require', 'knockout', 'jquery', 'knockout-collection', 'knockout-dragdrop', 'text!./multiple-files-chooser.html', 'cms/modules/dispatcher', 'cms/ko-bindings/uk-sortable'], function(require, ko, $, KnockoutCollection, koDragdrop, htmlString, dispatcher) {

  var filesChooser = function(params) {
    var that = this;

    var filesObservableArray = params.model[params.name];

    that.files = new KnockoutCollection(filesObservableArray, {key: 'key', reference: true});
    that.processing = ko.observable(false);
    that.isLoading = ko.observable(false);

    that.chooseFiles = function() {
      require(['cms/modules/main'], function(main) {
        main.openFileManager({
          success: function(files) {
            ko.utils.arrayForEach(files, function(file) {
              that.files.add(file);
            });
          },
          spinner: that.isLoading,
          choosingMode: true
        });
      });
    };

    that.addedFiles = ko.computed(function() {
      return ko.utils.arrayFilter(that.files.toArray(), function(file) {
        return ko.unwrap(file.isExisting);
      });
    });

    that.removeFromAddedFiles = function(file) {
      that.files.remove(file);
    };

    that.uploadPath = ko.computed(function() {
      return params.generateUploadPath.call(null);
    });

    that.reorder = function(item, newIndex, type) {
      //var oldIndex = filesObservableArray.indexOf(item);
      //console.log('moved from ', oldIndex, 'to ', newIndex);
      filesObservableArray.remove(item);
      filesObservableArray.splice(newIndex, 0, item);
    };

    // this could be solved much nicer, if bar and upload would be knockout-components itself (TODO)
    $context = $(params.uploadAreaSelector);

    require(['uikit', 'uikit-src/components/upload'], function(UIkit) {
      var bar, warnings, files;

      UIkit.upload($context, {
        url: '/cms/media/upload',
        multiple: true,
        concurrent: 2,
        dataType: 'json',
        clsDragover: 'upload-area-hover',

        beforeAll: function(Upload, uploadFiles) { 
          bar = $context.find('.uk-progress')[0];
          warnings = [];
          files = [];

          Upload.params['path'] = that.uploadPath();

          bar.max = 0;
          ko.utils.arrayForEach(uploadFiles, function(file) {
            bar.max += file.size;
          });
          bar.removeAttribute('hidden');
          bar.value = 0;
        },

        progress: function (e) {
          bar.value += e.loaded;
        },

        complete: function(xhr) {
          var body = xhr.responseJSON;

          ko.utils.arrayForEach(body.warnings, function(warning) {
            warnings.push(warning);
          });

          ko.utils.arrayForEach(body.files, function(file) {
            files.push(file);
          });
        },

        completeAll: function(onlyLastXhr) {
          if (warnings.length) {
            $.notify({
              message: 
                files.length == 1 ? 
                  "Ich habe die Datei hochgeladen, aber:\n"+warnings.join("<br>\n") :
                  "Ich habe die "+files.length+" Dateien hochgeladen, aber:\n"+warnings.join("<br>\n")
            },{
              type: 'warning'
            });
          } else {
            $.notify({
              message: 
                files.length == 1 ? 
                  "Okay, ich habe 1 Datei hochgeladen." :
                  "Okay, ich habe "+files.length+" Dateien hochgeladen."
            },{
              type: 'success'
            });
          }

          ko.utils.arrayForEach(files, function(exportedFile) {
            that.files.add(exportedFile);
          });

          setTimeout(function () {
            bar.setAttribute('hidden', 'hidden');
          }, 1000);
        }
      });
    });
  };

  return { viewModel: filesChooser, template: htmlString };
});