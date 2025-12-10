<?php
namespace App\Entity;

use App\Repository\UserLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserLogRepository::class)]
#[ORM\Table(name: 'user_log')]
#[ORM\Index(name: 'idx_user_created', columns: ['user_id', 'created_at'])]
#[ORM\Index(name: 'idx_action', columns: ['action'])]
class UserLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    private ?string $action = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(length : 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $details = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $userAgent = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): static
    {
        $this->details = $details;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getActionLabel(): string
    {
        return match ($this->action) {
            'login'           => 'Connexion',
            'logout'          => 'Déconnexion',
            'register'        => 'Inscription',
            'password_change' => 'Changement de mot de passe',
            'profile_update'  => 'Mise à jour du profil',
            'user_create'     => 'Création d\'utilisateur',
            'user_update'     => 'Modification d\'utilisateur',
            'user_delete'     => 'Suppression d\'utilisateur',
            'user_suspend'    => 'Suspension d\'utilisateur',
            'user_unsuspend'  => 'Réactivation d\'utilisateur',
            default           => ucfirst($this->action),
        };
    }

    public function getActionIcon(): string
    {
        return match ($this->action) {
            'login'           => '🔓',
            'logout'          => '🔒',
            'register'        => '✨',
            'password_change' => '🔑',
            'profile_update'  => '✏️',
            'user_create'     => '➕',
            'user_update'     => '📝',
            'user_delete'     => '🗑️',
            'user_suspend'    => '🚫',
            'user_unsuspend'  => '✅',
            default           => '📋',
        };
    }
}
