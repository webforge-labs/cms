define(['jquery'], function($) {
  /* globals prompt */

  function UI() {
    var that = this;

    this.prompt = function(question) {
      var d = $.Deferred();

      var answer = prompt(question);

      if (answer === false) {
        d.reject();
      } else {
        d.resolve(answer);
      }

      return d.promise();
    }
  }

  return new UI();

});