<?php

namespace nst\quickbooks;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Class QuickbooksInvoice
 * @Entity(repositoryClass="QuickbooksRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @Table="QuickbooksItems")
 */
class QuickbooksItem
{
    /**
     * @var int $id
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }



}