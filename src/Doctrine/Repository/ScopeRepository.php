<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Doctrine\Repository;

use Thorr\OAuth\Repository\ScopeRepositoryInterface;
use Thorr\Persistence\Doctrine\Repository\EntityRepository;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\Guard\ArrayOrTraversableGuardTrait;

class ScopeRepository extends EntityRepository implements ScopeRepositoryInterface
{
    use ArrayOrTraversableGuardTrait;

    /**
     * @param $scopes
     * @return array|Traversable
     */
    public function findScopes($scopes)
    {
        $this->guardForArrayOrTraversable($scopes);

        if (! is_array($scopes)) {
            $scopes = ArrayUtils::iteratorToArray($scopes);
        }

        $queryBuilder = $this->createQueryBuilder('scope');
        $queryBuilder->where($queryBuilder->expr()->in('scope.name', $scopes));

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @return array
     */
    public function findDefaultScopes()
    {
        return $this->findBy([ 'default' => true ]);
    }
}
