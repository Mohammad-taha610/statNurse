<?php

namespace nst\events;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use nst\member\Provider;
use nst\events\NstCategory;

/**
 * @Entity(repositoryClass="PresetShiftTimeRepository")
 * @HasLifecycleCallbacks
 * @IOC_NAME="PresetShiftTime"
 */
class PresetShiftTime
{
    /**
     * @var int $id
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;
    /**
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $start_time;

    /**
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $end_time;

    /**
     * Many PresetShiftTimes have one category. This is the owning side.
     * @ManyToOne(targetEntity="nst\events\NstCategory", inversedBy="presetShiftTimes")
     * @JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected NstCategory|null $category = null;

    /**
     * Many PresetShiftTimes have one Provider.
     * @ManyToOne(targetEntity="nst\member\Provider", inversedBy="presetShiftTimes")
     * @JoinColumn(name="provider_id", referencedColumnName="id")
     */
    private Provider|null $provider = null;

    /**
     * @var string
     * @Column(type="string", nullable=false)
     */
    private $human_readable;

    public function __construct() {
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set startTime
     *
     * @param string $startTime
     *
     * @return PresetShiftTime
     */
    public function setStartTime($startTime)
    {
        $this->start_time = $startTime;

        return $this;
    }

    /**
     * Get startTime
     *
     * @return string
     */
    public function getStartTime()
    {
        return $this->start_time;
    }

    /**
     * Set endTime
     *
     * @param string $endTime
     *
     * @return PresetShiftTime
     */
    public function setEndTime($endTime)
    {
        $this->end_time = $endTime;

        return $this;
    }

    /**
     * Get endTime
     *
     * @return string
     */
    public function getEndTime()
    {
        return $this->end_time;
    }

    /**
     * @return NstCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param NstCategory $category
     * @return PresetShiftTime
     */
    public function setCategory($category = null)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return Provider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param Provider $provider
     * @return PresetShiftTime
     */
    public function setProvider($provider = null)
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * Set human_readable
     *
     * @param string $human_readable
     *
     * @return PresetShiftTime
     */
    public function setHumanReadable($human_readable)
    {
        $this->human_readable = $human_readable;

        return $this;
    }

    /**
     * Get human_readable
     *
     * @return string
     */
    public function getHumanReadable()
    {
        return $this->human_readable;
    }

    // /**
    //  * @param Provider $provider
    //  * @return PresetShiftTime
    //  */
    // public function addProvider($provider)
    // {
    //     $this->providers->add($provider);
    //     return $this;
    // }

    // /**
    //  * @param Provider $provider
    //  * @return PresetShiftTime
    //  */
    // public function removeProvider($provider)
    // {
    //     $this->providers->removeElement($provider);
    //     return $this;
    // }

    // /**
    //  * @return PresetShiftTime
    //  */
    // public function clearProviders()
    // {
    //     $this->providers->clear();
    //     return $this;
    // }
}