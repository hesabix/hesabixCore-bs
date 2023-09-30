<?php

namespace App\Entity;

use App\Repository\SettingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingsRepository::class)]
class Settings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $payamakUsername = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $payamakPassword = null;

    #[ORM\Column(nullable: true)]
    private ?bool $activeSendSms = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPayamakUsername(): ?string
    {
        return $this->payamakUsername;
    }

    public function setPayamakUsername(?string $payamakUsername): static
    {
        $this->payamakUsername = $payamakUsername;

        return $this;
    }

    public function getPayamakPassword(): ?string
    {
        return $this->payamakPassword;
    }

    public function setPayamakPassword(?string $payamakPassword): static
    {
        $this->payamakPassword = $payamakPassword;

        return $this;
    }

    public function isActiveSendSms(): ?bool
    {
        return $this->activeSendSms;
    }

    public function setActiveSendSms(?bool $activeSendSms): static
    {
        $this->activeSendSms = $activeSendSms;

        return $this;
    }
}