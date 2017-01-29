# Create a new Tab

- use a controller to create the contents of the tab
- put route to tab content into sidebar
- add a tab module in the `gulpfile.js`

You only need a route that returns the content of the tab. Put the link into the sidebar:

```php
    $this->render('WebforgeCmsBundle::base.html.twig', array(
      //[...]

      'sidebar'=>array(
          'Blog'=>array(
            array(
              'label'=>'Posts verwalten',
              'tab'=>array(
                'label'=>'Posts verwalten',
                'id'=>'posts-list',
                'url'=>'/cms/posts/list'
              )
            ),
```

Dont forget to add a tab module into the gulpfile.js

```js
var cmsBuilder = new Cms.Builder(gulp, __dirname, require, isDevelopment);

// [...]
cmsBuilder.addTabModule('admin/post/list', { include: ['cms/ko-components/multiple-files-chooser']});

```
# Create a component

- create html
- create js module (returns: `{ viewModel: function(params) {}, template: htmlString }` )
- add name to `src\js\cms\ko-components\index.js`


# save images

- create mediaTree and binary in model.json

```
    {
      "name": "Binary",

      "properties": {
        "id": { "type": "DefaultId" },

        "mediaFileKey": { "type": "String" },
        "mediaName": { "type": "String" }
      }
    },

    {
      "name": "MediaTree",

      "properties": {
        "id": { "type": "DefaultId" },

        "content": { "type": "Text" },
        "created": { "type": "DateTime" }
      }
    },
```

- binary  `implements \Webforge\CmsBundle\Model\MediaFileEntityInterface`