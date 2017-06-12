# cms [![Build Status](https://travis-ci.org/webforge-labs/cms.svg?branch=master)](https://travis-ci.org/webforge-labs/cms)

this is a testable body of a cms installation.

The "core" of the cms is in the Webforge\CmsBundle (located in `src\php\Webforge\CmsBundle`). The cms consists of this bundle and some components (see `src\php`).

## versioning

The npm package webforge-cms and the composer package webforge/cms will be always in sync in versioning. So some minor changes may only apply to the js package, to the php-package or to both of them.

## testing

phpunit

and

chimp

## Sponsoring

A big thank you to [BrowserStack.com](https://www.browserstack.com) for sponsoring a free Live account, that allows us to test on Iphones and other devices.

## BC Breaks for 1.13.x

- Dont add \Knp\Bundle\MarkdownBundle\KnpMarkdownBundle() to the appkernel. It will be added automatically
- create a `etc/cms/blocktypes.json` file (containing one empty array sufficient)
- split the `admin/config.js` into `etc/cms/blocktypes.json` and include it with: requirejs-json


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
