<?php

namespace Webforge\CmsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Webforge\CmsBundle\Media\Manager;

class WarmupMediaFileMetaCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('cms:warmup-media-file')
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

        /** @var Manager $manager */
        $manager = $this->getContainer()->get('webforge.media.manager');

        $file = new \stdClass;
        $manager->serializeFile($mediaKey, $file);
        $manager->afterSerialization();

        return 0;
    }
}
