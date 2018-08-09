# Installation

## Step by Step

- create an empty composer.json (no requires, no require devs, without autoloading)
```json
{
    "name": "iwep/website",
    "description": "The official website",
    "authors": [
        {
            "name": "p.scheit",
            "email": "p.scheit@ps-webforge.com"
        }
    ]
}
```
- a package json will be created
- run cms install command
- webforge register-package .
- run post-install.bat after revision
- search for @FIXME in created Bundle


# Infos (might be outdated)

## composer-packages

 - doctrine/orm
 - webforge/utils (DateTime)
 - webforge/doctrine (for custom Doctrine DateTimeTypes)
 - doctrine/doctrine-bundle (of course)
 - jms/serializer-bundle

## Structure

This repository is like a local installation of the cms. The local installation should only provide configuration and environment for testing. You would install Webforge\Cms by installing the Webforge\CmsBundle. So developers be aware: **put everything into the bundle**.


## configuration

### Parameters

#### entities_namespace

String (without trailing backslash) used to configure Webforge\Doctrine\Entities (dependency injection service: `dc`)


### Gaufrette

gaufrette_bundle has to be configured for "cms_media" to use the media manager (can be installed via vendors.yml)

```yaml
knp_gaufrette:
  adapters:
    local_files:
      local:
        directory:  %root_directory%/files/media
        create:     true
  filesystems:
    cms_media:
      adapter: local_files
```

