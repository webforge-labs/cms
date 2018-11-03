<?php

namespace Webforge\CmsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Finder\Finder;
use Webforge\CmsBundle\Media\Manager;

class WarmupMediaFileMetaCommand extends ContainerAwareCommand
{

    /**
     * @var Manager
     */
    private $mediaManager;

    /**
     * @var EventDispatcherInterface
     */
    private $eventManager;

    public function __construct(Manager $mediaManager, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct('cms:warmup-media-file');

        $this->mediaManager = $mediaManager;
        $this->eventManager = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Runs a serializiation for a specific file to create thumbnails and other meta.')
            ->setDefinition(array(
                new InputArgument(
                    'mediaKey',
                    InputArgument::REQUIRED,
                    'unique media key of the entity'
                )
            ));
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mediaKey = $input->getArgument('mediaKey');

        list ($binary) = $this->mediaManager->findFiles([$mediaKey]);

        $file = new \stdClass;
        $this->mediaManager->serializeEntity($binary, $file);

        $event = new GenericEvent($binary);

        $this->eventManager->dispatch(Manager::EVENT_FILE_WARMUP, $event);

        $this->mediaManager->afterSerialization();

        return 0;
    }
}
