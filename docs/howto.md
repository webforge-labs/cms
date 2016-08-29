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