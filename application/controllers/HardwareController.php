<?php
class HardwareController extends Zend_Controller_Action
{
    public function indexAction()
    { 
        $soapClient = SoftLayer_Soap_Client::getSoapClient('SoftLayer_Account');

        $objectMask = new SoftLayer_Soap_ObjectMask();
        $objectMask->hardware->datacenter;

        $paginator = new Zend_Paginator(new SoftLayer_Paginator_Adapter_Soap($soapClient, 'getHardware', $objectMask));
        $paginator->setItemCountPerPage(25);
        $paginator->setCurrentPageNumber($this->_getParam('page'));

        $this->view->paginator = $paginator;
    }
}
