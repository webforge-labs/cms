<?php

namespace Webforge\CmsBundle;

use Symfony\Component\Process\Process;

class MediaControllerTest extends \Webforge\Testing\WebTestCase {
  
  public function testImagesAndOtherStuffSaving() {
    $client = self::makeClient($this->credentials['petra']);

    $this->loadAliceFixtures(
      array(
        'users'
      ),
      $client
    );

    $this->emptyFileSystemAndCache();

    $json = $this->getDropboxFiles();
    $this->sendJsonRequest($client, 'POST', '/cms/media/dropbox', $json);

    $this->assertJsonResponse(201, $client)
      ->property('root')
        ->property('type', 'ROOT')->end()
        ->property('items')->isArray()
          ->key(0)
            ->property('name', '2016-03-27')->end()
            ->property('type', 'directory')->end()
            ->property('items')->isArray()
              ->key(0)
                ->property('name', 'DSC03281.JPG')->end()
                ->property('url')->is($this->logicalNot($this->isEmpty()))->end()
                ->property('thumbnails')->isObject()
                  ->property('big')
                    ->property('orientation')->is('landscape')->end()
                    ->property('isPortrait')->is(false)->end()
                    ->property('isLandscape')->is(true)->end()
                    ->property('url')->contains('/images/cache/big/2016-03-27/DSC03281.JPG')->end()
                  ->end()
                  ->property('sm')
                    ->property('orientation')->is('landscape')->end()
                  ->end()
                ->end()
                ->property('key', '2016-03-27/DSC03281.JPG')->end()
              ->end()
            ->end()
          ->end()
        ->end()
    ;
  }

  public function testImageBatchDeleting() {
    $client = self::makeClient($this->credentials['petra']);

    $this->loadAliceFixtures(
      array(
        'users'
      ),
      $client
    );

    $filesystem = $this->getContainer()->get('knp_gaufrette.filesystem_map')->get('cms_media');
    $filesystem->write('test-image.png', $this->getResourceImage('tapire.png')->getContents(), $overwrite = true);
    $filesystem->write('something/test-image2.png', $this->getResourceImage('mini-single.png')->getContents(), $overwrite = true);

    $json = $this->parseJSON(<<<'JSON'
{
  "keys": ["test-image.png", "something/test-image2.png"]
}
JSON
    );

    $this->sendJsonRequest($client, 'DELETE', '/cms/media', $json);
    $this->assertJsonResponse(200, $client)
      ->property('root')
        ->property('type', 'ROOT')->end()
        ->property('items')->isArray();

    $this->assertFalse($filesystem->has('test-image.png'));
    $this->assertFalse($filesystem->has('something/test-image2.png'));
  }

  public function testExistingImagesWillNotBeOverwritten_AndNoticed() {
    $client = self::makeClient($this->credentials['petra']);

    $this->loadAliceFixtures(
      array(
        'users'
      ),
      $client
    );

    $filesystem = $this->getContainer()->get('knp_gaufrette.filesystem_map')->get('cms_media');
    $filesystem->write('folder/tapire.png', $this->getResourceImage('tapire.png')->getContents(), $overwrite = true);
    $filesystem->write('folder/mini-single.png', $this->getResourceImage('mini-single.png')->getContents(), $overwrite = true);

    $json = $this->getDropboxFiles();
    $json->dropboxFiles[0]->name = 'mini-single.png';
    $json->path = '/folder/';
    $this->sendJsonRequest($client, 'POST', '/cms/media/dropbox', $json);

    $this->assertJsonResponse(201, $client)
      ->property('root')
        ->property('items')->isArray()->end()
      ->end()
      ->property('warnings')->isArray()->length(1)
        ->key(0)->contains('folder/mini-single.png')->contains('existiert')->end()
      ->end()
    ;
  }

  public function testThatImagesWithBadNamesWillBeUrlified() {
    $client = self::makeClient($this->credentials['petra']);

    $this->loadAliceFixtures(
      array(
        'users'
      ),
      $client
    );

    $this->emptyFileSystemAndCache();

    $json = $this->getDropboxFiles();
    $json->dropboxFiles[0]->name = '20140825-IMGP4127_Käernten superpilß.jpg';
    $json->path = '/folder2/';
    $this->sendJsonRequest($client, 'POST', '/cms/media/dropbox', $json);

    $this->assertJsonResponse(201, $client)
      ->property('root')
        ->property('items')->isArray()
          ->key(0)
            ->property('key', 'folder2/')->end()
            ->property('items')->isArray()
              ->key(0)->property('key', 'folder2/20140825-imgp4127-kaeernten-superpilss.jpg')
    ;
  }

  private function getDropboxFiles() {
    // normally link is something like: https://dl.dropboxusercontent.com/1/view/hhkjprs3c7kxk98/Theo%20Family/2016-04-07%20Besuch%20Judith%2C%20Martin%20und%20Marlene/DSC03281.JPG
    // we fake it here to a local url
    $json = $this->parseJSON(<<<'JSON'
{
  "dropboxFiles": [
    {
      "isDir": false,
      "link": "%hostUrl%/assets/img/mini-single.png",
      "name": "DSC03281.JPG",
      "thumbnailLink": "https://api-content.dropbox.com/r11/t/AAA217oe69t24DpE6JGjbftPxAjLMBlPRyX2On6_ot1FhA/12/886689/jpeg/_/0/4/DSC03281.JPG/CKGPNiACIAcoAigH/hhkjprs3c7kxk98/AABbOb3Ey4HiK9oLYO6InxRsa/DSC03281.JPG?bounding_box=75&mode=fit",
      "is_dir": false,
      "bytes": 2089395,
      "icon": "https://www.dropbox.com/static/images/icons64/page_white_picture.png"
    }
  ],
  "path": "/2016-03-27/"
}
JSON
    );

    $json->dropboxFiles[0]->link = str_replace('%hostUrl%', $this->getContainer()->getParameter('hostUrl'), $json->dropboxFiles[0]->link);

    return $json;
  }

  protected function getResourceImage($filename) {
    return $GLOBALS['env']['root']->sub('Resources/img/')->getFile($filename);
  }

  protected function emptyFileSystemAndCache() {
    $filesystem = $this->getContainer()->get('knp_gaufrette.filesystem_map')->get('cms_media');

    foreach ($filesystem->keys() as $key) {
      if (!$filesystem->isDirectory($key)) {
        $filesystem->delete($key);
      }
    }

    // clearing the thumbnails cache is important: otherwise we'll get the "no meta cache key"
    $GLOBALS['env']['root']->sub('www/images/cache/')->delete();
    $GLOBALS['env']['root']->sub('files/cache/imagine-meta')->delete();
  }
}
