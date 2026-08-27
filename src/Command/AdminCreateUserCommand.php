<?php

namespace App\Command;

use App\Entity\AdminUser;
use App\Repository\AdminUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Creates an admin account directly in the database — needed to bootstrap the
 * very first account, since there is no way to reach the admin user
 * management UI before at least one account exists.
 */
#[AsCommand(
    name: 'app:admin:create-user',
    description: 'Crée un compte administrateur',
)]
class AdminCreateUserCommand extends Command
{
    public function __construct(
        private readonly AdminUserRepository $adminUserRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Adresse e-mail du compte')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Mot de passe en clair (sera haché)')
            ->addOption('password-hash', null, InputOption::VALUE_REQUIRED, 'Hash de mot de passe déjà calculé, à réutiliser tel quel')
            ->addOption('totp-secret', null, InputOption::VALUE_REQUIRED, 'Secret TOTP existant à réutiliser tel quel')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        $password = $input->getOption('password');
        $passwordHash = $input->getOption('password-hash');

        if (($password === null) === ($passwordHash === null)) {
            $io->error('Fournissez exactement une des deux options --password ou --password-hash.');

            return Command::FAILURE;
        }

        if ($this->adminUserRepository->findOneBy(['email' => $email])) {
            $io->error(sprintf('Un compte existe déjà avec l\'e-mail "%s".', $email));

            return Command::FAILURE;
        }

        $adminUser = new AdminUser();
        $adminUser->setEmail($email);
        $adminUser->setPassword($password !== null ? $this->passwordHasher->hashPassword($adminUser, $password) : $passwordHash);
        $adminUser->setTotpSecret($input->getOption('totp-secret'));

        $this->entityManager->persist($adminUser);
        $this->entityManager->flush();

        $io->success(sprintf('Compte administrateur créé pour "%s".', $email));

        return Command::SUCCESS;
    }
}
