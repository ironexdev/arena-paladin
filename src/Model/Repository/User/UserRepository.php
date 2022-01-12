<?php declare(strict_types=1);

namespace Paladin\Model\Repository\User;

use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Error;
use Paladin\Enum\ResponseStatusCodeEnum;
use Paladin\Model\Document\User;

class UserRepository extends DocumentRepository implements UserRepositoryInterface
{
    /**
     * @param User $user
     * @return bool
     */
    public function isUnique(User $user): bool
    {
        try {
            $result = $this->dm->createQueryBuilder(User::class)
                ->field("email")
                ->equals($user->getEmail())
                ->count()
                ->getQuery()
                ->execute();
        } catch (MongoDBException $e) {
            throw new Error($e->getMessage(), ResponseStatusCodeEnum::INTERNAL_SERVER_ERROR, $e);
        }

        return !$result;
    }

    public function activate(User $user): void
    {
        try {
            $this->dm->createQueryBuilder(User::class)
                ->findAndUpdate()
                ->field("id")
                ->equals($user->getId())
                ->field("active")->set(true)
                ->getQuery()
                ->execute();
        } catch (MongoDBException $e) {
            throw new Error($e->getMessage(), ResponseStatusCodeEnum::INTERNAL_SERVER_ERROR, $e);
        }
    }
}
