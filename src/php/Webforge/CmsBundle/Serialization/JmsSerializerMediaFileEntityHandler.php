<?php

namespace Webforge\CmsBundle\Serialization;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Context;
use Webforge\CmsBundle\Model\MediaFileInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webforge\CmsBundle\Media\Manager as MediaManager;
use Webforge\CmsBundle\Model\MediaFileEntityInterface;

class JmsSerializerMediaFileEntityHandler
{

    private $manager;

    public function __construct(MediaManager $manager)
    {
        $this->manager = $manager;
    }

    public function serialize(
        JsonSerializationVisitor $visitor,
        MediaFileEntityInterface $binary,
        array $type,
        Context $context
    ) {
        // @TODO i want to export all serialized properties here, what should i use the visitor? the context?
        // $serialized = $visitor->getNavigator()->accept($binary, $type, $context);

        $file = new \stdClass;
        $file->id = $binary->getId();

        try {
            $this->manager->serializeEntity($binary, $file);

            $file->isExisting = true;

        } catch (\Webforge\CmsBundle\Media\FileNotFoundException $e) {
            $file->isExisting = false;
        }

        // we return an array here, because otherwise @inline in serializer will not work
        return (array)$file;
    }

    public function onPostSerialize()
    {
        // this is flushing way to often, maybe we can simplify that
        $this->manager->afterSerialization();
    }
}
