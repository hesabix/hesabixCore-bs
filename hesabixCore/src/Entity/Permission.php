<?php

namespace App\Entity;

use App\Repository\PermissionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
class Permission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'permissions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'permissions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Business $bid = null;

    #[ORM\Column(nullable: true)]
    private ?bool $owner = null;

    #[ORM\Column(nullable: true)]
    private ?bool $settings = null;

    #[ORM\Column(nullable: true)]
    private ?bool $person = null;

    #[ORM\Column(nullable: true)]
    private ?bool $commodity = null;

    #[ORM\Column(nullable: true)]
    private ?bool $getpay = null;

    #[ORM\Column(nullable: true)]
    private ?bool $banks = null;

    #[ORM\Column(nullable: true)]
    private ?bool $bankTransfer = null;

    #[ORM\Column(nullable: true)]
    private ?bool $buy = null;

    #[ORM\Column(nullable: true)]
    private ?bool $sell = null;

    #[ORM\Column(nullable: true)]
    private ?bool $cost = null;

    #[ORM\Column(nullable: true)]
    private ?bool $income = null;

    #[ORM\Column(nullable: true)]
    private ?bool $accounting = null;

    #[ORM\Column(nullable: true)]
    private ?bool $report = null;

    #[ORM\Column(nullable: true)]
    private ?bool $log = null;

    #[ORM\Column(nullable: true)]
    private ?bool $permission = null;

    #[ORM\Column(nullable: true)]
    private ?bool $salary = null;

    #[ORM\Column(nullable: true)]
    private ?bool $cashdesk = null;

    #[ORM\Column(nullable: true)]
    private ?bool $plugNoghreAdmin = null;

    #[ORM\Column(nullable: true)]
    private ?bool $plugNoghreSell = null;

    #[ORM\Column(nullable: true)]
    private ?bool $plugCCAdmin = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getBid(): ?Business
    {
        return $this->bid;
    }

    public function setBid(?Business $bid): self
    {
        $this->bid = $bid;

        return $this;
    }

    public function isOwner(): ?bool
    {
        return $this->owner;
    }

    public function setOwner(?bool $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function isSettings(): ?bool
    {
        return $this->settings;
    }

    public function setSettings(?bool $settings): self
    {
        $this->settings = $settings;

        return $this;
    }

    public function isPerson(): ?bool
    {
        return $this->person;
    }

    public function setPerson(?bool $person): self
    {
        $this->person = $person;

        return $this;
    }

    public function isCommodity(): ?bool
    {
        return $this->commodity;
    }

    public function setCommodity(?bool $commodity): self
    {
        $this->commodity = $commodity;

        return $this;
    }

    public function isGetpay(): ?bool
    {
        return $this->getpay;
    }

    public function setGetpay(?bool $getpay): self
    {
        $this->getpay = $getpay;

        return $this;
    }

    public function isBanks(): ?bool
    {
        return $this->banks;
    }

    public function setBanks(?bool $banks): self
    {
        $this->banks = $banks;

        return $this;
    }

    public function isBankTransfer(): ?bool
    {
        return $this->bankTransfer;
    }

    public function setBankTransfer(?bool $bankTransfer): self
    {
        $this->bankTransfer = $bankTransfer;

        return $this;
    }

    public function isBuy(): ?bool
    {
        return $this->buy;
    }

    public function setBuy(?bool $buy): self
    {
        $this->buy = $buy;

        return $this;
    }

    public function isSell(): ?bool
    {
        return $this->sell;
    }

    public function setSell(?bool $sell): self
    {
        $this->sell = $sell;

        return $this;
    }

    public function isCost(): ?bool
    {
        return $this->cost;
    }

    public function setCost(?bool $cost): self
    {
        $this->cost = $cost;

        return $this;
    }

    public function isIncome(): ?bool
    {
        return $this->income;
    }

    public function setIncome(?bool $income): self
    {
        $this->income = $income;

        return $this;
    }

    public function isAccounting(): ?bool
    {
        return $this->accounting;
    }

    public function setAccounting(?bool $accounting): self
    {
        $this->accounting = $accounting;

        return $this;
    }

    public function isReport(): ?bool
    {
        return $this->report;
    }

    public function setReport(?bool $report): self
    {
        $this->report = $report;

        return $this;
    }

    public function isLog(): ?bool
    {
        return $this->log;
    }

    public function setLog(?bool $log): self
    {
        $this->log = $log;

        return $this;
    }

    public function isPermission(): ?bool
    {
        return $this->permission;
    }

    public function setPermission(?bool $permission): self
    {
        $this->permission = $permission;

        return $this;
    }

    public function isSalary(): ?bool
    {
        return $this->salary;
    }

    public function setSalary(?bool $salary): self
    {
        $this->salary = $salary;

        return $this;
    }

    public function isCashdesk(): ?bool
    {
        return $this->cashdesk;
    }

    public function setCashdesk(?bool $cashdesk): self
    {
        $this->cashdesk = $cashdesk;

        return $this;
    }

    public function isPlugNoghreAdmin(): ?bool
    {
        return $this->plugNoghreAdmin;
    }

    public function setPlugNoghreAdmin(?bool $plugNoghreAdmin): static
    {
        $this->plugNoghreAdmin = $plugNoghreAdmin;

        return $this;
    }

    public function isPlugNoghreSell(): ?bool
    {
        return $this->plugNoghreSell;
    }

    public function setPlugNoghreSell(?bool $plugNoghreSell): static
    {
        $this->plugNoghreSell = $plugNoghreSell;

        return $this;
    }

    public function isPlugCCAdmin(): ?bool
    {
        return $this->plugCCAdmin;
    }

    public function setPlugCCAdmin(?bool $plugCCAdmin): static
    {
        $this->plugCCAdmin = $plugCCAdmin;

        return $this;
    }
}