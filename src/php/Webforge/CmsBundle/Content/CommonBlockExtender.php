<?php

namespace Webforge\CmsBundle\Content;

class CommonBlockExtender implements BlockExtender
{
    protected $markdowner;
    protected $mediaManager;

    public function __construct($markdowner, $mediaManager)
    {
        $this->markdowner = $markdowner;
        $this->mediaManager = $mediaManager;
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
                        $this->mediaManager->serializeFile($fileSpec->key, $fileSpec);
                        $refreshedFiles[] = $fileSpec;
                    }
                    // replace with fresh serialized
                    $block->{$property->name} = $refreshedFiles;
                }
            }
        }
    }
}
