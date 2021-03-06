<?php

namespace Webforge\CmsBundle;

use Gaufrette\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Webforge\CmsBundle\Media\Manager;

class MediaControllerTest extends \Webforge\Testing\WebTestCase
{
    use \Webforge\Testing\TestTrait;

    protected function setupEmpty()
    {
        $client = self::makeClient($this->credentials['petra']);

        $this->loadAliceFixtures(
            array(
                'users'
            ),
            $client
        );

        $this->emptyFileSystemAndCache($client);

        return $client;
    }

    public function testMediaControllerReturnsTheEmptyStructure()
    {
        $client = $this->setupEmpty();

        $this->sendJsonRequest($client, 'GET', '/cms/media');

        $this->assertJsonResponse(200, $client)
            ->property('root')
            ->property('type', 'ROOT')->end()
            ->property('items')->isArray()->length(0);
    }

    public function testImagesAndOtherStuffSaving()
    {
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
            ->property('isExisting')->is(true)->end()
            ->property('thumbnails')->isObject()
            ->property('sm')
            ->property('orientation')->is('landscape')->end()
            ->property('isPortrait')->is(false)->end()
            ->property('isLandscape')->is(true)->end()
            ->property('width')->is($this->greaterThan(0))->end()
            ->property('height')->is($this->greaterThan(0))->end()
            ->property('url')->contains('fit')->contains('dsc03281.jpg')->end()
            ->end()
            ->property('xs')
            ->property('name')->is('xs')->end()
            ->end()
            ->end()
            ->property('key')->isNotEmpty()->get();

        $filesystem = $this->getFilesystem($client);
        $this->assertTrue($filesystem->has($dsc03281Key));

        $this->assertDatabaseBinaries($client, [
            $dsc03281Key => true
        ]);
    }

