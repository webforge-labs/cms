<?php

namespace Webforge\CmsBundle\Media;

class Directory extends Item
{
    protected $items;

    public $parent;

    public $path;

    private $itemsList;

    public function __construct($name, $path, Directory $parent = null, $type = 'directory')
    {
        if ($parent === null && $type != 'ROOT') {
            throw new \LogicException('only root directory shouldnt have a parent');
        }
        $this->path = $path;
        $this->parent = $parent;
        $this->items = [];
        $this->itemsList = [];
        parent::__construct($name, $type);
    }

    public function addItem(Item $item)
    {
        $this->itemsList[$item->name] = $item;
        $this->items[] = $item;
    }

    public function hasItem(Item $item)
    {
        return array_key_exists($item->name, $this->itemsList);
    }

    public function getItems()
    {
        return $this->items;
    }

    public function export(array $options)
    {
        $export = (object)[
            'name' => $this->name,
            'type' => $this->type,
            'key' => ltrim($this->path, '/'),
            'items' => array_map(function ($item) use ($options) {
                return $item->export($options);
            }, $this->items)
        ];

        return $export;
    }
}
