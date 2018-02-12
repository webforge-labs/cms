<?php

namespace Webforge\Symfony\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class LoadFixturesCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('testing:load-alice-fixtures')
      ->setDescription('Inserts all alice fixtures into the db.')
      ->addOption('purge', null, InputOption::VALUE_NONE, 'Purge the db before inserting.')
      ->addArgument('files', InputArgument::IS_ARRAY, 'List of files to import.')
      ->addOption('manager', 'm', InputOption::VALUE_OPTIONAL, 'The fixture manager name to used.', 'default');
    ;
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $objectManager = $this->getContainer()->get(sprintf('doctrine.orm.%s_entity_manager', $input->getOption('manager')));
    $aliceManager = $this->getContainer()->get(sprintf('webforge_symfony_alice_manager'));

    $aliceManager->loadFixtures($input->getArgument('files'), $objectManager, $output, $input->getOption('purge'));
  }
}
