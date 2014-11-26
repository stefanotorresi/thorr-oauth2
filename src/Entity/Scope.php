<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Entity;

use Rhumsaa\Uuid\Uuid;
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
     * {@inheritdoc}
     * @param string $name
     * @param $default
     */
    public function __construct(Uuid $uuid = null, $name = null, $default = null)
    {
        parent::__construct($uuid);

        if ($name) {
            $this->setName($name);
        }

        if ($default) {
            $this->setDefault($default);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = (string) $name;
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
     */
    public function setDefault($default)
    {
        $this->default = (bool) $default;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}
