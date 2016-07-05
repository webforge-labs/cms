define(['require', 'knockout', 'codemirror', 'marked', 'uikit-src/uikit', 'uikit-src/components/htmleditor'], function(require, ko, CodeMirror, marked) {
  

  ko.bindingHandlers.markdownEditor = {
    init: function(element, valueAccessor, allBindings, deprecated, bindingContext) {

      require(['uikit', 'uikit-htmleditor'], function(UIkit) {
        var editor = UIkit.htmleditor(element, {
          mode: 'split',
          maxsplitsize: 380,
          CodeMirror: CodeMirror,
          mdparser: marked,
          markdown: true,
          lblCodeview: 'Markdown',
          lblPreview: 'Vorschau',
          lblMarkedview: 'Markdown'
        });

        editor.addButtons({
          headline: {
            title  : 'Überschrift',
            label  : '<i class="uk-icon-header"></i>'
          }
        });

        editor.options.toolbar = [
          'headline',
          'bold',
          'italic',
          //'link',
          'image',
          'blockquote',
          'listUl',
          'listOl'
        ];


        editor
          .off('action.headline')
          .on('action.headline', function (e, cmEditor) {
             var cur = cmEditor.getCursor();
             var curLine = cmEditor.getLine(cur.line);

            if (curLine.charAt(0) === '#') {
              editor.replaceLine('\#$1');
            } else {
              editor.replaceSelection('\# $1');
            }
          });
          
        //var cursor = editor.editor.getCursor();
        //editor.editor.replaceRange(value, cursor);
        // 
       
/*            
        editor
          .off('action.image')
          .on('action.image', function (e, editor) {
            var cursor = editor.getCursor();
            var image = {
              alt: 'Bild Titel',
              src: 'http://'
            };
            var content = '![' + image.alt + '](' + image.src + ')';
            editor.replaceRange(content, cursor);
          });

            .on('render', function () {
                var regexp = editor.getMode() != 'gfm' ? /<img(.+?)>/gi : /(?:<img(.+?)>|!(?:\[([^\n\]]*)])(?:\(([^\n\]]*?)\))?)/gi;
                vm.images = editor.replaceInPreview(regexp, vm.replaceInPreview);
            })
            .on('renderLate', function () {

                while (vm.$children.length) {
                    vm.$children[0].$destroy();
                }

                Vue.nextTick(function () {
                    editor.preview.find('image-preview').each(function () {
                        vm.$compile(this);
                    });
                });
            });

        editor.element.on('htmleditor-save', function (e, editor) {
          if (editor.element[0].form) {
            var event = document.createEvent('HTMLEvents');
            event.initEvent('submit', true, false);
            editor.element[0].form.dispatchEvent(event);
          }
        });

        editor.on('render', function () {
          var regexp = /<script(.*)>[^<]+<\/script>|<style(.*)>[^<]+<\/style>/gi;
          editor.replaceInPreview(regexp, '');
        });
        */
      });

      ko.bindingHandlers.value.init(element, valueAccessor, allBindings, deprecated, bindingContext);
    },

    update: function(element, valueAccessor, allBindings, deprecated, bindingContext) {
      ko.bindingHandlers.value.update(element, valueAccessor, allBindings, deprecated, bindingContext);
    }
  };
});