define(['knockout', 'cms/modules/dispatcher', 'bootstrap-notify'], function(ko, dispatcher, notify) {

  return function(listModel, listOptions) {
    var that = listModel;

    that.selection =  ko.observableArray();

    that.reloadTab = function() {
      require(['cms/modules/main'], function(cmsMain) {
        cmsMain.tabs.reload();
      });
    };

    this.createTab = listOptions.EntityModel.createTab;

    that.removeInSelection = function() {
      var selection = that.selection();

      var ids = [];
      ko.utils.arrayForEach(selection, function(item) {
        ids.push(item.id());
      });

      dispatcher.send('POST', '/cms/'+listOptions.restName+'/delete', { ids: ids }, 'json')
        .done(function(response) {
          $.notify({
            message: "Okay, das konnte ich löschen."
          },{
            type: 'success'
          });

          ko.utils.arrayForEach(selection, function(entity) {
            if (ko.utils.arrayIndexOf(response.body.removed, entity.id()) !== -1) {
              that[listOptions.restName].remove(entity);
            }
          });

          that.selection([]);
        })
        .fail(function(err, res) {
          $.notify({
            message: "Oops, das löschen hat nicht geklappt."
          },{
            type: 'danger'
          });
        });
    };
  };
});