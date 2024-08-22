<?php

namespace nst\member;

use nst\system\NstDefaultRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Mapping as ORM;
use Doctrine\Mapping\DiscriminatorColumn;
use Doctrine\Mapping\Entity;
use Doctrine\Mapping\InheritanceType;
use Doctrine\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToOne;

class ExecutiveRepository extends NstDefaultRepository
{
    
}

