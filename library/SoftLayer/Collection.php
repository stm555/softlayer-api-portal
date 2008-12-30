<?php
class SoftLayer_Collection extends ArrayObject
{
    protected $_keyCache;

    public function __get($name)
    {
        if (is_null($this->_keyCache[$name])) {
            // get the current position
            $startKey = &key($this);
            reset($this);

            $totalItems = $this->count();

            $newCollection = new SoftLayer_Collection();

            for ($i = 0; $i < $totalItems; $i++) {
                $value = &current($this)->{$name};
                next($this);

                if ($value == null) continue;

                $newCollection[] = $value;
            }

            reset($this);
            // reset array position
            while (key($this) !== $startKey) {
                next($this);
            }

            $this->_keyCache[$name] = $newCollection;
        }

        return $this->_keyCache[$name];
    }

    public function getWhere($name, $matchValue, $returnFirst = false, $true = true, $strict = false)
    {
        $parts = explode('->', $name);
        $partCount = count($parts);

        $startKey = &key($this);
        reset($this);

        $totalItems = $this->count();

        $newCollection = new SoftLayer_Collection();

        for ($i = 0; $i < $totalItems; $i++) {
            $item = current($this);
            next($this);

            $value = $item->{$parts[0]};

            $deepCheckFound = false;

            for ($j = 1; $j < $partCount; $j++) {
                if ($value->{$parts[$j]} instanceof ArrayObject) {
                    $value = $value->{$parts[$j]}->getWhere(implode('->', array_slice($parts, $j + 1)), $matchValue, $returnFirst, $true, $strict);

                    // recursion found an item
                    if ($value->count() > 0) {
                        $deepCheckFound = true;
                        break;
                    }
                } else {
                    $value = $value->{$parts[$j]};
                }
            }

            if ($deepCheckFound === true || $true == (($strict && $value === $matchValue) || $value == $matchValue)) {
                if ($returnFirst === true) {
                    return $item;
                }

                $newCollection[] = $item;
            }
        }

        reset($this);
        // reset array position
        while (key($this) !== $startKey) {
            next($this);
        }

        return $newCollection;
    }

    public function getWhereNot($name, $matchValue, $returnFirst = false, $strict = false)
    {
        return $this->getWhere($name, $matchValue, $returnFirst, false, $strict);
    }

    public function getFirstWhere($name, $matchValue)
    {
        return $this->getWhere($name, $matchValue, true);
    }

    public function getFirstWhereNot($name, $matchValue, $strict = false)
    {
        return $this->getWhereNot($name, $matchValue, true, $strict);
    }

    public function unique()
    {
        $this->exchangeArray(array_unique($this->getArrayCopy()));

        return $this;
    }
}