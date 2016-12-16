<?php

namespace Webforge\CmsBundle;

use Symfony\Component\Process\Process;

class MediaControllerTest extends \Webforge\Testing\WebTestCase {

  use \Webforge\Testing\TestTrait;

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

  public function testMediaControllerReturnsTheEmptyStructure() {
    $client = $this->setupEmpty();

    $this->sendJsonRequest($client, 'GET', '/cms/media');

    $this->assertJsonResponse(200, $client)
      ->property('root')
        ->property('type', 'ROOT')->end()
        ->property('items')->isArray()->length(0);
  }
  
  public function testImagesAndOtherStuffSaving() {
    $client = $this->setupEmpty();

    $json = $this->getDropboxFiles();
    $this->sendJsonRequest($client, 'POST', '/cms/media/dropbox', $json);

    $dsc03281Key = $this->assertJsonResponse(201, $client)
      ->property('root')
        ->property('type', 'ROOT')->end()
        ->property('items')->isArray()
          ->key(0)
            ->property('name', '2016-03-27')->end()
            ->property('type', 'directory')->end()
            ->property('items')->isArray()
              ->key(0)
                ->property('name', 'dsc03281.jpg')->end()
                ->property('url')->isNotEmpty()->end()
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
                ->property('key')->isNotEmpty()->get()
    ;

    $filesystem = $this->getFilesystem($client);
    $this->assertTrue($filesystem->has($dsc03281Key));

    $this->assertDatabaseBinaries($client, [
      $dsc03281Key => TRUE
    ]);
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

    $this->assertDatabaseBinaries($client, []);
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
            ->property('name', 'folder2')->end()
            ->property('items')->isArray()
              ->key(0)->property('name', '20140825-imgp4127-kaeernten-superpilss.jpg')
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

    $response = $this->assertJsonResponse(200, $client);

    $this->assertMediaFiles($response, [
      'folder1/folder2/tapire.png',
      'folder1/folder2/mini.png'
    ]);
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

    $response = $this->assertJsonResponse(200, $client);

    $this->assertMediaFiles($response, [
      'otherroot/folder1/folder2/tapire.png',
      'otherroot/folder1/mini.png'
    ]);
  }

  private function assertMediaFiles($response, array $flatFiles) {
    $root = $response->property('root')
      ->property('items')->end()
      ->get();

    // do the tree => flat conversion (Tiefensuche)
    $files = [];
    $stack = [$root];
    $path = [];
    while (count($stack) > 0) {
      $item = array_pop($stack);

      if ($item->type === 'directory' || $item->type === 'ROOT') {
        foreach ($item->items as $child) {
          $stack[] = $child;
        }

        if ($item->type === 'directory') {
          $path[] = $item->name;
        }
      } else {
        $files[] = $f = implode('/', $path).'/'.$item->name;
      }
    }

    $this->assertArrayEquals($flatFiles, $files);
  }

  private function assertDatabaseBinaries($client, array $files) {
    $dc = $client->getContainer()->get('dc');

    $binaries = $dc->getRepository('Binary')->findAll();
    $actualBinaries = array();

    foreach ($binaries as $binary) {
      $actualBinaries[] = $binary->getGaufretteKey();
    }

    $this->assertArrayEquals(array_keys($files), $actualBinaries, 'gaufretteKeys from Binaries in Database:');
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

  protected function getFilesystem($client) {
    return $client->getContainer()->get('knp_gaufrette.filesystem_map')->get('cms_media');
  }

  protected function storeFiles($client, Array $files) {
    $filesystem = $this->getFilesystem($client);

    // this is a shortcut to insert binaries, we could query the mediaController dropboxUpload everytime (or use another web request here, or use fixtures) which would be cleaner
    $storage = $client->getContainer()->get('webforge.media.persistent_storage');

    foreach ($files as $target => $source) {
      $filesystem->write($target, $this->getResourceImage($source)->getContents(), $overwrite = true);
      $storage->persistFile($target, 'original-name-'.$target);
    }
    $storage->flush();

    return $filesystem;
  }
}
