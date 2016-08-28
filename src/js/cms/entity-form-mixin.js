define(['require', 'knockout', 'knockout-mapping', 'jquery', './form-mixin', 'cms/modules/dispatcher', 'cms/TabModel', 'bootstrap-notify', 'bootstrap/alert', 'cms/ko-components/index'], function(require, ko, koMapping, $, FormMixin, dispatcher, Tab, notify, bsAlert, componentsIndex) {

  return function EntityFormMixin(formModel, data, options) {
    var that = formModel;
    var EntityModel = options.EntityModel;

    that.isNew = ko.observable(data.isNew);

    FormMixin(that, { dispatcher: dispatcher});

    if (that.isNew()) {
      that.entity = options.create();
    } else {
      that.entity = options.EntityModel.map(data.entity);
    }

    var _parent = {
      save: that.save
    };

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

      return _parent.save(method, url, body)
        .then(function(response) {
          if (that.isNew()) {
            require(['cms/modules/main'], function(cmsMain) {
              var entity = EntityModel.map(response.body);
              var tab = new Tab(entity.editTab());

              cmsMain.tabs.open.call(cmsMain, tab, e);
              // make new edit tab active
              cmsMain.tabs.select.call(cmsMain, tab, e);
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
        }, function(err) {
          // the error is displayed in formMixin, we won't have to do something else
        })
    };
  };
});