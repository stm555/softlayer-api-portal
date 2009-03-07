<?php
class SoftLayer_Role_Table extends SoftLayer_Db_Table_Orm_Table_Abstract
{
    protected $_name   = 'role';
    protected $_primary = 'id';

    protected $_columnMap = array(
        'id'            => 'id',
        'role'          => 'role',
        'keyname'       => 'keyname',
        'description'   => 'description',
    );

    protected $_relationMap = array(
        'role' => array(
            'has' => 'one',
            'className' => 'SoftLayer_Role',
            'properties' => array('roleId'),
            'remoteProperties' => array('id')
        )
    );
}