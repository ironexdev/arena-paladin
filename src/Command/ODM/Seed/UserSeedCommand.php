<?php declare(strict_types=1);

namespace Paladin\Command\ODM\Seed;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Paladin\Model\DocumentFactory\User\UserFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Paladin\Model\Document\User;
use Paladin\Service\Security\SecurityServiceInterface;

class UserSeedCommand extends Command
{
    protected static $defaultName = "odm:seed:user";

    /**
     * @param DocumentManager $documentManager
     * @param SecurityServiceInterface $securityService
     * @param UserFactoryInterface $userFactory
     */
    public function __construct(
        private DocumentManager          $documentManager,
        private SecurityServiceInterface $securityService,
        private UserFactoryInterface     $userFactory
    )
    {
        parent::__construct(static::getDefaultName());
    }

    protected function configure(): void
    {
        $this->setDescription("User seed.");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws MongoDBException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = $this->userFactory->create(
            "First Name",
            "Last Name",
            "Nickname",
            "name@domain.com",
            $this->securityService->passwordHash("password123456"),
            true
        );

        $this->documentManager->persist($user);

        try {
            $this->documentManager->flush();
        } catch (MongoDBException $e) {
            throw $e;
        }

        $output->writeln("User seed finished.");

        return Command::SUCCESS;
    }
}
