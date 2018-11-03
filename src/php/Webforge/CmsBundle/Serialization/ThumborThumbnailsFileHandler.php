<?php

namespace Webforge\CmsBundle\Serialization;

use Psr\Log\LoggerInterface;
use stdClass;
use Webforge\CmsBundle\Media\FileInterface;
use Webforge\CmsBundle\Media\Manager;
use Webforge\CmsBundle\Model\MediaFileEntityInterface;

class ThumborThumbnailsFileHandler implements MediaFileHandlerInterface
{
    protected $transformations;
    protected $transformer;

    private $enabled;

    /**
     * @var Manager
     */
    private $manager;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct($transformations, $transformer, $filesystem, LoggerInterface $logger)
    {
        $this->transformer = $transformer;
        $this->transformations = $transformations;
        $this->enabled = count($this->transformations) > 0;
        $this->logger = $logger;
    }

    public function serializeToFile(
        FileInterface $mediaFile,
        MediaFileEntityInterface $entity,
        stdClass $file,
        array $options
    ) {
        if ($this->enabled && $mediaFile->isImage()) {
            if (!isset($file->thumbnails)) {
                $file->thumbnails = [];
            } else {
                $file->thumbnails = (array)$file->thumbnails;
            }

            foreach ($this->transformations as $name => $transformation) {
                if ($options && $options['filters'] && isset($options['filters']['thumbnails']) && is_array($options['filters']['thumbnails'])) {
                    if (!in_array($name, $options['filters']['thumbnails'])) {
                        continue;
                    }
                }

                $meta = new stdClass;
                $builder = $this->transformer->transform($file->url, $name);

                if (array_key_exists('metadata_only', $transformation) && $transformation['metadata_only']) {
                    $metadataUrl = (string)$builder->build();

                    // build the real url without metadata
                    $builder->metadataOnly(false);
                    $meta->url = (string)$builder->build();

                    $this->fetchAndMergeMetadata($metadataUrl, $entity, 'thumbor.'.$name, $meta, $file);
                } else {
                    $meta->url = (string)$builder->build();
                }

                $meta->name = $name;

                $file->thumbnails[$name] = $meta;
            }
        }
    }

    public function fetchAndMergeMetadata(
        $url,
        MediaFileEntityInterface $entity,
        $cacheKey,
        stdClass $thumbnailMeta,
        stdClass $file
    ) {
        $metadata = $entity->getMediaMetadata($cacheKey);

        if (!$metadata) {
            $this->logger->info('get metadata for file with thumbor-url: '.$url);
            /* this does not work right now 100%, because: https://github.com/thumbor/thumbor/issues/949

            heres the thing:
            if one would upload an image where the camera is hold in portrait mode, the camera will write the file in landscape mode anyway
            but put a orientation (right top) into the exif metadata

            windows10 (even explorer preview), irfanview, macs they all will display the image upwards

            php getimagesizefromstring will return the real format (not the rotated) which is width>>height
            (we got that wrong all the time)

            when the image is passed through thumbor (even with no filters applied) it will get rotated according the exif tag. so then the real image is portrait
            of course this applies to any thumbnail, too

            but this is exactly what thumbor gets wrong, the 'source' tag in thumbor is correct
            but the target tag width and height should be switched (because thumbor has rotated the thumbnail physically)

            for a workaround we have to read the exif data here, to determine if target needs to be rotated
            according to: https://cdn.ich-will-ein-pony.de/EXIF_Orientations.jpg

            */
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => "Accept: application/json\r\n"
                ]
            ]);
            $metadata = json_decode(file_get_contents($url, false, $context))->thumbor;

            $streamUrl = $this->manager->getStreamUrl($entity);

            $exif = $this->readExifData($streamUrl, 'IFD0');

            if (is_array($exif)) {
                // it maybe twisted because thumbor did not normalized with exif rotation for the thumbnail when providing metadata (see github issue)
                $orientation = isset($exif['Orientation']) ? (int)$exif['Orientation'] : 1;

                if ($orientation >= 5 && $orientation <= 8) {
                    // this is the gretchen-question: should this be flipped, or not? the "original" is in physical form landscape, but would be shown twisted
                    /*
                    $metadata->source = (object)[
                        'width' => $metadata->source->height,
                        'height' => $metadata->source->width
                    ];
                    */

                    $metadata->target = (object)[
                        'width' => $metadata->target->height,
                        'height' => $metadata->target->width
                    ];
                }
            } else {
                $this->logger->warning('Cannot read exif data for image with key: '.$file->key.' creating thumbnail: '.$url.' this might be totally okay, because no rotation should be made');
            }

            $entity->setMediaMetadata($cacheKey, $metadata);
        }

        $thumbnailMeta->width = $metadata->target->width;
        $thumbnailMeta->height = $metadata->target->height;
        $thumbnailMeta->isPortrait = $isPortrait = ($metadata->target->height > $metadata->target->width);
        $thumbnailMeta->isLandscape = $metadata->target->width > $metadata->target->height;
        $thumbnailMeta->orientation = $isPortrait ? 'portrait' : 'landscape'; // square === landscape

        $file->width = $metadata->source->width;
        $file->height = $metadata->source->height;
        $file->isPortrait = $isPortrait = ($metadata->source->height > $metadata->source->width);
        $file->isLandscape = $metadata->source->width > $metadata->target->height;
        $file->orientation = $isPortrait ? 'portrait' : 'landscape'; // square === landscape
    }

    /**
     * @param Manager $manager
     */
    public function setManager(Manager $manager): void
    {
        $this->manager = $manager;
    }

    protected function readExifData($streamUrl, $section)
    {
        if ($section !== 'IFD0') {
            throw new \InvalidArgumentException('I cannot do something else');
        }

        //$exif = @exif_read_data($streamUrl, 'IFD0');
        $data = new PelDataWindow(file_get_contents($streamUrl));

        if (PelJpeg::isValid($data)) {
            $img = new PelJpeg();
            $img->load($data);

            $app1 = $img->getExif();
            if ($app1 == null) {
                return false;
            }

            $tiff = $app1->getTiff();
        } elseif (PelTiff::isValid($data)) {
            $tiff = new PelTiff($data);
        } else {
            return false;
        }

        $ifd0 = $tiff->getIfd();
        if ($entry = $ifd0->getEntry(PelTag::ORIENTATION)) {
            return ['Orientation'=>$entry->getValue()];
        }
    }
}
