# Upgrade to 2.0.x

Version 2.0 uses Symfony 4. You have to upgrade every BC break from Symfony. These are a lot, but here are some Tips:

- `$form = $this->get('form.factory')->createNamedBuilder(null, FormType::class, $json, array('csrf_protection'=>false))`
- if your method is put you have to change `array('csrf_protection'=>false)` to `array('csrf_protection'=>false, 'method'=>'put')`
- `->add('name', 'text', array(` is now `->add('name', TextType::class, array(`
- `$form->bind()` is now `$form->handleRequest`
- `$form->isValid` is now `$form->isSubmitted() && $form->isValid()`
- Forms that are sent empty will fail to pass isSubmitted(). So for empty json sets as a farm, it returns only one error (form is empty) instead of errors for all required fields
- bundle extension was removed in symfony 4.0
- replace "%root_directory%" with "%kernel.project_dir%"
- env variables with "SYMFONY__" wont work anymore use (in config.yml): `tenant_name: "%env(SYMFONY__TENANT_NAME)%"` for workarounds or use dev variables directly
- use your own `security.yml` file