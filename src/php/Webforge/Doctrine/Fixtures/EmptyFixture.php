<?php

namespace Webforge\Doctrine\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class EmptyFixture extends AbstractFixture
{
    public function load(ObjectManager $em)
    {
    }
}
