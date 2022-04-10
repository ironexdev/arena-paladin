<?php declare(strict_types=1);

namespace Paladin\Model\Repository\AuthorizationToken;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Paladin\Model\Document\AuthorizationToken;

class AuthorizationTokenRepository extends DocumentRepository implements AuthorizationTokenRepositoryInterface
{
    public function persist(AuthorizationToken $authorizationToken): AuthorizationToken
    {
        $this->dm->persist($authorizationToken);

        return $authorizationToken;
    }

    public function delete(AuthorizationToken $authorizationToken)
    {
        $this->dm->remove($authorizationToken);
    }
}
