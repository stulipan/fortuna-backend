<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class FakeApiUser implements UserInterface
{
    private $username;

    public function __construct(string $username)
    {
        $this->username = $username;
    }

    public function getRoles()
    {
        return ['ROLE_API_USER']; // Adjust roles as needed
    }

    public function getPassword()
    {
        // Not needed for API token authentication
        return null;
    }

    public function getSalt()
    {
        // Not needed for API token authentication
        return null;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @deprecated since Symfony 5.3
     */
    public function getUsername()
    {
        return $this->username;
    }

    public function eraseCredentials()
    {
        // Not needed for API token authentication
    }
}
