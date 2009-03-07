<?php

class SoftLayer_Controller_Action_Helper_SoapPaginator extends Zend_Controller_Action_Helper_Abstract
{
    public function direct($currentPage = 1, SoftLayer_Soap_Client $soapClient, $soapMethod, $objectMask = null, $objectFilter = null)
    {
        $paginator = new SoftLayer_Paginator(new SoftLayer_Paginator_Adapter_Soap($soapClient, $soapMethod, $objectMask, $objectFilter));
        $paginator->setCurrentPageNumber($currentPage);
        $paginator->setItemCountPerPage(25); // need to move this to a user config value

        return $paginator;
    }
}