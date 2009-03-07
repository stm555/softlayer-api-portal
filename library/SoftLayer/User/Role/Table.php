<?php
class SoftLayer_User_Role_Table extends SoftLayer_Db_Table_Orm_Table_Abstract
{
    protected $_name   = 'user_role';
    protected $_primary = 'id';

    protected $_columnMap = array
    (
        'id'        => 'id',
        'userId'    => 'user_id',
        'roleId'    => 'role_id',
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
