<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * A single admin account, defined entirely via environment variables
 * (ADMIN_USERNAME / ADMIN_PASSWORD_HASH in .env.local) — no user table needed.
 */
class EnvAdminUserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly string $adminUsername,
        private readonly string $adminPasswordHash,
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        if (!hash_equals($this->adminUsername, $identifier)) {
            throw new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
        }

        return new InMemoryUser($this->adminUsername, $this->adminPasswordHash, ['ROLE_ADMIN']);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof InMemoryUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return InMemoryUser::class === $class;
    }
}
