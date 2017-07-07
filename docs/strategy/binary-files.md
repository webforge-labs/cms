# Binary Files

Binaries are stored in the gaufrette File system and referenced with its mediaFileKey in the database. A Binary entity must implement: `\Webforge\CmsBundle\Model\MediaFileEntityInterface`. It should use `\Webforge\CmsBundle\Media\MediaFileEntityMetadata` to implement the getters and setters for storing arbitrary mediaMeta.


```json
    {
      "name": "Binary",

      "properties": {
        "id": { "type": "DefaultId" },

        "mediaFileKey": { "type": "String" },
        "mediaName": { "type": "String" },
        "mediaMeta": { "type": "Object", "nullable": true }
      }
    }
```

There are `Webforge\CmsBundle\Serialization\MediaFileHandlerInterface` which handles the serializiation for such an entity. For example the imagine thumbnails handler (or thumbor thumbnails handler) create thumbnails while serialization. see `images.yml` in the standard edition for configuration.

## ATTENTION

i screwed up: Webforge\CmsBundle\Model\MediaFileEntityInterface; is extend with setMediaMetadata and getMediaMetadata. It is available to store thumbnail width and height into the db. Instead of using a doctrine\common\Cache (which was really annoying as a filesystem-cache implementation).  
To make this work we need to flush after serialization mechanisms to persist the cache. This might have undesired side-effects.  
Another mistake is, that `Webforge\CmsBundle\Media\FileInterface` was designed to be an representational interface which is decoupled from the persistance of the media file entity. But it isnt anymore, because the entity is passed to the Handler as well.

how to fix?: Maybe implement a caching system that can store metadata for a file/thumbnail and persist it separately (in another db-table or other db? redis?)

## frontend

files that aren't existing won't be displayed in the multiple-files-chooser