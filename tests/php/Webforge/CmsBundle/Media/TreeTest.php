<?php

namespace Webforge\CmsBundle\Media;

use Tree\Node\Node;
use Webforge\Common\ArrayUtil as A;

class TreeTest extends \PHPUnit_Framework_TestCase {

  public function setUp() {
    $builder = new \Tree\Builder\NodeBuilder;

    $builder
        ->value('root')
        ->leaf('minis')
        ->tree('travel')
          ->tree('usa')
            ->leaf('california')
            ->leaf('new-york')
          ->end()
          ->leaf('marocco')
        ->end()
        ->tree('usa')
          ->leaf('this-is-wrong')
          ->tree('wyoming')
            ->leaf('east')
            ->leaf('west')
          ->end()
        ->end()
    ;

    $this->root = $builder->getNode();
    $this->tree = new Tree($this->root);
  }

  public function testItFindsRoot() {
    $this->assertSame($this->root, $this->find('/'));
  }

  public function testItFindsANestedPathWithLS() {
    $node = $this->find('/travel/usa/new-york');

    $this->assertFoundNode('new-york', $node);
  }

  public function testItFindsANestedPathWithoutLS() {
    $node = $this->find('travel/usa/new-york');

    $this->assertFoundNode('new-york', $node);
    $this->assertSame($node, $this->root->getChildren()[1]->getChildren()[0]->getChildren()[1]);
  }

  public function testItFindsAnAmbiguousPath() {
    $node = $this->find('usa');
    $this->assertFoundNode('usa', $node);
    $this->assertSame($this->root->getChildren()[2], $node);
  }

  public function testItFindsARootLeaf() {
    $node = $this->find('/minis');
    $this->assertFoundNode('minis', $node);
  }

  public function testItFindsNothing() {
    $this->assertEmpty($this->find('/this-is-wrong'));
  }

  public function testItSavesTheLastFoundNodeForNotExistingpaths() {
    $visitor = new NodeFinderVisitor('/travel/usa/chicago');
    $this->assertEmpty($this->root->accept($visitor));

    $this->assertEquals('usa', $visitor->getLastNode()->getValue());
  }

  public function testLastnodeCanBeRoot() {
    $visitor = new NodeFinderVisitor('/this-is-not-existing');
    $this->assertEmpty($this->root->accept($visitor));

    $this->assertSame($this->root, $visitor->getLastNode(), 'lastNode should be set to root');
  }

  public function testTreeCanAddNodes() {
    $this->tree->addNode('/minis', 'mini-single', '394x');

    $node = $this->find('/minis/mini-single');
    $this->assertFoundNode('mini-single', $node);
  }

  public function testTreeCanAddNodesToRoot() {
    $this->tree->addNode('/', 'test', 'test-key');
    $this->assertNotEmpty($this->find('/test'));
  }

  public function testTreeWontOverwriteExisting() {
    $this->setExpectedException('LogicException', 'travel/usa/california');
    $this->tree->addNode('/travel/usa', 'california', 'california-key');
  }

  public function testTreeCanAddDeepNodesThatAreNotExising() {
    $this->tree->addNode('/travel/usa/chicago', 'westside', 'westside-key');

    $this->assertNotEmpty($this->find('/travel/usa/chicago/westside'));
  }

  /**
   * @group tree-move
   */
  public function testTreeCannotMovePartsThatAreNotExisting() {
    $this->setExpectedException('LogicException', '"/not-existing/wyoming" is not existing');
    $this->tree->moveNode('/not-existing/wyoming', '/travel/usa/');
  }

  /**
   * @group tree-move
   */
  public function testTreeCanMoveNodesWithLeaves() {
    $this->tree->moveNode('/usa/wyoming', '/travel/usa/');

    $this->assertNotEmpty($this->find('/travel/usa/wyoming/east'), 'east should be moved through wyoming');
    $this->assertNotEmpty($this->find('/travel/usa/wyoming/west'));

    $this->assertEmpty($this->find('/usa/wyoming'), 'wyoming should be (re-)moved');
  }

  /**
   * @group tree-move
   */
  public function testTreeIntegratesNodesToEqualNodes() {
    $this->tree->moveNode('/usa', '/travel/');

    $travel = $this->find('/travel');

    $this->assertNotEmpty($this->find('/travel/usa/new-york'), 'new-york should be integrated while moving');
    $this->assertNotEmpty($this->find('/travel/usa/wyoming'), 'wyoming should be integrated into travel/usa/');
    $this->assertNotEmpty($this->find('/travel/usa/wyoming/east'), 'east should integrated into travel/usa/ through integrating wyoming');
    $this->assertEquals(['usa', 'marocco'], A::pluck($travel->getChildren(), 'getValue'), 'marocco and usa should be the only child-nodes from travel');
  }

  /**
   * @group tree-move
   */
  public function testTreeComplexIntegration() {
    $builder = new \Tree\Builder\NodeBuilder;
    $builder
      ->value('root')
      ->tree('minis')
        ->tree('tapir')
          ->leaf('image1.png')
        ->end()
      ->end()
      ->tree('wrong')
        ->tree('minis')
          ->tree('tapir')
            ->leaf('image2.png')
          ->end()
        ->end()
      ->end()
    ;

    $tree = new Tree($builder->getNode());
    $tree->moveNode('/wrong/minis', '/');

    $this->assertEquals(<<<'TREE'

root
 minis
  tapir
   image1.png
   image2.png
 wrong
TREE
      ,
      $tree->dump($advanced = FALSE)
    );
  }

  public function testTreeShouldNotAllowMovingToSelf() {
    throw new Exception('Please implement me');
  }

  public function testTreeShouldNotAllowMovingChildrenToItsChildren() {
    throw new Exception('Please implement me');
  }

  protected function assertFoundNode($value, $node) {
    $this->assertNotEmpty($node, 'should have found: '.$value);
    $this->assertEquals($value, $node->getValue(), 'value of found node');
  }

  protected function find($path) {
    $visitor = new NodeFinderVisitor($path);
    return $this->root->accept($visitor);
  }
}
