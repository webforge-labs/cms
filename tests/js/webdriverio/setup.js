module.exports = function(that) {

  require('webforge-testing').setup(that, {
    root: [__dirname, '..', '..', '..']
  });

  that.World.prototype.onPrompt = function(text) {
    client.execute(function(text) {
      window.require(['cms/modules/ui', 'jquery'], function(ui, $) {

        // fake prompt
        ui.prompt = function() {
          var d = $.Deferred();

          window.setTimeout(function() {
            d.resolve(text);
          }, 10);

          return d.promise();
        };

      });

      /*
      window.prompt = function(text) {
        return text;
      };
      */
    }, text);
  };

};