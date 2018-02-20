<?php

namespace Webforge\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Webforge\Doctrine\Container as DoctrineContainer;

class Util {

  const FORCE = '__force';

  /**
   * Webforge\Doctrine\Container
   */
  private $dcc;

  public function __construct(DoctrineContainer $dcc) {
    $this->dcc = $dcc;
  }

  /**
   * @param string $con
   */
  public function updateSchema($con, $force = NULL, $eol = "<br />") {
    if (extension_loaded('apc')) {
      apc_clear_cache();
    }

    $em = $this->dcc->getEntityManager($con);
    $tool = $this->dcc->getSchemaTool($con);
    $classes = $em->getMetadataFactory()->getAllMetadata();
    
    $log = NULL;
    foreach ($tool->getUpdateSchemaSql($classes, TRUE) as $sql) {
      $log .= $sql.';'.$eol;
    }
    
    if ($force === self::FORCE) {
      $tool->updateSchema($classes, TRUE);
    }

    return $log;
  }
}
