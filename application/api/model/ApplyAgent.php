<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/18
 * Time: 15:26
 * 申请成为经纪人
 */

namespace app\api\model;


use think\Model;

class ApplyAgent extends Model
{
    public function apply($params)
    {
        return self::allowField(true)->save($params);
    }
}