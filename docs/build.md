## how to

### Adding a module used in a tab

When you're using some requirejs call to load some dependencies from a tab(-template) like this:

```js
require(['admin/post/form', 'cms/modules/main', 'jquery'], function(controller, main, $) {
  // run code loaded for a tab
});
```

you have to add 'admin/post/form' as a tabmodule:

```js
var cmsBuilder = new Cms.Builder(gulp, __dirname, require, isDevelopment);

cmsBuilder.addTabModule('admin/post/list');
```

because you want to accomplish that:

- the module isnt combined into some other dependency and cannot be loaded separately
- the dependencies from the tabmodule shouldn't contain the already loaded modules from the cms (to avoid duplicate loading of dependencies)

*e.g.:*  
lets say the cms html-frame (where the tabs are displayed) loads knockout and jquery and your tabmodule requires them, then they must not be included in the 'admin/post/list'-module.

**Note:**  
This happens if you embed the form/bootstrap3/body.html.twig which calls the controller like that.


## Pitfalls

the datepicker.scss in vendor/ is the css file copied and renamed as .scss. Unfortunately bootstrap-datepicker is just available in less.

modules/booostrap-datepicker is always german (yet). It is copied from the german locale from the repo

be sure that the dependencies used from the Builder.js are added to dependencies (because child projects won't install the dev-dependencies from cms)


## Optimizing

have a look at this requirejs build.txt:

```
cms/main.js
----------------
jquery.js
knockout.js
knockout-mapping.js
knockout-collection.js
superagent.js
lodash.js
cms/modules/dispatcher.js
amplify.js
cms/TabsModel.js
i18next.js
Webforge/Translator.js
text.js
json.js
json!WebforgeCmsBundle/translations-compiled.json
cms/modules/translator.js
cms/TabModel.js
cms/MainModel.js
cms/ko-bindings/cms-tab.js
moment.js
cms/modules/moment.js
cms/ko-bindings/moment.js
bootstrap/button.js
bootstrap/transition.js
bootstrap/collapse.js
bootstrap/dropdown.js
cms/main.js

admin/post/form.js
----------------
admin/PostModel.js
cms/ko-bindings/markdown-editor.js
bootstrap-datepicker.js
cms/modules/bootstrap-datepicker.js
cms/ko-bindings/date-picker.js
bootstrap-notify.js
bootstrap/alert.js
cms/modules/main.js
admin/post/form.js

admin/post/list.js
----------------
admin/PostModel.js
bootstrap-notify.js
admin/post/list.js
```

The three layers are loaded separately: cms/main.js is loaded from the html-frame of the cms. The tabs with the js-modules: `admin/post/form` and `admin/post/list` are loaded into this frame. But for each tab `bootstrap-notify` is loaded. Lets assume this is a HUGE javascript file. That would lead to two expensive (size) web-requests when loading the tabs. So I would advise to require the bootstrap-notify in cms/main.js (or include it in the build layer):

```
cms/main.js
----------------
jquery.js
knockout.js
knockout-mapping.js
knockout-collection.js
superagent.js
lodash.js
cms/modules/dispatcher.js
amplify.js
cms/TabsModel.js
i18next.js
Webforge/Translator.js
text.js
json.js
json!WebforgeCmsBundle/translations-compiled.json
cms/modules/translator.js
cms/TabModel.js
cms/MainModel.js
cms/ko-bindings/cms-tab.js
moment.js
cms/modules/moment.js
cms/ko-bindings/moment.js
bootstrap/button.js
bootstrap/transition.js
bootstrap/collapse.js
bootstrap/dropdown.js
bootstrap-notify.js
cms/main.js

admin/post/form.js
----------------
admin/PostModel.js
cms/ko-bindings/markdown-editor.js
bootstrap-datepicker.js
cms/modules/bootstrap-datepicker.js
cms/ko-bindings/date-picker.js
bootstrap/alert.js
cms/modules/main.js
admin/post/form.js

admin/post/list.js
----------------
admin/PostModel.js
admin/post/list.js
```

this looks better now.