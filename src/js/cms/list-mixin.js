define(['knockout', 'cms/modules/dispatcher', 'bootstrap-notify'], function(ko, dispatcher, notify) {

  return function(listModel, listOptions) {
    var that = listModel;

    that.selection =  ko.observableArray();
    that.isProcessing = ko.observable(false);

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

      that.isProcessing(true);
      dispatcher.sendPromised('POST', '/cms/'+listOptions.restName+'/delete', { ids: ids }, 'json')
        .then(function(response) {
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
          that.isProcessing(false);
        })
        .catch(function(err) {
          that.isProcessing(false);
          $.notify({
            message: "Oops, das löschen hat nicht geklappt."
          },{
            type: 'danger'
          });
        });
    };
  };
});