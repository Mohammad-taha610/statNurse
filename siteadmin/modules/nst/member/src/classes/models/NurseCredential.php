<?php


namespace nst\member;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use sacore\application\DateTime;
use sa\files\saFile;
use sa\member\saMember;
use sa\system\saCity;
use sa\system\saState;

/**
 * @Entity(repositoryClass="NurseCredentialRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @Table="NurseCredentials"
 */
class NurseCredential {
    /**
     * @var int $id
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;

    /**
     * Many NurseCredentials have Many Providers.
     * @ManyToMany(targetEntity="Provider", mappedBy="nurseCredentials")
     */
    private $providers;

    /**
     * @var string $name
     * @Column(type="string", nullable=true)
     */
    private $name;

    public function __construct() {
        $this->providers = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @param string $name
     * @return NurseCredential
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * @param Provider $provider
     * @return NurseCredential
     */
    public function addProvider($provider)
    {
        $this->providers->add($provider);
        return $this;
    }

    /**
     * @param Provider $provider
     * @return NurseCredential
     */
    public function removeProvider($provider)
    {
        $this->providers->removeElement($provider);
        return $this;
    }

    /**
     * @return NurseCredential
     */
    public function clearProviders()
    {
        $this->providers->clear();
        return $this;
    }
}
