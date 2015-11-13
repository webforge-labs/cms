<?php

require __DIR__.'/../../../bootstrap.php';

$example = new \Webforge\TestData\NestedSet\Hgdrn();

$array = array();
$yamlKeys = array();
$d = 1;
foreach ($example->toArray() as $node) {
  $node['slug'] = \URLify::filter($node['title']);
  $node['created'] = "<webforgeDateTimeBetween('-10 hours')>";
  $yamlKeys[$node['title']] = sprintf('hgdrn_node_%d', $d++);
  $array[ $yamlKeys[$node['title']] ] = $node;
}

foreach ($example->toParentPointerArray() as $node) {
  if ($node['parent']) {
    $array[ $yamlKeys[$node['title']] ]['parent'] = '@'.$yamlKeys[$node['parent']];
  }
}


use Symfony\Component\Yaml\Dumper;


$dumper = new Dumper();
$dumper->setIndentation(2);

$yaml = $dumper->dump(array('Webforge\CmsBundle\Entity\NavigationNode'=>$array), 3);

file_put_contents(__DIR__.'/nestedset.hgdrn.yml', $yaml);


