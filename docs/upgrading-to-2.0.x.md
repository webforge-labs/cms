# Upgrade to 2.0.x

Version 2.0 uses Symfony 4. You have to upgrade every BC break from Symfony. These are a lot, but here are some Tips:

- `$form = $this->get('form.factory')->createNamedBuilder(null, FormType::class, $json, array('csrf_protection'=>false))`
- `->add('name', 'text', array(` is now `->add('name', TextType::class, array(`
- `$form->bind()` is now `$form->handleRequest`
- `$form->isValid` is now `$form->isSubmitted() && $form->isValid()`
- bundle extension was removed in symfony 4.0