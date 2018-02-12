<?php

namespace Webforge\Doctrine\Fixtures;


use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Fidry\AliceDataFixtures\Loader\PurgerLoader;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Symfony\Component\Console\Output\OutputInterface;

class AliceManager {

  private $loader;

  public function __construct(PurgerLoader $loader) {
    $this->loader = $loader;
  }

  public function loadFixtures(Array $files, EntityManagerInterface $objectManager, OutputInterface $output, $purge = FALSE) {
    $this->loader->load($files, [], [], $purge ? PurgeMode::createTruncateMode() : PurgeMode::createNoPurgeMode());
    /*
    foreach ($files as $file) {
      $objects = $this->loader->load($file);
      $output->writeln('<comment>  file: '.$file.'</comment>');
      $persister->persist($objects);
    }

    $output->writeln('flushing...');
    $objectManager->flush();
    $output->writeln('<info>done.</info>');
    */
  }

  private function purge(EntityManagerInterface $objectManager, OutputInterface $output) {
    $output->writeln('purging...');

    $purger = new ORMPurger($objectManager);
    $purger->purge();

/*    $connection = $objectManager->getConnection();
    $platform = $connection->getDatabasePlatform();
    $configuration = $objectManager->getConfiguration();

    $connection->executeQuery('set foreign_key_checks = 0');

    $classes = array();
    foreach ($objectManager->getMetadataFactory()->getAllMetadata() as $class) {
      if ($this->isTabledMetadata($class)) {
        $classes[] = $class;
      }
    }

    foreach ($classes as $class) {
      foreach ($class->associationMappings as $assoc) {
        if ($assoc['isOwningSide'] && $assoc['type'] == ClassMetadata::MANY_TO_MANY) {
          $assocTable = $this->getJoinTableName($assoc, $class, $platform, $configuration);
          $connection->executeUpdate($platform->getTruncateTableSQL($assocTable, true));
        }
      }
    }

    foreach ($classes as $class) {
      $tbl = $this->getTableName($class, $platform, $configuration);
      $connection->executeUpdate($platform->getTruncateTableSQL($tbl, true));
    }

    $connection->executeQuery('set foreign_key_checks = 1');*/
  }

  /**
   * Returns true if the class is metadata for an entity that has an acutal table in the db
   * @param ClassMetadata $class
   * @return boolean
   */
  private function isTabledMetadata($class) {
    if (isset($class->isEmbeddedClass) && $class->isEmbeddedClass) {
      return FALSE;
    }

    if ($class->isMappedSuperclass) {
      return FALSE;
    }
    
    if ($class->isInheritanceTypeSingleTable() && $class->name !== $class->rootEntityName) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   *
   * @param \Doctrine\ORM\Mapping\ClassMetadata $class
   * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
   * @return string
   */
  private function getTableName($class, $platform, $configuration) {
    if (isset($class->table['schema']) && !method_exists($class, 'getSchemaName')) {
      return $class->table['schema'].'.'.$configuration->getQuoteStrategy()->getTableName($class, $platform);
    }
    return $configuration->getQuoteStrategy()->getTableName($class, $platform);
  }

 /**
   *
   * @param array            $association
   * @param \Doctrine\ORM\Mapping\ClassMetadata    $class
   * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
   * @return string
   */
  private function getJoinTableName($assoc, $class, $platform, $configuration) {
    if (isset($assoc['joinTable']['schema']) && !method_exists($class, 'getSchemaName')) {
      return $assoc['joinTable']['schema'].'.'.$configuration->getQuoteStrategy()->getJoinTableName($assoc, $class, $platform);
    }

    return $configuration->getQuoteStrategy()->getJoinTableName($assoc, $class, $platform);
  }  
}
