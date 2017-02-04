define(['jquery'], function($) {
  /* globals prompt */

  function UI() {
    var that = this;

    this.prompt = function(question, value) {
      var d = $.Deferred();

      var answer = prompt(question, value);

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