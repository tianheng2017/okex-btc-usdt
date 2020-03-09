<?php
/**
 *
 */

namespace app\common\model;
use think\Model;

class Trade extends Model
{
    protected $pk = 'id';
    
    protected function setTimeAttr($value){
        return strtotime($value);
    }
    
    protected function setTimeStampAttr($value){
        return strtotime($value);
    }

}