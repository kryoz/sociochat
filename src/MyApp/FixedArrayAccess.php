<?php

namespace MyApp;

abstract class FixedArrayAccess implements \ArrayAccess
{
    protected $propertyNames = [];
    protected $properties;
    
    public function __construct($propertyNames = null)
    {
        if (is_array($propertyNames)) {
            $this->propertyNames = $propertyNames;
        }
        
        foreach ($this->propertyNames as $propertyName) {
            $this->properties[$propertyName] = null;
        }
    }

	/**
	 * @param string $property
	 * @param mixed $val
	 * @return $this
	 */
	protected function addProperty($property, $val = null)
    {
        if (!$this->offsetExists($property)) {
            $this->propertyNames = array_merge($this->propertyNames, [$property]);
            $this->properties[$property] = $val;
        }

	    return $this;
    }

    public function offsetExists($offset)
    {
        return in_array($offset, $this->propertyNames, true);
    }

    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->properties[$offset];
        } 
    }

    public function offsetSet($offset, $value)
    {
        if ($this->offsetExists($offset)) {
            $this->properties[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            $this->properties[$offset] = 0;
        }
    }

}

