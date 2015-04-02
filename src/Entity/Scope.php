<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Entity;

use Thorr\Persistence\Entity\AbstractEntity;

class Scope extends AbstractEntity
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $defaultScope = false;

    /**
     * {@inheritdoc}
     *
     * @param string $name
     * @param bool   $default
     */
    public function __construct($uuid = null, $name = null, $default = null)
    {
        parent::__construct($uuid);

        if ($name) {
            $this->setName($name);
        }

        if ($default) {
            $this->setDefaultScope($default);
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
     * @return bool
     */
    public function isDefaultScope()
    {
        return $this->defaultScope;
    }

    /**
     * @param bool $default
     */
    public function setDefaultScope($default)
    {
        $this->defaultScope = (bool) $default;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}
