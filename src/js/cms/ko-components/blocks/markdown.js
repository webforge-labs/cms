define(['knockout'], function(ko) {

  var vm = function(params) {
    var that = this;

    params.init(this, {
      property: 'value',
      options: {
        rows: 14
      }
    });

    if (!params.block.computedLabel()) { // use the first markdown in compounds
      params.block.computedLabel(function () {
        var markdown = that.value();
        if (typeof(markdown) === "string" && markdown != "") {
          return (markdown.substr(0, 50).replace(/^\#+(.*)$/mg, '$1: ')) + '…';
        }

        return ko.unwrap(that.block.label);
      });
    }

  };

  var template = '<textarea data-bind="markdownEditor: value, attr: options" class="form-control"></textarea>';

  return { viewModel: vm, template: template };

});