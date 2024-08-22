<?php

namespace App\Entity\Nst\Events;

use App\Entity\Sax\Events\Category;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\OneToMany;

/**
 * @IOC_Name="EventsCategory"
 */
#[Entity(repositoryClass: 'NstCategoryRepository')]
#[HasLifecycleCallbacks]
class NstCategory extends Category
{
    /**
     * One category has many presetShiftTimes. This is the inverse side.
     */
    #[OneToMany(mappedBy: 'category', targetEntity: PresetShiftTime::class)]
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
     * @param  PresetShiftTime  $presetShiftTime
     * @return NstCategory
     */
    public function addPresetShiftTime($presetShiftTime)
    {
        $this->presetShiftTimes->add($presetShiftTime);

        return $this;
    }

    /**
     * @param  PresetShiftTime  $presetShiftTime
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
