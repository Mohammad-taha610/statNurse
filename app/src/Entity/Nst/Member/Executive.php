<?php

namespace App\Entity\Nst\Member;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity]
#[InheritanceType("SINGLE_TABLE")]
#[DiscriminatorColumn(name: "discriminator", type: "string")]
#[Table("Executive")]
class Executive
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: "integer")]
    private $id;

    #[OneToOne(inversedBy: "executive", targetEntity: NstMember::class)]
    protected $member;

    #[Column(length: 255, nullable: true)]
    protected ?string $name = null;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return Executive
     */
    public function setName(?string $name): Executive
    {
        $this->name = $name;
        return $this;
    }

    #[ManyToMany(targetEntity: Provider::class)]
    #[JoinTable(name: "executives_providers")]
    private $providers;

    public function __construct()
    {
        $this->providers = new ArrayCollection();
    }

    public function addProvider(Provider $provider): self
    {
        if (!$this->providers->contains($provider)) {
            $this->providers[] = $provider;
        }

        return $this;
    }

    public function removeProvider(Provider $provider): self
    {
        $this->providers->removeElement($provider);
        return $this;
    }

    public function getProviders()
    {
        return $this->providers;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getMember(): NstMember
    {
        return $this->member;
    }
}
