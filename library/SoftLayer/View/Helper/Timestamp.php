<?php

class SoftLayer_View_Helper_Timestamp extends Zend_View_Helper_Abstract
{
    /**
     * Zend_Date instance
     *
     * @var Zend_Date
     */
    private $_date;

    public function __construct()
    {
        $this->_date = new Zend_Date();
    }

    public function timestamp($date)
    {

        if ($date == null) {
            return null;
        }

        $this->_date->set($date);

        return $this->_date;
    }
}