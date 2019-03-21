<?php

namespace Webforge\CmsBundle\Content;

use Psr\Log\LoggerInterface;
use Webforge\CmsBundle\Media\Manager;

class CommonBlockExtender implements BlockExtender
{
    protected $markdowner;
    /**
     * @var Manager
     */
    private $mediaManager;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct($markdowner, Manager $mediaManager, LoggerInterface $logger)
    {
        $this->markdowner = $markdowner;
        $this->mediaManager = $mediaManager;
        $this->logger = $logger;
    }

    public function extend(array &$blocks, \stdClass $context)
    {
        $first = true;
        foreach ($blocks as $block) {
            $blockType = $context->config->getBlockType($block);

            foreach ($blockType->getProperties() as $property) {
                $value = isset($block->{$property->name}) ? $block->{$property->name} : null;

                if ($property->hasMarkdown) {
                    $block->{$property->name.'Html'} = $this->markdowner->transformMarkdown($value);
                }

                if ($property->hasText) {
                    $block->{$property->name.'Text'} = $this->markdowner->transformText($value);
                }

                if ($property->hasFiles) {
                    // create a fresh serialized version of the file only by media key
                    $refreshedFiles = array();
                    foreach ($value as $fileSpec) {
                        // we will overwrite a lot from $fileSpec here, regenerating thumbnail-informations, etc
                        try {
                            $this->mediaManager->serializeFile($fileSpec->key, $fileSpec);
                            $refreshedFiles[] = $fileSpec;
                        } catch (\Webforge\CmsBundle\Media\MediaEntityNotFoundException $e) {
                            $this->logger->warning('Entity with key: '.$fileSpec->key.' was not found. Referenced in content block in property: '.$property->name.'. Will remove this reference.');
                        }
                    }
                    // replace with fresh serialized
                    $block->{$property->name} = $refreshedFiles;
                }
            }
        }
    }
}
