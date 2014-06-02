<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth\Doctrine\Repository;

use Doctrine\ORM\NoResultException;
use Thorr\OAuth\Entity\ThirdPartyUserInterface;
use Thorr\OAuth\Entity\UserInterface;
use Thorr\OAuth\Repository\UserRepositoryInterface;
use Thorr\Persistence\Doctrine\Repository\EntityRepository;

class UserRepository extends EntityRepository implements UserRepositoryInterface
{
    /**
     * @param ThirdPartyUserInterface $thirdPartyUser
     * @return UserInterface|null
     */
    public function findUserByThirdParty(ThirdPartyUserInterface $thirdPartyUser)
    {
        $qb = $this->createQueryBuilder('user');
        $qb
            ->addSelect('thirdPartyUser')
            ->leftJoin('user.thirdPartyUsers', 'thirdPartyUser')
            ->where(
                $qb->expr()->eq(
                    'thirdPartyUser.id',
                    $qb->expr()->literal($thirdPartyUser->getId())
                )
            )
            ->andWhere(
                $qb->expr()->eq(
                    'thirdPartyUser.provider',
                    $qb->expr()->literal($thirdPartyUser->getProvider())
                )
            )
        ;

        try {
            $result = $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            $result = null;
        }

        return $result;
    }
}
