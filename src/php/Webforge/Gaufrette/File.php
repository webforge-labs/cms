<?php

namespace Webforge\Gaufrette;

class File extends Item {

  public $directory;
  public $key;

  public function __construct($name, Directory $directory) {
    $this->directory = $directory;
    parent::__construct($name, 'file');
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
      'key'=>$this->key
    ];
  }
}
