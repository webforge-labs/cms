# How to upgrade to 3.0

in 3.0.0 symfony flex is used. This will change the majority of your config files in your project and give you a new folder-structure.
we will follow this: https://symfony.com/doc/current/setup/flex.html

- `composer remove webforge/cms`
- `composer remove symfony/symfony` (allthough its not required in your composer.json it is installed and locked)
- composer remove all-other-symfony-bundles (for now)
- `composer require symfony/flex`
- leave your files in etc/symfony/* you will need the to configure
- add `"conflict": { "symfony/symfony": "*" }` to your composer.json
- install the basics: `composer require annotations orm-pack twig templating logger mailer form security translation validator && composer require --dev maker-bundle profiler`
- move Kernel to php and add to composer autoload
- rename www to public and adjust index.php
- add var as appvar volume to docker-compose
- `composer require "webforge/cms:^3.0"`
- install cms soft-dependencies `composer require jbouzekri/phumbor-bundle jms/serializer-bundle friendsofsymfony/user-bundle browser-kit process`
- cleanup the .env file (reference APP_SECRET={{SECRET}} )
- move parameters.xxx.yml to etc/docker/php and update docker-compose*.yml files
- add `Webforge\CmsBundle\WebforgeCmsBundle::class => ['all' => true],` to bundles.php
- add `Webforge\UserBundle\WebforgeUserBundle::class => ['all' => true]` to bundles.php
- add missing bundles to bundles and add env for testing
- rename prod to production
- rename www to public in your dockerfile
- `yarn install webforge-cms@latest`
- fix $this->get('') calls in all your controllers. Eliminate them with containerAction-Dependency Injection or Setter Injection