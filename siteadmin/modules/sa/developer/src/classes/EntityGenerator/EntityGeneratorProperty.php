<?php

namespace sa\developer\EntityGenerator;

class EntityGeneratorProperty
{
    protected $name;

    protected $type;

    protected $is_nullable;

    /**
     * EntityGeneratorProperty constructor.
     */
    public function __construct()
    {
        $this->is_nullable = true;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  mixed  $name
     * @return EntityGeneratorProperty
     */
    public function setName($name)
    {
        $this->name = strtolower($name);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param  mixed  $type
     * @return EntityGeneratorProperty
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsNullable()
    {
        return $this->is_nullable;
    }

    /**
     * @param  mixed  $is_nullable
     * @return EntityGeneratorProperty
     */
    public function setIsNullable($is_nullable)
    {
        $this->is_nullable = $is_nullable;

        return $this;
    }
}
