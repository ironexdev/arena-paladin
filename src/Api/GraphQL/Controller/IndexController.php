<?php declare(strict_types=1);

namespace Paladin\Api\GraphQL\Controller;

use TheCodingMachine\GraphQLite\Annotations\Query;

class IndexController extends AbstractController
{
    /**
     * @param string $name
     * @return string
     */
    #[Query]
    public function hello(string $name): string
    {
        return $this->request::class;
    }
}