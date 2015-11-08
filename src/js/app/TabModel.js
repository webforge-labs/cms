define(['knockout', 'knockout-mapping', 'jquery'], function(ko, koMapping, $) {
  
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
    };

    this.contentLoadingError = function(response) {
      that.contents('<div class="alert alert-danger" role="alert"><strong>Oh mist!</strong> Der Tab konnte nicht geladen werden.</div>');
      that.hasError(true);
      that.wasLoaded(true);
    };

    this.deactivate = function() {
      that.isActive(false);
    };

    this.activate = function() {
      that.isActive(true);
    };
  };
});