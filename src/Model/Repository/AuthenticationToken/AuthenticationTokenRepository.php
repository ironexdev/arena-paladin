<?php declare(strict_types=1);

namespace Paladin\Model\Repository\AuthenticationToken;

use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Error;
use Paladin\Enum\ResponseStatusCodeEnum;
use Paladin\Model\Document\AuthenticationToken;
use Paladin\Model\Document\User;

class AuthenticationTokenRepository extends DocumentRepository implements AuthenticationTokenRepositoryInterface
{
    public function deleteByUser(User $user)
    {
        try {
            $this->dm->createQueryBuilder(AuthenticationToken::class)
                ->field("user")
                ->equals($user)
                ->remove()
                ->getQuery()
                ->execute();
        } catch (MongoDBException $e) {
            throw new Error($e->getMessage(), ResponseStatusCodeEnum::INTERNAL_SERVER_ERROR, $e);
        }
    }

    public function persist(AuthenticationToken $authenticationToken): AuthenticationToken
    {
        $this->dm->persist($authenticationToken);

        return $authenticationToken;
    }
}
