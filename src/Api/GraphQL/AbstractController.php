<?php declare(strict_types=1);

namespace Paladin\Api\GraphQL;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use TheCodingMachine\GraphQLite\Validator\ValidationFailedException;

class AbstractController
{
    public function __construct(
        protected LoggerInterface $logger, protected ServerRequestInterface $request, protected ValidatorInterface $validator)
    {
    }

    protected function validateInput($input)
    {
        $errors = $this->validator->validate($input);

        ValidationFailedException::throwException($errors);
    }
}
