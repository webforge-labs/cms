define(function(require) {

  var ko = require('knockout');
  var CodeMirror = require('codemirror');
  var marked = require('marked');

  require('uikit-src/uikit');
  require('uikit-src/components/htmleditor');

  // configure CodeMirror:
  require('codemirror/mode/htmlmixed/htmlmixed');
  require('codemirror/mode/gfm/gfm');
  /*
  require('codemirror/mode/xml/xml');
  require('codemirror/mode/htmlembedded/htmlembedded');
  require('codemirror/addon/mode/overlay');
  require('codemirror/mode/gfm/gfm');
  require('codemirror/addon/selection/active-line');
  require('codemirror/addon/selection/mark-selection');
  require('codemirror/addon/wrap/hardwrap');
  require('codemirror/addon/edit/matchbrackets');
  require('codemirror/addon/edit/closetag');
  require('codemirror/addon/edit/closebrackets');
  require('codemirror/addon/edit/matchtags');
  require('codemirror/addon/display/placeholder');
  require('codemirror/addon/hint/anyword-hint');
  require('codemirror/addon/fold/markdown-fold');
  require('codemirror/addon/fold/xml-fold');
  require('codemirror/addon/hint/show-hint');
  require('codemirror/addon/hint/javascript-hint');
  require('codemirror/addon/hint/xml-hint');
  */

//  { mode: 'htmlmixed', lineWrapping: true, dragDrop: false, autoCloseTags: true, matchTags: true, autoCloseBrackets: true, matchBrackets: true, indentUnit: 4, indentWithTabs: false, tabSize: 4, hintOptions: {completionSingle:false} },

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

            editor.replaceLine(curLine.charAt(0) === '#' ? '\#$1' : '\# $1');
            cmEditor.setCursor({ line: cur.line, ch: null });
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

      ko.bindingHandlers.textInput.init(element, valueAccessor, allBindings, deprecated, bindingContext);
    }
  };
});