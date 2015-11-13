define(['knockout', 'knockout-mapping', 'jquery', 'modules/translator', 'amplify'], function(ko, koMapping, $, translator, amplify) {
  
  return function(data) {
    var that = this;

    // required: id, label, url

    koMapping.fromJS(data, {ignore:[]}, this);

    if (!this.isActive) {
      this.isActive = ko.observable(false);
    }

    if (!this.wasLoaded) {
      this.wasLoaded = ko.observable(false);
    }

    this.hasError = ko.observable(false);

    if (!this.contents) {
      this.contents = ko.observable('<i class="fa fa-spinner"></i>');
    }

    this.contentLoaded = function(response) {
      that.contents(response.text);
      that.hasError(false);
      that.wasLoaded(true);
      amplify.publish('cms.tabs.loaded', that, $('#'+that.id()));
    };

    this.contentLoadingError = function(response) {
      that.contents('<div class="alert alert-danger" role="alert"><strong>'+translator.trans('errors.ohcrap', undefined, 'cms')+'</strong> '+translator.trans('errors.tabload', undefined, 'cms')+'</div>');
      that.hasError(true);
      that.wasLoaded(true);
    };

    this.deactivate = function() {
      that.isActive(false);
    };

    this.activate = function() {
      that.isActive(true);
    };

    this.serialize = function() {
      return {
        id: that.id(),
        label: that.label(),
        url: that.url()
      };
    };
  };
});