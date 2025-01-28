<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Admin;

use Romanzaycev\Fundamenta\Components\Auth\User;

class AdminUser implements User
{
    public function __construct(
        protected string $id,
        protected string $login,
        protected string $name,
        protected string $passwordHash,
        protected bool $isActive,
        protected ?\DateTimeInterface $lastLogin,
        protected ?string $lastUa,
        protected ?string $lastIp,
        protected ?string $totpSecret,
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function getLastUa(): ?string
    {
        return $this->lastUa;
    }

    public function getLastIp(): ?string
    {
        return $this->lastIp;
    }

    public function getTotpSecret(): ?string
    {
        return $this->totpSecret;
    }

    public function setLogin(string $login): void
    {
        $this->login = $login;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): void
    {
        $this->lastLogin = $lastLogin;
    }

    public function setLastUa(?string $lastUa): void
    {
        $this->lastUa = $lastUa;
    }

    public function setLastIp(?string $lastIp): void
    {
        $this->lastIp = $lastIp;
    }

    public function setTotpSecret(?string $totpSecret): void
    {
        $this->totpSecret = $totpSecret;
    }
}
