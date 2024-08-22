<?php

namespace sa\store;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="ComposerTaskRepository")
 *
 * @InheritanceType("SINGLE_TABLE")
 *
 * @DiscriminatorColumn(name="discriminator", type="string")
 *
 * @Table(name="sa_store_composer_tasks")
 */
class ComposerTask
{
    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;

    /** @Column(type="string") */
    protected $task_name;

    /**
     * @return mixed
     */
    public function getTaskName()
    {
        return $this->task_name;
    }

    /**
     * @param  mixed  $taskName
     */
    public function setTaskName($taskName)
    {
        $this->task_name = $taskName;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
