<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Test\Asset;

use Thorr\OAuth2\Entity\ScopesProviderInterface;
use Thorr\OAuth2\Entity\ScopesProviderTrait;
use Thorr\OAuth2\Entity\User;

class ScopeAwareUser extends User implements ScopesProviderInterface
{
    use ScopesProviderTrait;
}
