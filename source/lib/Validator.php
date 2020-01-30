<?php
namespace Volsu\Validation;

class Validator
{
    /**
     * Validate input for exist and digits
     * @param null $input
     * @return bool
     */
    public static function isDigits($input = null){
        if(!empty($input) && preg_match('/^[a-zA-Zа-яА-Я0-9]+$/', $input)){
            return true;
        }
        return false;
    }

    /**
     * Validate is empty object
     * @param $object
     * @return bool
     */
    public static function isEmptyObject($object){
        return (!count((array)$object)) ? true : false;
    }
}