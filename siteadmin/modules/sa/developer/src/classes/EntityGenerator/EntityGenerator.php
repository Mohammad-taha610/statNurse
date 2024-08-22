<?php

namespace sa\developer\EntityGenerator;

use sacore\application\Thread;
use sacore\application\ThreadConfig;

class EntityGenerator
{
    protected $name;

    protected $namespace;

    protected $table_name;

    protected $properties;

    protected $path;

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param  mixed  $path
     * @return EntityGenerator
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return ucfirst($this->name);
    }

    /**
     * @param  mixed  $name
     * @return EntityGenerator
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param  mixed  $namespace
     * @return EntityGenerator
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->table_name;
    }

    /**
     * @param  mixed  $table_name
     * @return EntityGenerator
     */
    public function setTableName($table_name)
    {
        $this->table_name = $table_name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param  mixed  $properties
     * @return EntityGenerator
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @return EntityGenerator
     */
    public function addProperty(EntityGeneratorProperty $property)
    {
        $this->properties[] = $property;

        return $this;
    }

    public function create()
    {
        $path = $this->path.'/'.$this->name.'.php';

        $contents = '<?php
namespace '.$this->getNamespace().';

use sacore\application\app;
use sacore\application\ioc;
use sacore\application\modelResult;
use sacore\application\Thread;
use sacore\utilities\fieldValidation;

/**
 * @Entity(repositoryClass="'.$this->getName().'Repository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @Table(name="'.$this->getTableName().'")
 * @HasLifecycleCallbacks
 */

class '.$this->getName().'  {

    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;

';

        /** @var EntityGeneratorProperty $property */
        foreach ($this->getProperties() as $property) {
            $contents .= '    /** @Column(type="'.$property->getType().'", nullable='.($property->getIsNullable() ? 'true' : 'false').') */
    protected $'.$property->getName().';'."\n\n";
        }

        $contents .= '}';

        file_put_contents($path, $contents);

        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
            $_SESSION['apc_dev_cache'] = time();
        }

        $config = new ThreadConfig();
        $config->setBlocking(true);

        $thread = new Thread('executeController', 'saDeveloperController', 'updateNewEntities', ['name' => $this->getNamespace().'\\'.$this->getName()], $config);
        $thread->run();
    }
}
