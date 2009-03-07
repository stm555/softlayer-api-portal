<?php

class SoftLayer_Collection_Orm extends SoftLayer_Collection
{
    protected $_ormSelect;
    
    public function __get($name)
    {
    	$this->_savePosition();
    	
    	// need to beef up the logic here to only fetch for records that need it, for now we'll skip the fetch completley if the first record has the property
    	
    	$fetchData = !property_exists(current($this), $name);
    	
    	$this->_restorePosition();
    
    	if ($fetchData == false) {    		    		
            return parent::__get($name);
    	} else {
            return SoftLayer_Db_Orm::get($this, $name);
    	}
    }

    public function setOrmSelect($select)
    {
        $this->_ormSelect = $select;
    }

    public function getOrmSelect()
    {
        return $this->_ormSelect;
    }
}