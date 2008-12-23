<?php

class SoftLayer_Controller_Action_Helper_SoapClient extends Zend_Controller_Action_Helper_Abstract
{
    public function getSoapClient($serviceName, $id = null)
    {
        return SoftLayer_Soap_Client::getSoapClient($serviceName, $id);
    }

    public function direct($serviceName, $id = null)
    {
        return $this->getSoapClient($serviceName, $id);
    }
}