<?php

namespace Webforge\Doctrine\Test\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * A basic user of the blog
 *
 * this entity was compiled from Webforge\Doctrine\Compiler
 * @ORM\Entity
 * @ORM\Table(name="users", uniqueConstraints={@ORM\UniqueConstraint(name="user_email_unique", columns={"email"})})
 *
 */
class User extends CompiledUser
{
}
