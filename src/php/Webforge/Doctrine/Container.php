<?php

namespace Webforge\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use InvalidArgumentException;
use LogicException;
use Webforge\Common\System\Dir;
use Doctrine\DBAL\Types\Type as DBALType;

/**
 * A Container for the full configuratino and business objects from the doctrine module
 *
 * Language:
 *
 *   $con always describes a single string that identifies the connection (for example default, or tests)
 *   a connection has a database, a user and a password and an encoding
 *   for every $con one entityManager is in the container (if requested)
 *
 *
 */
class Container
{

  /**
   * @var Doctrine\ORM\EntityManager[]
   */
    protected $entityManagers;

    /**
     * @var Webforge\Doctrine\Util
     */
    protected $util;

    /**
     * @var Doctrine\ORM\Tools\SchemaTool[]
     */
    protected $schemaTools;

    /**
     * @var Doctrine Configuration
     */
    protected $configuration;

    /**
     * @var array
     */
    protected $connectionsConfiguration;

    /**
     * @var Webforge\Common\System\Dir
     */
    protected $proxyDirectory;

    /**
     * @param array $connectionsConfiguration every array should have the con-name as key and name, password, dbname, host, charset as sub-keys
     */
    public function initDoctrine(array $connectionsConfiguration, array $entitiesPath, $isDevMode = true)
    {
        $this->connectionsConfiguration = $this->normalizeConnections($connectionsConfiguration);
    
        $entitiesPath = array_map(
      function ($dir) {
          return (string) $dir;
      },
      $entitiesPath
    );
    
        $this->configuration = Setup::createAnnotationMetadataConfiguration($entitiesPath, $isDevMode, $this->getProxyDirectory(), $this->getCache(), $useSimpleAnnotationReader = false);
        $this->registerDefaultTypes();
    }

    /**
     * Registers some common types build into Webforge\Doctrine
     *
     */
    protected function registerDefaultTypes()
    {
        $types = array(
      'WebforgeDateTime'=>'Webforge\Doctrine\Types\DateTimeType',
      'WebforgeDate'=>'Webforge\Doctrine\Types\DateType'
    );

        foreach ($types as $name => $class) {
            if (!DBALType::hasType($name)) {
                DBALType::addType($name, $class);
            }
        }

        return $this;
    }

    /**
     * @param string $con default is 'default'
     */
    public function getEntityManager($con = null)
    {
        if (!isset($con)) {
            $con = 'default';
        }

        if (!isset($this->entityManagers[$con])) {
            if (!is_array($this->connectionsConfiguration)) {
                throw new LogicException('You have to initDoctrine() before you can getEntityManager()');
            }
            $this->entityManagers[$con] = EntityManager::create($this->connectionsConfiguration[$con], $this->configuration);
        }

        return $this->entityManagers[$con];
    }

    /**
     * @return Doctrine\ORM\Tools\SchemaTool
     */
    public function getSchemaTool($con)
    {
        if (!isset($this->schemaTools[$con])) {
            return $this->schemaTools[$con] = new SchemaTool($this->getEntityManager($con));
        }

        return $this->schemaTools[$con];
    }

    /**
     * @return Webforge\Doctrine\Util
     */
    public function getUtil()
    {
        if (!isset($this->util)) {
            $this->util = new Util($this);
        }

        return $this->util;
    }

    protected function normalizeConnections(array $dbsConfiguration)
    {
        $connections = array();

        $requiredParams = array(
      'user'=>'nes',
      'password'=>null,
      'host'=>'nes',
      'dbname'=>'nes', // doctrine style name
      'driver'=>'nes',
      'charset'=>'nes'
    );

        foreach ($dbsConfiguration as $con => $connection) {
            if (is_array($connection)) {
                if (array_key_exists('database', $connection)) {
                    $connection['dbname'] = $connection['database'];
                    unset($connection['database']);
                }

                $config = $connections[$con] = array_replace(
          array(
            'host'=>'127.0.0.1',
            'driver'=>'pdo_mysql',
            'charset'=>'utf8'
          ),
          $connection
        );

                $invalid = null;
                foreach ($requiredParams as $param => $shouldBe) {
                    if (!array_key_exists($param, $config)) {
                        $invalid .= $param.' is not set in parameters array';
                    } elseif ($shouldBe === 'nes' && mb_strlen(trim($config[$param])) === 0) {
                        $invalid .= $param.' cannot be empty';
                    }
                }

                if ($invalid) {
                    throw new InvalidArgumentException("Your connection configuration for ".$con." is not complete:\n".$invalid);
                }
            } else {
                throw new InvalidArgumentException("Please provide the connectionsConfiguration array in the format array('default'=>array('name'=>'', 'password'=>'', 'dbname'=>'', ...))");
            }
        }

        if (count($connections) === 1) {
            $connections['default'] = current($connections);
        }

        if (!array_key_exists('default', $connections)) {
            throw new InvalidArgumentException('Please provide a con with name default in you connectionsConfiguration. You only have: '.implode(', ', array_keys($connections)).' defined');
        }

        return $connections;
    }

    /**
     * Where to write doctrine proxies to
     *
     * if NULL a system tmp dir will be used
     * @return Dir|NULL
     */
    public function getProxyDirectory()
    {
        return $this->proxyDirectory;
    }

    /**
     * Set where to write doctrine proxies to
     * @chainable
     */
    public function setProxyDirectory(Dir $dir)
    {
        $this->proxyDirectory = $dir;
        return $this;
    }

    /**
     * The cache used for doctrine
     *
     * by default this is set to NULL und will be decided for devMode
     * @see Doctrine/ORM/Tools/Setup which caches can be used
     */
    public function getCache()
    {
        return null;
    }

    // used in psc-cms
    public function injectEntityManager(EntityManager $em, $con = null)
    {
        if (!isset($con)) {
            $con = 'default';
        }

        $this->entityManagers[$con] = $em;
        return $this;
    }

    // used in psc-cms
    public function injectSchemaTool(SchemaTool $schemaTool, $con)
    {
        $this->schemaTools[$con] = $schemaTool;
        return $this;
    }

    public function injectUtil(Util $util)
    {
        $this->util = $util;
        return $this;
    }
}
