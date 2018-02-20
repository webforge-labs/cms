<?php

namespace Webforge\Doctrine\Test;

use Webforge\Common\ArrayUtil AS A;
use Doctrine\DBAL\Logging\EchoSQLLogger;
use Webforge\Doctrine\Fixtures\FixturesManager;

/**
 * Use this as a starting point for your database tests
 *
 */
abstract class DatabaseTestCase extends \Webforge\Doctrine\Test\SchemaTestCase {
  
  /**
   * Ist dies während der setUp()-Method  == true dann wird die gesetzte Fixture geladen
   * 
   * @var bool
   */
  protected static $setupDatabase = TRUE;
  
  /**
   * @var bool
   */
  protected $backupGlobals = FALSE;
  
  /**
   * @var Doctrine\DBAL\Schema\AbstractSchemaManager
   */
  protected $sm;
  
  /**
   * @var Webforge\Doctrine\Test\FixturesManager
   */
  protected $fm;
  
  /**
   *
   * Diese Methode brauchen wir trotzdem. Denn Die Klassenvariable für DoctrineDatabaseTestCase  verhält sich wie eine globale und wird nicht zwischen den
   * Test-Klassen zurückgesetzt. Diese Funktion wird immer vor jeder TestKlasse ausgeführt.
   */
  public static function setUpBeforeClass() {
    self::$setupDatabase = TRUE;
  }
  
  public function setUp() {
    parent::setUp();
    
    // dcc and em are defined from schematestcase and base
    $this->setUpFixturesManager();

    if (self::$setupDatabase) {
      $this->setUpDatabase();
      self::$setupDatabase = FALSE;
    }
  }

  protected function setUpFixturesManager() {
    $this->fm = new FixturesManager($this->em);

    foreach ($this->getFixtures() as $fixture) {
      $this->fm->add($fixture);
    }
  }

  protected function getFixtures() {
    return array();
  }

  /**
   * Setups the fixtures and the datbase
   * 
   * per default this does $this->fm->execute()
   * you can override this behaviour for your own test
   * but be aware: this function is called only ONCE in a test-suite
   * you can trigger further calls to this when you call resetDatabaseOnNextTest
   * 
   * here is a log how this would be called:
   *
   * setUp()
   * setUpDatabase()
   * firstTest()
   * tearDown()
   *
   * setUp()
   * secondTest()
   * $this->resetDatbaseOnNextTest()
   * tearDown()
   *
   * setUp()
   * setUpDatabase()
   * thirdTest()
   * tearDown()
   */
  protected function setUpDatabase() {
    $this->fm->execute();
  }
  
  /**
   * Call this to trigger setUpDatabase for the NEXT test
   */
  protected function resetDatabaseOnNextTest() {
    self::$setupDatabase = TRUE;
  }
  
  /**
   * @return ClassMetadata
   */
  public function getEntityMetadata($entityName) {
    return $this->em->getMetaDataFactory()->getMetadataFor($this->getEntityName($entityName));
  }
  
  /**
   * @return Doctrine\ORM\EntityManager
   */
  public function getEntityManagerMock() {
    return $this->mocker->createEntityManager();
  }

  /**
   * Cleanup open transactions
   */
  protected function onNotSuccessfulTest(\Exception $e) {
    if (isset($this->em) && $this->em->getConnection()->isTransactionActive()) {
      $this->em->getConnection()->rollback();
    }
    
    return parent::onNotSuccessfulTest($e);
  }
  
  
  /* HELPERS */
  /**
   * @return EntitiyRepository
   */
  public function getRepository($name) {
    return $this->em->getRepository($this->getEntityName($name));
  }

  /**
   * override this for shorter names in your tests
   */
  protected function getEntityName($shortName) {
    return $shortName;
  }

  public function getSchemaManager() {
    if (!isset($this->sm)) {
      $this->sm = $this->em->getConnection()->getSchemaManager();
    }
    
    return $this->sm;
  }
  
  /**
   * @chainable
   */
  public function startDebug() {
    $this->em->getConnection()->getConfiguration()->setSQLLogger(new EchoSQLLogger());
    return $this;
  }
  
  /**
   * @chainable
   */
  public function stopDebug() {
    $this->em->getConnection()->getConfiguration()->setSQLLogger(NULL);
    return $this;
  }
  
  /**
   * Hydrates one entity by criterias or by identifier
   * 
   * @param int|string|array an identifier or an array
   * @return object<$entity>
   */
  public function hydrate($entity, $data) {
    if (is_array($data) && !A::isNumeric($data)) { // numeric bedeutet composite key (z.b. OID)
      return $this->getRepository($entity)->hydrateBy($data);
    } else {
      return $this->getRepository($entity)->hydrate($data);
    }
  }

  /* ASSERTIONS */
  
  /**
   * Asserts that 2 collections are really equal
   */
  /*
  public function assertCollection($expected, $actual, $compareFieldGetter = 'identifier') {
    $this->assertEquals(DoctrineHelper::map($expected, $compareFieldGetter),
                        DoctrineHelper::map($actual, $compareFieldGetter),
                        'Collections sind nicht gleich'
                       );
  }
  */
}
