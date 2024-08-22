<?php
namespace nst\events;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\OneToMany;
use sa\events\Category;
use \Doctrine\Common\Collections\ArrayCollection;

/**
 * @package nst\events
 * @Entity(repositoryClass="NstCategoryRepository")
 * @HasLifecycleCallbacks
 * @IOC_Name="EventsCategory"
 */
class NstCategory extends \sa\events\Category
{
    /**
     * One category has many presetShiftTimes. This is the inverse side.
     * @OneToMany(targetEntity="nst\events\PresetShiftTime", mappedBy="category")
     */
    protected $presetShiftTimes;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->presetShiftTimes = new ArrayCollection();
    }
    
    /**
     * @return ArrayCollection
     */
    public function getPresetShiftTimes()
    {
        return $this->presetShiftTimes;
    }

    /**
     * @param PresetShiftTime $presetShiftTime
     * @return NstCategory
     */
    public function addPresetShiftTime($presetShiftTime)
    {
        $this->presetShiftTimes->add($presetShiftTime);
        return $this;
    }

    /**
     * @param PresetShiftTime $presetShiftTime
     * @return NstCategory
     */
    public function removePresetShiftTime($presetShiftTime)
    {
        $this->presetShiftTimes->removeElement($presetShiftTime);
        return $this;
    }

    /**
     * @return NstCategory
     */
    public function clearPresetShiftTimes()
    {
        $this->presetShiftTimes->clear();
        return $this;
    }
}
