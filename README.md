# cms

this repo contains the Webforge/CmsBundle and a symfony bundle (AppBundle) that allows self-tests 

The "core" of the cms is in the Webforge\CmsBundle (located in `src\php\Webforge\CmsBundle`). The cms consists of this bundle and some components (see `src\php`).

## versioning

The npm package webforge-cms and the composer package webforge/cms will be always in sync in versioning. So some minor changes may only apply to the js package, to the php-package or to both of them.

## testing

phpunit

and several javascript tests

npm test

## Sponsoring

A big thank you to [BrowserStack.com](https://www.browserstack.com) for sponsoring a free Live account, that allows us to test on Iphones and other devices.

## changelog

## BC Breaks 3.1.x

- added resetMediaMetadata to MediaFileEntityInterface. If you are using the MediaFileEntityMetadata-Trait you're all good

## BC Breaks for 3.0.x

- symfony/symfony is now longer a dependency. You have to use symfony/flex and configure EVERYTHING on your own. (prepend config is no longer used)
- the public folder is now www (not public)
- construct CommonController with em and dc
- Symfony\Kernel was removed
- CommonController is now an Symfony-AbstractController and therefore has narrower dependency injection: You need to refactor your controller code
- [Read Upgrading to 3.0](docs/upgrading-to-3.0.x.md)


## BC Breaks for 2.3.x

- after serialization there will be no automatic flush (after each binary serialization) as before - this was slow as hell. You have call em->flush yourself
- exif is now read with lsolesen/pel, not with php native anymore. This might have more or less failing cases for you
- original image metadata (allthough with rotation-exif-data) wont be automatically rotated, because the physical file is not rotated as well, but thumbnails-meta will be rotated and images are physically rotated

## BC Breaks for 2.2.x

- you need php 7.2 to install
- dependency symfony/symfony updated to 4.1
- use phpunit 6.5.x
- TestCaseTrait now uses the mockery trait, so you have to install mockery to 1.1.0 now 

## BC Breaks for 2.1.x

- files uploaded to the media controller will overwrite existing files (but warnings will still be generated)

## Upgrade to 2.0.x

- [Read Upgrading to 2.0](docs/upgrading-to-2.0.x.md)
- Update to Symfony 4.0

## BC Breaks for 1.15.x

- use PHPUnit 5.x.x
- BlockExtender Interface has been changed to pass value of blocks-array by reference

## BC Breaks for 1.14.x

- MediaFileEntityInterface has two new methods: `setMediaMetadata` and `getMediaMetadata`
- add this to your model.json for binaries `"mediaMeta": { "type": "Object", "nullable": true }`
- use the trait: `Webforge\CmsBundle\Media\MediaFileEntityMetadata` to implement the methods
- generation of thumbnails with imagine is deprecated
- thumbnails will be created with thumbor and therefore have no `width` and `height` per default (pass metadata_only: true to the configuration of the transformation to enable getting metadata)
- add `cms.version` to your global twig variables


## BC Breaks for 1.13.x

- Dont add \Knp\Bundle\MarkdownBundle\KnpMarkdownBundle() to the appkernel. It will be added automatically
- create a `etc/cms/blocktypes.json` file (containing one empty array sufficient)
- split the `admin/config.js` into `etc/cms/blocktypes.json` and include it with: requirejs-json
- Refactor `When I click on "" in context` to `When I click on ""`

## BC Breaks for 1.12.x

- change `site.url` into `cms.site.url` in global twig variables
- change `site.title` into `cms.site.title` in global twig variables

## BC Breaks for 1.11.x

- Rename Webforge\Common\String into Webforge\Common\StringUtil (because of PHP 7.x)

## BC Breaks for 1.7.x

- A link in the CMS adds and activates the tab with one click (instead of two). So calling tabs.open() was changed. add and select will still work.

## BC Breaks for 1.6.x

- You need to provide the database parameters again (database_host, database_port, database_name, database_user, database_password)
- You need to set `sidebar.activeGroup` in the base.html.twig template
- You need to set `site.url` and `site.title` in the base.html.twig template
- You need to set `cms.title` and (optional) `site.xsTitle` in the base.html.twig template (do this with a global twig extension: `Twig_Extension_GlobalsInterface`)
