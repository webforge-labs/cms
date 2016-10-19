# Forms

Forms are embeds written in twig with macros:

```twig
{% import 'WebforgeCmsBundle:form/bootstrap3:elements.html.twig' as form %}

{% embed "WebforgeCmsBundle:form/bootstrap3:body.html.twig" 
   with {
    'module': 'admin/post/form',
    'dependencies': [
      'cms/ko-bindings/markdown-editor',
      'cms/ko-bindings/date-picker',
      'bootstrap-select'
    ]
  }
%}

  {% block body %}
    <div data-bind="with: entity">
      {{ form.input_text('Überschrift', 'title', 'text') }}
      {{ form.datetime('Veröffentlichung', 'published') }}
      {{ form.single_entity_chooser('Kategorie', 'categories') }}
      {{ form.markdown('Teaser', 'teaserMarkdown') }}
      {{ form.markdown('Inhalt', 'markdown') }}
      {{ form.multiple_files_chooser('Bilder', 'images') }}
    </div>

    {{ form.submit('Speichern', 'save') }}
  {% endblock %}

{% endembed %}
```

You have to manage the js dependencies on your own, that your macros might need.

## macros / elements

### single_entity_chooser

**dependencies**: `bootstrap-select`

This is a wrapper around bootstrap-select that allows you to pick an object/entity by propertyId and propertyLabel. 
The second parameter passed to the macro is the `$name`. There are two bindings for the single_entity_choose which are relevant. First the knockout-options binding is bound with: `${name}Options`. This should be an collection of objects having `$propertyId` (default `id`) and `$propertyLabel`-properties. This is the universe of the value in your entity.
The second binding is bound against `${name}OptionValue` You can use an computed-observable for this:

```js
    this.categoriesOptionValue = ko.computed({
      read: function() {
        return _.map(ko.unwrap(that.categories), function(category) {
          return ko.unwrap(category.id);
        });
      },
      write: function(categoryId) {
        var category = _.find(ko.unwrap(that.categoriesOptions), function(category) {
          return ko.unwrap(category.id) == categoryId;
        });

        that.cate
      }
    });
```
given that `$name` is `categories`. Notice that both bindings are bound to the entity, so you have to put the universe-collection onto the prototype for example.