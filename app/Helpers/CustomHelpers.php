<?php

namespace App\Helpers;

class CustomHelpers {

    public static function searchInCollection($collection, $key1, $value1, $key2 = '', $value2 = '') {

        if ($key2 == '') {
            $result = $collection->where($key1, $value1)
                    ->toArray();
        } else {
            $result = $collection->where($key1, $value1)
                    ->where($key2, $value2)
                    ->toArray();
        }

        return $result;
    }

    public static function getCollectionCount($collection, $key1, $value1, $key2 = '', $value2 = '') {

        if ($key2 == '') {
            if (!is_null($collection)){

                $result = $collection->where($key1, $value1)
                    ->count();
            }else{
//                dd($collection);
                return 0;
            }
        } else {
            $result = $collection->where($key1, $value1)
                    ->where($key2, $value2)
                    ->count();
        }
        //print_r($collection);
        //echo "result = ".$result;
        //die;
        return $result;
    }

    public static function activeUrl($url = null)
    {
        return (request()->getPathInfo() == $url) ? 'active' : '';
    }
    
}
