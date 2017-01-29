# Binary Files

Binaries are stored in the gaufrette File system and referenced with its gaufrette key in the database. A gaufrette key is a relative path in that filesystem. (it has no trailing-slash)  
There is a `Model\GaufretteFileInterface` which represents an entity with a gaufrette key. For example call this a binary in your model:

```json
    {
      "name": "Binary",

      "properties": {
        "id": { "type": "DefaultId" },

        "gaufretteKey": { "type":"String" }
      }
    }
```

There is a `Webforge\CmsBundle\Serialization\JmsSerializerGaufretteBinaryHandler` which handles the serializiation for such an entity and exports images with its thumbnails. Thumbnails are generated with the liip_imagine_bundle. The configuration for gaufrette and liip_imagine resides under `vendors.yml`

## frontend

files that aren't existing won't be displayed in the multiple-files-chooser