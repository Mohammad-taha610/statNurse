<?php

namespace sa\system;

use sacore\application\ioc;

class TrackedRevisionEntity {
    
    /** @var string */
    private $className;
    /** @var array */
    private $ignoredFields;
    
    public function __construct($className, $ignoredFields = array())
    {
        $this->className = ioc::staticGet($className);
        
        $ignoredFields[] = 'date_created';
        $ignoredFields[] = 'date_updated';
        $ignoredFields[] = 'created_at';
        $ignoredFields[] = 'updated_at';
        
        $this->ignoredFields = $ignoredFields;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * @return array
     */
    public function getIgnoredFields()
    {
        return $this->ignoredFields;
    }

    /**
     * @param array $ignoredFields
     */
    public function setIgnoredFields($ignoredFields)
    {
        $this->ignoredFields = $ignoredFields;
    }
}