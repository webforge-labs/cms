<?php

namespace Webforge\Doctrine\Fixtures;

class AliceEntitiesProvider
{
    private $dc;

    public function __construct($dc)
    {
        $this->dc = $dc;
    }
  
    public function hydrate($entityName, $criterias)
    {
        return $this->dc->hydrate($entityName, $criterias);
    }
}
