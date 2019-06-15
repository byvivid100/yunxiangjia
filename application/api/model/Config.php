<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/13
 * Time: 11:56
 */

namespace app\api\model;


use think\Model;

class Config extends Model
{
    public function getConfig($name)
    {
        return self::where('name','=',$name)->value('value');
    }
}