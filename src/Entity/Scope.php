<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Entity;

use Thorr\Persistence\Entity\AbstractEntity;

class Scope extends AbstractEntity
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var boolean
     */
    protected $default = false;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * @param boolean $default
     * @return self
     */
    public function setDefault($default)
    {
        $this->default = (bool) $default;

        return $this;
    }
}
