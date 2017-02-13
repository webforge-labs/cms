define(['jquery'], function($) {
  /* globals prompt */

  function UI() {
    var that = this;

    this.prompt = function(question, value) {
      var d = $.Deferred();

      setTimeout(function() {
        var answer = prompt(question, value);

        if (answer == false || answer == null) {
          d.reject(new Error("user canceled"));
        } else {
          d.resolve(answer);
        }
      }, 10);

      return d.promise();
    }
  }

  return new UI();

});