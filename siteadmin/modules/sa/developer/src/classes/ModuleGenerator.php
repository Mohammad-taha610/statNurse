<?php

namespace sa\developer;

use sacore\application\app;

class ModuleGenerator
{
    protected $display_name;

    protected $name;

    protected $namespace;

    protected $entities;

    protected $moduleDirectory;

    protected $configFilePath;

    /**
     * ModuleGenerator constructor.
     */
    public function __construct($display_name, $namespace)
    {
        $this->setDisplayName($display_name);
        $this->setNamespace($namespace);
        $this->entities = [];
        $parts = explode('\\', $this->namespace);
        $this->setName($parts[1]);
        $this->configFilePath = $this->moduleDirectory.'/'.$parts[1].'Config.php';
    }

    /**
     * @return mixed
     */
    public function getDisplayName()
    {
        return $this->display_name;
    }

    /**
     * @param  mixed  $display_name
     * @return ModuleGenerator
     */
    public function setDisplayName($display_name)
    {
        $this->display_name = $display_name;

        return $this;
    }

    /**
     * @return string
     */
    public function getConfigFilePath()
    {
        return $this->configFilePath;
    }

    /**
     * @param  string  $configFilePath
     * @return ModuleGenerator
     */
    public function setConfigFilePath($configFilePath)
    {
        $this->configFilePath = $configFilePath;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getModuleDirectory()
    {
        return $this->moduleDirectory;
    }

    /**
     * @param  mixed  $moduleDirectory
     * @return ModuleGenerator
     */
    public function setModuleDirectory($moduleDirectory)
    {
        $this->moduleDirectory = $moduleDirectory;

        return $this;
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
     * @return ModuleCreator
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
     * @return ModuleCreator
     */
    public function setNamespace($namespace)
    {
        $app = app::getAppPath();
        $this->namespace = $namespace;

        $path = str_replace('\\', '/', $this->namespace);
        $this->moduleDirectory = $app.'/modules/'.$path.'/src/classes';

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * @param  mixed  $entities
     * @return ModuleGenerator
     */
    public function setEntities($entities)
    {
        $this->entities = $entities;

        return $this;
    }

    /**
     * @return ModuleGenerator
     */
    public function addEntity(EntityGenerator $entity)
    {
        $this->entities[] = $entity;

        return $this;
    }

    public function create()
    {
        if (! file_exists($this->moduleDirectory)) {
            mkdir($this->moduleDirectory, 0777, true);
        }

        if (! file_exists($this->configFilePath)) {
            $this->generateConfig();
        }

        foreach ($this->getEntities() as $entity) {
            $entity->create();
        }

        $app = app::getAppPath();
        $vendor = explode('\\', $this->getNamespace());
        array_pop($vendor);
        $vendor = implode('\\', $vendor);

        $module = [
            'namespace' => $this->getNamespace(),
            'vendor' => $vendor,
            'module' => $this->getName(),
            'dir' => $app.'/modules',
        ];

        CodeGeneration::buildControllers($module);
    }

    public function generateConfig()
    {
        if (count($this->entities) > 0) {
            $baseEntity = $this->entities[0];
        }

        file_put_contents($this->configFilePath, '<?php
namespace '.$this->getNamespace().';

use sacore\application\moduleConfig;
use sacore\application\SaRestfulRoute;
use sacore\application\navItem;
use sacore\application\resourceRoute;
use sacore\application\saRoute;
use sacore\application\staticResourceRoute;

abstract class '.$this->getName().'Config extends moduleConfig
{
    static function getRoutes()
    {
        return array(

            new SaRestfulRoute(
                array(
                    "id"=>"sa_'.strtolower($this->getName()).'",
                    "route_settings"=>array(),
                    "name"=>"'.$this->getDisplayName().'",
                    "prefix_route"=>"/siteadmin/'.$this->getName().'",
                    "controller"=>"SaManage'.ucfirst($this->getName()).'Controller",
                    "prefix_methods"=>"",
                    '.(! empty($baseEntity) ? '"base_entity"=>"'.$baseEntity->getName().'"' : '').'
                )
            )

        );
    }

    static function getNavigation()
    {
        return array(
            new navItem(array("id"=>"sa_'.strtolower($this->getName()).'", "name"=>"'.$this->getDisplayName().'", "icon"=>"fa fa-cubes", "parent"=>"siteadmin_root")),
            new navItem(array("id"=>"sa_'.strtolower($this->getName()).'_index",  "name"=>"'.$this->getDisplayName().' List", "routeid"=>"sa_'.strtolower($this->getName()).'_index", "parent"=>"sa_'.strtolower($this->getName()).'")),
        );
    }
}');
    }
}
