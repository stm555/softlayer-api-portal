<?php

class SoftLayer_Db_Orm
{
    protected static $_tableCache = array();

    public static function get($object, $property)
    {
        if ($object instanceof SoftLayer_Db_Table_Orm_Row_Abstract) {
            $object->{$property} = self::_getSingle($object, $property);
        } else if ($object instanceof SoftLayer_Collection_Orm) {
            $key = key($object);
            reset($object);
            
            $firstItem = current($object);

            $columnMap   = $firstItem->getRow()->getTable()->getColumnMap();
            $relationMap = $firstItem->getRow()->getTable()->getRelationMap();
            
            $mapData = self::_resolveRelation($relationMap, $property);
    
            $query = new $mapData['className'](null);
    
            foreach ($mapData['properties'] AS $key => $propertyName) {
                $select = clone $object->getOrmSelect();
                $select->reset(Zend_Db_Select::COLUMNS);
                $select->columns($columnMap[$propertyName], 't1');
                $query->{$mapData['remoteProperties'][$key]} = new Zend_Db_Expr("in ({$select})");
            }
            
            $data = self::findByObjectFilter($query);            
            
            reset($data);
            $totalDataItems = $data->count();
            // map the data back to the root object
            for ($i = 0; $i < $totalDataItems; $i++) {
                $item = current($data);
                next($data);
                 
                $parentRecords = $object;
                foreach ($mapData['properties'] AS $key => $propertyName) {
                    $parentRecords = $parentRecords->getWhere($propertyName, $item->{$mapData['remoteProperties'][$key]});
                }
                   
                reset($parentRecords);
                $totalParentRecords = $parentRecords->count();                
                   
                for ($j = 0; $j < $totalParentRecords; $j++) {
                    $parent = current($parentRecords);
                    next($parentRecords);
                    $parent->{$property} = $item;
                }
            }
            
            return $data;
        } else {
            throw new SoftLayer_Db_Orm_Exception_UnknownSourceObject($object);
        }        

        return $object->{$property};
    }

    protected static function _getSingle(SoftLayer_Db_Table_Orm_Row_Abstract $object, $property)
    {
        // look up orm key

        //$relationMap = $object->getRow()->getTable()->getRelationMap();
        $columnMap   = self::getTable($object)->getColumnMap();
        
        if (isset($columnMap[$property])) {
            return null;
        }
                
        $relationMap = self::getTable($object)->getRelationMap();

        $mapData = self::_resolveRelation($relationMap, $property);

        $query = new $mapData['className'](null);

        foreach ($mapData['properties'] AS $key => $property) {
            $query->{$mapData['remoteProperties'][$key]} = $object->{$property};
        }
        
        // if there's no row, then it's a query object
        if ($object->getRow() == null) {
            return $query;
        }

        if ($mapData['has'] == 'many') {
            return self::findByObjectFilter($query, $object);
        } else {
            return self::findSingleByObjectFilter($query, $object);
        }
    }

    public static function findByObjectFilter($query, $requestingObject = null, $single = false)
    {
        $select = self::getSelectForObjectFilter($query);
        
        echo "Executing query: {$select} <hr />";
            
        if ($single === false) {
            return $select->getTable()->fetchAll($select);
        } else {
            return $select->getTable()->fetchRow($select);
        }
    }

    public static function findSingleByObjectFilter($query, $requestingObject = null)
    {
        return self::findByObjectFilter($query, $requestingObject, true);
    }

    protected static function _resolveRelation($relationMap, $property)
    {
        $mapData = $relationMap[$property];

        if (is_null($relationMap[$property])) {
            // check for Count properties/Max/etc
        }

        return $mapData;
    }
    
    protected static $_uniqueCorrelations = array();
    
    protected static function _uniqueCorrelation($object)
    {
        $objectHash = spl_object_hash($object);

        if (isset(self::$_uniqueCorrelations[$objectHash])) {
            return self::$_uniqueCorrelations[$objectHash];
        }
        
        self::$_uniqueCorrelations[$objectHash] = 't' . (count(self::$_uniqueCorrelations) + 1);

        return self::$_uniqueCorrelations[$objectHash];
    }

    public static function getSelectForObjectFilter($filter, $select = null, $parentFilter = null, $parentMapData = null, $parentColumnMap = null)
    {
        $table = self::getTable($filter);

        $columnMap = $table->getColumnMap();
        
        if ($select == null) {
            // reset table correlations for new filter
            self::$_uniqueCorrelations = array();
        }
        
        $prefix = self::_uniqueCorrelation($filter);

        if ($select == null) {            
            $select = $table->select();
    
            $select->from(array($prefix => $table->getName()));
        } else {
            if (is_null($parentFilter) || is_null($parentMapData) || is_null($parentColumnMap)) {
                throw new Exception("The parameters parentFilter, parentMapData, and parentColumnMap must be provided for a sub filter.");
            }
            
            $parentPrefix = self::_uniqueCorrelation($parentFilter);
            
            $join = array();
            
            foreach ($parentMapData['properties'] AS $key => $propertyName) {
                $join[] = "{$prefix}.{$columnMap[$parentMapData['remoteProperties'][$key]]} = {$parentPrefix}.{$parentColumnMap[$propertyName]}";
            }
            
            $select->join(array($prefix => $table->getName()), implode(' and ', $join), null);
        }

        foreach ($columnMap AS $property => $column) {
            if (property_exists($filter, $property) && $filter->$property !== null) {
                if ($filter->$property instanceof Zend_Db_Expr) {
                    $select->where("{$prefix}.{$column} ?", $filter->$property);
                } else {
                    $select->where("{$prefix}.{$column} = ?", $filter->$property);
                }
            }
        }
        
        $relationMap = $table->getRelationMap();
        
        foreach ($filter AS $property => $value) {
            $mapData = self::_resolveRelation($relationMap, $property);
        
            if ($mapData != null) {
                self::getSelectForObjectFilter($value, $select, $filter, $mapData, $columnMap);
            }
        }
        
/*        print_pre($filter, __LINE__);
        print_pre($table->getRelationMap(), __LINE__);
        
        print_pre($select->__toString());*/

        return $select;
    }

    public static function getTable($filter)
    {
        $tableName = self::getBaseClass($filter) . '_Table';

        if (isset(self::$_tableCache[$tableName])) {
            return self::$_tableCache[$tableName];
        }

        if (!class_exists($tableName, true)) {
            throw new SoftLayer_Db_Orm_Exception_TableNotFound($tableName);
        }

        self::$_tableCache[$tableName] = new $tableName(null);

        return self::$_tableCache[$tableName];
    }

    public static function getBaseClass($query)
    {
        return get_class($query);
    }
}