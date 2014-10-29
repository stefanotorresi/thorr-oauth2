<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Generator;

use Zend\Math\Rand;

class IdGenerator implements IdGeneratorInterface
{
    /**
     * @var int
     */
    protected $length = 32;

    /**
     * @param $length
     */
    public function __construct($length = null)
    {
        if ($length) {
            $this->setLength($length);
        }
    }

    /**
     * @return string
     */
    public function generate()
    {
        return md5(Rand::getString($this->length));
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int $length
     */
    public function setLength($length)
    {
        $this->length = (int) $length;
    }
}
