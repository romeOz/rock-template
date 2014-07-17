<?php

namespace rock\template;

trait ObjectTrait
{
    use ClassName;

    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            $this->setProperties($config);
        }
        $this->init();
    }

    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init()
    {
    }

    /**
     * Configures an object with the initial property values.
     *
     * @param array  $properties the property initial values given in terms of name-value pairs.
     */
    public function setProperties(array $properties)
    {
        foreach ($properties as $name => $value) {
            $this->$name = $value;
        }
    }
} 