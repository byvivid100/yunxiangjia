<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/19
 * Time: 15:59
 */

namespace app\cms\controller;


use app\api\model\AgentApply;
use think\Controller;

class Agent extends Controller
{
    public function index()
    {
        $res= model('api/AgentApply')->getList();
        $this->assign('list',$res);
        $this->assign('page',$res->render());
        return $this->fetch();
    }
    public function changeStatus()
    {
        $res= model('api/AgentApply')->changeStatus(input());
        if($res){
            $this->success('操作成功');
        }
        $this->error('操作失败');
    }
}