define(['knockout', 'knockout-mapping', 'cms/modules/dispatcher', 'cms/TabModel', 'bootstrap-notify', 'bootstrap/alert'], function(ko, koMapping, dispatcher, Tab, notify, bsAlert) {

  return function EntityFormMixing(formModel, data, options) {
    var that = formModel;
    var EntityModel = options.EntityModel;

    that.isNew = ko.observable(data.isNew);

    if (that.isNew()) {
      that.entity = options.create();
    } else {
      that.entity = options.EntityModel.map(data.entity);
    }

    that.error = ko.observable();

    that.save = function(model, e) {
      var body = that.entity.serialize();

      var method, url;

      if (that.isNew()) {
        method = 'POST';
        url = EntityModel.createTab().url;
      } else {
        method = 'PUT';
        url = that.entity.editTab().url;
      }

      that.handle(
        dispatcher.send(method, url, body),
        e
      );
    };

    that.handle = function(req, e) {
      that.error(undefined);

      req
        .done(function(response) {

          if (that.isNew()) {
            require(['cms/modules/main'], function(cmsMain) {
              var entity = EntityModel.map(response.body);
              var tab = new Tab(entity.editTab());

              cmsMain.tabs.open.call(cmsMain, tab, e);
            });
            $.notify({
              message: "Alles klar, das hab ich neu erstellt."
            },{
              type: 'success'
            });
          } else {
            $.notify({
              message: "Alles klar, das hab ich mir gemerkt."
            },{
              type: 'success'
            });
          }
        })
        .fail(function(err, res) {
          var text = '';

          if (res.body && res.body.validation && res.body.validation.errors) {
            $.each(res.body.validation.errors, function(i, error) {
              text += "<p>";
              if (error.field && error.field.path) {
                text += "<strong>"+error.field.path+"</strong>: ";
              }
              text += error.message+"</p>";
            });
          } else {
            text = res.text;
          }

          that.error(text);
        });
    };

  };

});
