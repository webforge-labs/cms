<?php

namespace Webforge\CmsBundle;

use Symfony\Component\Process\Process;

class MediaControllerTest extends \Webforge\Testing\WebTestCase {

  protected function setupEmpty() {
    $client = self::makeClient($this->credentials['petra']);

    $this->loadAliceFixtures(
      array(
        'users'
      ),
      $client
    );

    $this->emptyFileSystemAndCache();

    return $client;
  }
  
  public function testImagesAndOtherStuffSaving() {
    $client = $this->setupEmpty();

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
                ->property('name', 'dsc03281.jpg')->end()
                ->property('url')->is($this->logicalNot($this->isEmpty()))->end()
                ->property('thumbnails')->isObject()
                  ->property('big')
                    ->property('orientation')->is('landscape')->end()
                    ->property('isPortrait')->is(false)->end()
                    ->property('isLandscape')->is(true)->end()
                    ->property('url')->contains('/images/cache/big/2016-03-27/dsc03281.jpg')->end()
                  ->end()
                  ->property('sm')
                    ->property('orientation')->is('landscape')->end()
                  ->end()
                ->end()
                ->property('key', '2016-03-27/dsc03281.jpg')->end()
              ->end()
            ->end()
          ->end()
        ->end()
    ;
  }

  public function testImageBatchDeleting() {
    $client = $this->setupEmpty();

    $filesystem = $this->storeFiles($client, [
      'test-image.png' => 'tapire.png',
      'something/test-image2.png' => 'mini-single.png'
    ]);

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
        ->property('items')->isArray()->length(0);

    $this->assertFalse($filesystem->has('test-image.png'));
    $this->assertFalse($filesystem->has('something/test-image2.png'));
  }

  public function testExistingImagesWillNotBeOverwritten_AndNoticed() {
    $client = $this->setupEmpty();

    $this->storeFiles($client, [
      'folder/tapire.png' => 'tapire.png',
      'folder/mini-single.png' => 'mini-single.png'
    ]);

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
    $client = $this->setupEmpty();

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

  public function testMovingASingleFileFromOneToAnotherDirectory() {
    $client = $this->setupEmpty();

    $this->storeFiles($client, [
      'folder1/folder2/tapire.png' => 'tapire.png',
      'other/mini.png' => 'mini-single.png'
    ]);

    $this->sendJsonRequest($client, 'POST', '/cms/media/move', (object) [
      'keys'=>["other/mini.png"],
      'target'=>'folder1/folder2/'
    ]);

    $this->assertJsonResponse(200, $client)
      ->property('root')
        ->property('items')->isArray()
          ->key(0)
            ->property('key', 'folder1/')->end()
            ->property('items')->isArray()
              ->key(0)
                ->property('key', 'folder1/folder2/')->end()
                ->property('items')->isArray()
                  ->key(1)
                    ->property('key', 'folder1/folder2/tapire.png')->end()
                  ->end()
                  ->key(0)
                    ->property('key', 'folder1/folder2/mini.png')->end()
                  ->end()
    ;
  }

  public function testMovingCompleteFolderToAnother() {
    $client = $this->setupEmpty();

    $filesystem = $this->storeFiles($client, [
      'folder1/folder2/tapire.png'=>'tapire.png',
      'folder1/mini.png'=>'mini-single.png'
    ]);


    $this->sendJsonRequest($client, 'POST', '/cms/media/move', (object) [
      'keys'=>["folder1"],
      'target'=>'otherroot/'
    ]);

    $this->assertJsonResponse(200, $client);

    $this->assertFileSystemFiles($filesystem, [
      'otherroot/folder1/folder2/tapire.png',
      'otherroot/folder1/mini.png'
    ]);
  }

  private function assertFileSystemFiles($filesystem, array $keys) {
    $fileKeys = array_filter(
      $filesystem->keys(),
      function($key) use ($filesystem) {
        return !$filesystem->isDirectory($key);
      }
    );

    $this->assertEquals($keys, $fileKeys, 'keys from filesystem which are files', 0, 1, TRUE);
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

    $dirs = array();
    foreach ($filesystem->keys() as $key) {
      if (!$filesystem->isDirectory($key)) {
        $filesystem->delete($key);
      } else {
        $dirs[] = $key;
      }
    }
    
    // order longest to first
    usort($dirs, function($a, $b) {
      return strlen($b) - strlen($a);
    });

    foreach ($dirs as $key) {
      $filesystem->delete($key);
    }

    // clearing the thumbnails cache is important: otherwise we'll get the "no meta cache key"
    $GLOBALS['env']['root']->sub('www/images/cache/')->delete();
    $GLOBALS['env']['root']->sub('files/cache/imagine-meta')->delete();
  }

  protected function storeFiles($client, Array $files) {
    $filesystem = $client->getContainer()->get('knp_gaufrette.filesystem_map')->get('cms_media');

    foreach ($files as $target => $source) {
      $filesystem->write($target, $this->getResourceImage($source)->getContents(), $overwrite = true);
    }

    return $filesystem;
  }
}
