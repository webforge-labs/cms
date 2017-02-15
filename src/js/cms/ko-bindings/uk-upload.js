define(['require', 'knockout', 'jquery', 'uikit-src/uikit-core'], function(require, ko, $, UIkit) {

  var UploadComponent = function(UIkit, element, options) {
    var bar, warnings, files;

    $context = $(element);

    var defaultOptions = {
      clsDragover: 'upload-area-hover',
      multiple: true,
      concurrent: 2,
      dataType: 'json'
    }

    var uploadOptions = $.extend({}, defaultOptions, options, {

      beforeAll: function(Upload, uploadFiles) { 
        bar = $(element).find('.uk-progress')[0];
        warnings = [];
        files = [];

        Upload.params['path'] = ko.isObservable(options.path) ? ko.unwrap(options.path) : options.path.call(Upload);

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

        if (options.completed) {
          options.completed.call(undefined, files, onlyLastXhr);
        }

        setTimeout(function () {
          bar.setAttribute('hidden', 'hidden');
        }, 1000);
      }
    });

    UIkit.upload($context.get(0), uploadOptions);
  };

  ko.bindingHandlers.ukUpload = {
    init: function(element, valueAccessor, allBindingsAccessor, data, context) {
      var options = ko.unwrap(valueAccessor());

      require(['uikit', 'uikit-src/components/upload'], function(UIkit) {
        new UploadComponent(UIkit, element, options);
      });
    }/*,
    update: function(element, valueAccessor, allBindingsAccessor, data, context) {
    }
    */
  };
});