    public function testImageBatchDeleting()
    {
        $client = $this->setupEmpty();

        $keys = $this->storeFiles($client, [
            'test-image.png' => 'tapire.png',
            'something/test-image2.png' => 'mini-single.png'
        ]);

        $json = (object)['keys' => $keys];

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

    public function testExistingImagesWillBeOverwritten_AndNoticed_whenUploadedPerDropbox()
    {
        $client = $this->setupEmpty();

        list($key1, $oldKey) = $this->storeFiles($client, [
            'folder/tapire.png' => 'tapire.png',
            'folder/mini-single.png' => 'tapire.png'
        ]);

        $json = $this->getDropboxFiles();
        $uploadUrl = $json->dropboxFiles[0]->link;
        $json->dropboxFiles[0]->name = 'mini-single.png';
        $json->path = '/folder/';
        $this->sendJsonRequest($client, 'POST', '/cms/media/dropbox', $json);

        $this->assertJsonResponse(201, $client)
            ->property('warnings')->isArray()->length(1)
                ->key(0)->contains('folder/mini-single.png')->contains('existiert')->contains('überschrieben')->end()
            ->end()
            ->property('root')
                ->property('items')->isArray()->end()
            ->end();

        $manager = $client->getContainer()->get('webforge.media.manager');
        /** @var Manager $manager */
        $binary = $manager->findFileByPath('folder/mini-single.png');

        $this->assertTrue(
            file_get_contents($uploadUrl) === file_get_contents($manager->getStreamUrl($binary)),
            'the file should be overwritten from the uploaded dropbox url contents (contents do not equal from streams)'
        );

        $this->assertNotEquals($oldKey, $binary->getMediaFileKey(), 'the key should have changed, because thumbnails caching should reset');
        $this->assertFalse($this->getFilesystem($client)->has($oldKey), 'the old image file should be deleted');
    }

    public function testThatImagesWithBadNamesWillBeUrlified()
    {
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
                        ->key(0)->property('name', '20140825-imgp4127-kaeernten-superpilss.jpg');
    }

    public function testMovingASingleFileFromOneToAnotherDirectory()
    {
        $client = $this->setupEmpty();

        $this->storeFiles($client, [
            'folder1/folder2/tapire.png' => 'tapire.png',
            'other/mini.png' => 'mini-single.png'
        ]);

        $this->sendJsonRequest($client, 'POST', '/cms/media/move', (object)[
            'sources' => ["other/mini.png"],
            'target' => 'folder1/folder2/'
        ]);

        $response = $this->assertJsonResponse(200, $client);

        $this->assertMediaFiles($response, [
            'folder1/folder2/tapire.png',
            'folder1/folder2/mini.png'
        ]);
    }

    public function testMovingCompleteFolderToAnother()
    {
        $client = $this->setupEmpty();

        $this->storeFiles($client, [
            'folder1/folder2/tapire.png' => 'tapire.png',
            'folder1/mini.png' => 'mini-single.png'
        ]);

        $this->sendJsonRequest($client, 'POST', '/cms/media/move', (object)[
            'sources' => ["folder1"],
            'target' => 'otherroot/'
        ]);

        $response = $this->assertJsonResponse(200, $client);

        $this->assertMediaFiles($response, [
            'otherroot/folder1/folder2/tapire.png',
            'otherroot/folder1/mini.png'
        ]);
    }

    public function testRenamingAFolderInPath()
    {
        $client = $this->setupEmpty();

        $keys = $this->storeFiles($client, [
            'folder1/folder2/tapire.png' => 'tapire.png',
            'folder1/mini.png' => 'mini-single.png'
        ]);

        $this->sendJsonRequest($client, 'POST', '/cms/media/rename', (object)[
            'path' => "folder1/",
            'name' => 'tapire'
        ]);

        $response = $this->assertJsonResponse(200, $client);

        $this->assertMediaFiles($response, [
            'tapire/folder2/tapire.png',
            'tapire/mini.png'
        ]);
    }

    public function testRenamingAfile()
    {
        $client = $this->setupEmpty();

        $keys = $this->storeFiles($client, [
            'folder1/mini.png' => 'mini-single.png'
        ]);

        $this->sendJsonRequest($client, 'POST', '/cms/media/rename', (object)[
            'path' => "folder1/mini.png",
            'name' => 'mini-360px.png'
        ]);

        $response = $this->assertJsonResponse(200, $client);

        $this->assertMediaFiles($response, [
            'folder1/mini-360px.png'
        ]);

        $binaries = $client->getContainer()->get('webforge.media.manager')->findFiles($keys);
        $this->assertEquals('mini-360px.png', $binaries[0]->getMediaName(), 'name in entity should be changed as well');
    }

    public function testRenamingAfileWithoutNameMakesNoSense()
    {
        $client = $this->setupEmpty();

        $keys = $this->storeFiles($client, [
            'folder1/mini.png' => 'mini-single.png'
        ]);

        $this->sendJsonRequest($client, 'POST', '/cms/media/rename', (object)[
            'path' => "folder1/mini.png",
            'name' => ''
        ]);

        $this->assertJsonResponse(400, $client)
            ->property('validation')
            ->property('errors')
            ->isArray()->length(1)
            ->key(0)
            ->property('field')
            ->property('path', 'name')->end();
    }

    public function testUploadingFilesConventional()
    {
        $client = $this->setupEmpty();

        $file = $this->getResourceImage('background.jpg');
        $photo = new UploadedFile(
            (string)$file,
            'background.jpg',
            'image/jpeg',
            123
        );

        $client->request(
            'POST',
            '/cms/media/upload?thumbnails[]=xs',
            array('path' => 'uploaded/2017/02/'),
            array('files' => [$photo])
        );

        $response = $this->assertJsonResponse(201, $client)
            ->property('warnings')->isArray()->length(0)->end()
            ->property('files')->isArray()->length(1)
                ->key(0)
                    ->property('thumbnails')
                        ->property('xs')->end()
                    ->end()
                    ->property('url')->end()
                ->end();

        $this->sendJsonRequest($client, 'GET', '/cms/media');
        $response = $this->assertJsonResponse(200, $client);

        $this->assertMediaFiles($response, [
            'uploaded/2017/02/background.jpg'
        ]);
    }

    public function testUploadingAlreadyExistingFilesConventional()
    {
        $client = $this->setUpEmpty();

        $keys = $this->storeFiles($client, [
            '/2016-03-27/DSC03281.JPG' => 'background.jpg'
        ]);


        $file = $this->getResourceImage('mini-single.png');
        $photo = new UploadedFile(
            (string) $file,
            'DSC03281.JPG',
            'image/jpeg'
        );

        $client->request(
            'POST',
            '/cms/media/upload',
            array('path' => '/2016-03-27'),
            array('files' => [$photo])
        );

        $this->assertJsonResponse(201, $client)
            ->property('warnings')->isArray()->length(1)->end()
            ->property('files')->isArray()->length(1)
                ->key(0)
                    ->property('name', 'dsc03281.jpg');

        $this->sendJsonRequest($client, 'GET', '/cms/media');
        $response = $this->assertJsonResponse(200, $client);

        $this->assertMediaFiles($response, [
            '2016-03-27/dsc03281.jpg'
        ]);
    }

    public function testGetWithThumbnailsFilter()
    {
        $client = $this->setUpEmpty();
        $json = $this->getDropboxFiles();
        $this->sendJsonRequest($client, 'POST', '/cms/media/dropbox', $json);

        $this->sendJsonRequest($client, 'GET', '/cms/media?thumbnails[]=xs');

        $response = $this->assertJsonResponse(200, $client);

        $thumbnails = $response->property('root')
            ->property('items')->key(0)
                ->property('items')->key(0)
                    ->property('thumbnails')
                        ->property('xs')->isObject()->end()
                    ->get();

        $this->assertEquals(['xs'], array_keys((array)$thumbnails), 'only xs thumbnails should be generated');
    }

    private function assertMediaFiles($response, array $flatFiles)
    {
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

    private function assertDatabaseBinaries($client, array $files)
    {
        $dc = $client->getContainer()->get('dc');

        $binaries = $dc->getRepository('Binary')->findAll();
        $actualBinaries = array();

        foreach ($binaries as $binary) {
            $actualBinaries[] = $binary->getMediaFileKey();
        }

        $this->assertArrayEquals(array_keys($files), $actualBinaries, 'gaufretteKeys from Binaries in Database:');
    }

    private function getDropboxFiles()
    {
        // normally link is something like: https://dl.dropboxusercontent.com/1/view/hhkjprs3c7kxk98/Theo%20Family/2016-04-07%20Besuch%20Judith%2C%20Martin%20und%20Marlene/DSC03281.JPG
        // we fake it here to a local url
        $json = $this->parseJSON(
            <<<'JSON'
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

        $json->dropboxFiles[0]->link = str_replace(
            '%hostUrl%',
            getenv('SYMFONY_BASEURL'),
            $json->dropboxFiles[0]->link
        );

        return $json;
    }

    protected function getResourceImage($filename)
    {
        return $GLOBALS['env']['root']->sub('Resources/img/')->getFile($filename);
    }

    protected function emptyFileSystemAndCache($client)
    {
        $filesystem = $client->getContainer()->get('knp_gaufrette.filesystem_map')->get('cms_media');

        foreach ($filesystem->keys() as $key) {
            $filesystem->delete($key);
        }
    }

    protected function getFilesystem($client) : Filesystem
    {
        return $client->getContainer()->get('knp_gaufrette.filesystem_map')->get('cms_media');
    }

    protected function storeFiles($client, array $files)
    {

        // this is a shortcut to insert binaries, we could query the mediaController dropboxUpload everytime (or use another web request here, or use fixtures) which would be cleaner
        $manager = $client->getContainer()->get('webforge.media.manager');
        $manager->beginTransaction();

        $keys = array();
        foreach ($files as $targetPath => $source) {
            $targetPath = '/'.$targetPath;
            $targetName = mb_substr($targetPath, strrpos($targetPath, '/') + 1);
            $targetPath = mb_substr($targetPath, 0, strrpos($targetPath, '/'));

            $entity = $manager->addFile($targetPath, $targetName, $this->getResourceImage($source)->getContents());
            $keys[] = $entity->getMediaFileKey();
        }

        $manager->commitTransaction();
        return $keys;
    }
}
