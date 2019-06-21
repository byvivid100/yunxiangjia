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

class AgentApply extends Model
{
    //保存申请
    public function apply($params)
    {
        return self::allowField(true)->save($params);
    }
    //获取列表
    public  function getList($params=null)
    {
        return self::alias('a')
            ->leftJoin('user b','a.uid=b.uuid')
            ->leftJoin('cities c','a.city=c.cityid')
            ->leftJoin('provinces d','a.province=d.provinceid')
            ->field('b.realname,a.*,c.name as cityname,d.province as provincename')
        ->where($params)->order('id desc')->paginate(15,false,['query'=>$params]);
    }
    //改变状态
    public  function changeStatus($params)
    {
        return self::where('id',$params['id'])->setField('status',$params['status']);
    }
}