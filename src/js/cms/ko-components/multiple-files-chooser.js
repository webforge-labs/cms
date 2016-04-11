define(['knockout', 'knockout-mapping', 'text!./multiple-files-chooser.html', 'cms/modules/dropbox-chooser', 'cms/modules/dispatcher', 'cms/models/File'], function(ko, koMapping, htmlString, Dropbox, dispatcher, File) {

  var filesChooser = function(params) {
    var that = this;
    params.model[params.name] = that;

    var mapping = {
      files: {
        create: function(options) {
          return new File(options.data);
        }
      }
    };

    that.files = ko.observable([]);
    that.processing = ko.observable(false);

    //koMapping.fromJS({files: []}, mapping, that);

    that.chooseWithDropbox = function() {
      Dropbox.choose({
        success: function(files) {
          that.processing(true);
          console.log(files);

          dispatcher.send('POST', '/cms/media/dropbox', files, 'json', function(request) {
            request.type('form');
          }).done(function(response) {
            koMapping.fromJS(response.body, mapping, that);
            that.processing(false);
          }).fail(function(err, response) {
            that.processing(false);
            amplify.publish('cms.ajax.error', response);
          });

        },
        linkType: "direct",
        multiselect: true,

        // file types, such as "video" or "images" in the list. For more information,
        // see File types below. By default, all extensions are allowed.
        extensions: ['images']
      });
    };
  }

  return { viewModel: filesChooser, template: htmlString };
});