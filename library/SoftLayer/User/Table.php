<?php
class SoftLayer_User_Table extends SoftLayer_Db_Table_Orm_Table_Abstract
{
    protected $_name   = 'user';
    protected $_primary = 'id';

    protected $_columnMap = array(
        'id'        => 'id',
        'username'  => 'username',
        'firstName' => 'first_name',
        'lastName'  => 'last_name'
    );

    protected $_relationMap = array(
        'roles' => array(
            'has' => 'many',
            'className' => 'SoftLayer_User_Role',
            'properties' => array('id'),
            'remoteProperties' => array('userId')
        )
    );
}