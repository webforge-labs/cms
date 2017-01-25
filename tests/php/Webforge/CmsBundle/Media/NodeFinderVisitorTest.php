<?php

namespace Webforge\CmsBundle\Media;

use Tree\Node\Node;

class NodeFinderVisitorTest extends \PHPUnit_Framework_TestCase {

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
    $this->tree->addNode('/minis', new Node('mini-single'));

    $node = $this->find('/minis/mini-single');
    $this->assertFoundNode('mini-single', $node);
  }

  public function testTreeCanAddNodesToRoot() {
    $this->tree->addNode('/', new Node('test'));
    $this->assertNotEmpty($this->find('/test'));
  }

  public function testTreeWontOverwriteExisting() {
    $this->setExpectedException('LogicException', 'travel/usa/california');
    $this->tree->addNode('/travel/usa', new Node('california'));
  }

  public function testTreeCanAddDeepNodesThatAreNotExising() {
    $this->tree->addNode('/travel/usa/chicago', new Node('westside'));

    $this->assertNotEmpty($this->find('/travel/usa/chicago/westside'));
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
