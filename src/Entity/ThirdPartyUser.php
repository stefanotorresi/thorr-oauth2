<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Entity;

class ThirdPartyUser implements ThirdPartyUserInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $provider;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param string $id
     * @param string $provider
     * @param array $data
     */
    public function __construct($id, $provider, $data = [])
    {
        $this->setId($id);
        $this->setProvider($provider);
        $this->setData($data);
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = (array) $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
