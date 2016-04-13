<?php

namespace Webforge\Gaufrette;

class Directory extends Item {

  protected $items;

  public $parent;

  private $itemsList;

  public function __construct($name, Directory $parent = NULL, $type = 'directory') {
    if ($parent === NULL && $type != 'ROOT') {
      throw new \LogicException('only root directory shouldnt have a parent');
    }
    $this->parent = $parent;
    $this->items = [];
    $this->itemsList = [];
    parent::__construct($name, $type);
  }

  public function addItem(Item $item) {
    $this->itemsList[$item->name] = $item;
    $this->items[] = $item;
  }

  public function hasItem(Item $item) {
    return array_key_exists($item->name, $this->itemsList);
  }

  public function getItems() {
    return $this->items;
  }

  public function export() {
    return (object) [
      'name'=>$this->name,
      'type'=>$this->type,
      'items'=>array_map(function ($item) {
        return $item->export();
      }, $this->items)
    ];
  }
}
