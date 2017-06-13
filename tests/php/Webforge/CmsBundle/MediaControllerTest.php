<?php

namespace Webforge\CmsBundle;

use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
                ->property('isExisting')->is(TRUE)->end()
                ->property('thumbnails')->isObject()
                  ->property('big')
                    ->property('orientation')->is('landscape')->end()
                    ->property('isPortrait')->is(false)->end()
                    ->property('isLandscape')->is(true)->end()
                    ->property('url')->contains('/images/cache/big')->contains('dsc03281.jpg')->end()
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

    $keys = $this->storeFiles($client, [
      'test-image.png' => 'tapire.png',
      'something/test-image2.png' => 'mini-single.png'
    ]);

    $json = (object) ['keys'=>$keys];

    $this->sendJsonRequest($client, 'DELETE', '/cms/media', $json);
    $this->assertJsonResponse(200, $client)
      ->property('root')
        ->property('type', 'ROOT')->end()
        ->property('items')->isArray()->length(0);

    // this is a very internal test
    $filesystem = $this->getFilesystem($client);
    $this->assertFalse($filesystem->has($keys[0]));
    $this->assertFalse($filesystem->has($keys[1]));

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
      'sources'=>["other/mini.png"],
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

    $this->storeFiles($client, [
      'folder1/folder2/tapire.png'=>'tapire.png',
      'folder1/mini.png'=>'mini-single.png'
    ]);

    $this->sendJsonRequest($client, 'POST', '/cms/media/move', (object) [
      'sources'=>["folder1"],
      'target'=>'otherroot/'
    ]);

    $response = $this->assertJsonResponse(200, $client);

    $this->assertMediaFiles($response, [
      'otherroot/folder1/folder2/tapire.png',
      'otherroot/folder1/mini.png'
    ]);
  }

  public function testRenamingAFolderInPath() {
    $client = $this->setupEmpty();

    $keys = $this->storeFiles($client, [
      'folder1/folder2/tapire.png'=>'tapire.png',
      'folder1/mini.png'=>'mini-single.png'
    ]);

    $this->sendJsonRequest($client, 'POST', '/cms/media/rename', (object) [
      'path'=>"folder1/",
      'name'=>'tapire'
    ]);

    $response = $this->assertJsonResponse(200, $client);

    $this->assertMediaFiles($response, [
      'tapire/folder2/tapire.png',
      'tapire/mini.png'
    ]);
  }

  public function testRenamingAfile() {
    $client = $this->setupEmpty();

    $keys = $this->storeFiles($client, [
      'folder1/mini.png'=>'mini-single.png'
    ]);

    $this->sendJsonRequest($client, 'POST', '/cms/media/rename', (object) [
      'path'=>"folder1/mini.png",
      'name'=>'mini-360px.png'
    ]);

    $response = $this->assertJsonResponse(200, $client);

    $this->assertMediaFiles($response, [
      'folder1/mini-360px.png'
    ]);

     $binaries = $client->getContainer()->get('webforge.media.manager')->findFiles($keys);
     $this->assertEquals('mini-360px.png', $binaries[0]->getMediaName(), 'name in entity should be changed as well');
  }

  public function testRenamingAfileWithoutNameMakesNoSense() {
    $client = $this->setupEmpty();

    $keys = $this->storeFiles($client, [
      'folder1/mini.png'=>'mini-single.png'
    ]);

    $this->sendJsonRequest($client, 'POST', '/cms/media/rename', (object) [
      'path'=>"folder1/mini.png",
      'name'=>''
    ]);

    $this->assertJsonResponse(400, $client)
      ->property('validation')
        ->property('errors')
          ->isArray()->length(1)
            ->key(0)
              ->property('field')
                ->property('path', 'name')->end()
     ;
  }

  public function testUploadingFilesConventional() {
    $client = $this->setupEmpty();

    $file = $this->getResourceImage('background.jpg');
    $photo = new UploadedFile(
      (string) $file,
      'background.jpg',
      'image/jpeg',
      123
    );

    $client->request(
      'POST',
      '/cms/media/upload',
      array('path' => 'uploaded/2017/02/'),
      array('files' => [$photo])
    );

    $response = $this->assertJsonResponse(201, $client);

    $this->assertMediaFiles($response, [
      'uploaded/2017/02/background.jpg'
    ]);

    $response
      ->property('files')->isArray()->length(1)
        ->key(0)
          ->property('name')->isNotEmpty()->end()
          ->property('key')->isNotEmpty()->end()
          ->property('isExisting')->is(TRUE)->end()
        ->end();
  }

  public function testUploadingAlreadyExistingFilesConventional() {
    $client = $this->setUpEmpty();
    $json = $this->getDropboxFiles();
    $this->sendJsonRequest($client, 'POST', '/cms/media/dropbox', $json);

    $file = $this->getResourceImage('mini-single.png');
    $photo = new UploadedFile(
      (string) $file,
      'DSC03281.JPG',
      'image/jpeg',
      123
    );

    $client->request(
      'POST',
      '/cms/media/upload',
      array('path' => '/2016-03-27'),
      array('files' => [$photo])
    );

    $response = $this->assertJsonResponse(201, $client);

    $response
      ->property('warnings')->isArray()->length(1)->end()
      ->property('files')->isArray()->length(1)
        ->key(0)
          ->property('name')->is('dsc03281.jpg')->end()
          ->property('key')->isNotEmpty()->end()
          ->property('isExisting')->is(TRUE)->end()
        ->end();
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
      $actualBinaries[] = $binary->getMediaFileKey();
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

    $json->dropboxFiles[0]->link = str_replace('%hostUrl%', getenv('SYMFONY_BASEURL'), $json->dropboxFiles[0]->link);

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

    // this is a shortcut to insert binaries, we could query the mediaController dropboxUpload everytime (or use another web request here, or use fixtures) which would be cleaner
    $manager = $client->getContainer()->get('webforge.media.manager');
    $manager->beginTransaction();

    $keys = array();
    foreach ($files as $targetPath => $source) {
      $targetPath = '/'.$targetPath;
      $targetName = mb_substr($targetPath, strrpos($targetPath, '/')+1);
      $targetPath = mb_substr($targetPath, 0, strrpos($targetPath, '/'));

      $entity = $manager->addFile($targetPath, $targetName, $this->getResourceImage($source)->getContents());
      $keys[] = $entity->getMediaFileKey();
    }

    $manager->commitTransaction();
    return $keys;
  }
}
