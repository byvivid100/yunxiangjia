<?php
namespace app\api\controller;

use think\Controller;
use app\common\Cache;
use app\common\Code;
use app\common\Wechat;

class Apply extends Controller
{
    //会员服务列表
    public function userlist()
    {
        $input = input();
        $apply = model('Apply')->userlist($input['uuid']);
        Code::send(200, $apply);
    }

    //经纪人服务列表
    public function agentlist()
    {
        $input = input();
        $apply = model('Apply')->agentlist($input['uuid']);
        Code::send(200, $apply);
    }

    //服务详情
    public function detail()
    {
        $input = input();
        if (empty($input['id'])) exit;
        $apply = model('Apply')->searchApply($input['id']);
        if (!empty($apply['svid'])) {
            $apply['service'] = model('Service')->searchService($apply['svid']);
        }
        if (!empty($apply['ppid'])) {
            $apply['propety'] = model('Propety')->searchPropety($apply['ppid']);
        }
        Code::send(200, $apply);
    }

    //经纪人接受服务
    public function checkApply()
    {
        $input = input();
        if (empty($input['id'])) exit;
        $apply = model('Apply')->searchApply($input['id']);
        if ($apply['status'] <> 1) Code::send(500);
        \Db::transaction(function(){
            if($apply['type2'] == 1) {
                $status = 2;
            }
            else {
                $status = 5;
            }

            // if ($apply['type'] == 11 || $apply['type'] == 12 || $apply['type'] == 13)   {
            //     $status2 = 2;
            // }
            // else {
                $status2 = 5;
            // }

            if ($status == 5 && ($apply['type'] == 2 || $apply['type'] == 3)) {
                $propety = model('Propety')->searchPropety($apply['ppid']);
                if ($propety['status'] <> 5) Code::send(500);
                $order = model('Order')->insertOrder($apply, $propety);
            }

            $res = db('apply')->where('id' => $input['id'])->update(['status' => $status, 'status2' => $status2, 'update_time' => time()]);
        });
        Code::send(200, $res);
    }

    //收费服务提前付款
    public function payAdvByUser()
    {  
        $input = input();
        if (empty($input['id'])) exit;
        $apply = model('Apply')->searchApply($input['id']);
        if ($apply['status'] <> 2) Code::send(500);
        \Db::transaction(function(){
            $money = $apply['money_adv'];
            $res = controller('Payment')->pay($apply, 1, $money);

            $status = 5;
            if ($status2 == 5 && ($apply['type'] == 2 || $apply['type'] == 3)) {
                $propety = model('Propety')->searchPropety($apply['ppid']);
                if ($propety['status'] <> 1) Code::send(500);
                $order = model('Order')->insertOrder($apply, $propety);
            }
            $res = db('apply')->where('id' => $input['id'])->update(['status' => $status, 'update_time' => time()]);
        });
        Code::send(200, $res);
    }

    //服务完成后付款
    public function payAftByUser()
    {  
        $input = input();
        if (empty($input['id'])) exit;
        $apply = model('Apply')->searchApply($input['id']);
        if ($apply['status'] <> 9) Code::send(500);
        \Db::transaction(function(){
            $money = $apply['money'];
            $res = controller('Payment')->pay($apply, 2, $money);

            $status = 10;
            $res = db('apply')->where('id' => $input['id'])->update(['status' => $status, 'status2' => $status, 'update_time' => time()]);
        });
        Code::send(200, $res);
    }

    //会员置成功
    public function succByUser()
    {  
        $input = input();
        if (empty($input['id'])) exit;
        $apply = model('Apply')->searchApply($input['id']);
        if ($apply['status2'] <> 9) Code::send(500);
        $res = self::succApply($apply, 0);
        Code::send(200, $res);
    }

    //经纪人置成功
    public function succByUser()
    {  
        $input = input();
        if (empty($input['id'])) exit;
        $apply = model('Apply')->searchApply($input['id']);
        $res = self::succApply($apply, 1);
        Code::send(200, $res);
    }

    public function succApply($apply, $utype)
    { 
        \Db::transaction(function(){
            if ($utype == 0){
                $res = db('apply')->where('id' => $apply['id'])->update(['status' => 9, 'update_time' => time()]);
            }
            else if ($utype == 1){
                $res = db('apply')->where('id' => $apply['id'])->update(['status2' => 9, 'update_time' => time()]);
            }
        });
        return $res;
    }
    
    //会员置失败
    public function failByUser()
    {  
        $input = input();
        if (empty($input['id'])) exit;
        $apply = model('Apply')->searchApply($input['id']);
        $res = self::failApply($apply, 0);
        Code::send(200, $res);
    }

    //经纪人置失败
    public function failByAgent()
    {  
        $input = input();
        if (empty($input['id'])) exit;
        $apply = model('Apply')->searchApply($input['id']);
        $res = self::failApply($apply, 1);
        Code::send(200, $res);
    }


    public function failApply($apply, $utype)
    {  
        \Db::transaction(function(){
            if ($utype == 0){
                $res = db('apply')->where('id' => $apply['id'])->update(['status' => -1, 'update_time' => time()]);
            }
            else if ($utype == 1){
                $res = db('apply')->where('id' => $apply['id'])->update(['status2' => -1, 'update_time' => time()]);
            }

            //卖家置失败，订单关联的所有买家也失败
            if ($apply['type'] == 11 || $apply['type'] == 12 || $apply['type'] == 13) {
                $res = db('propety')->where('id' => $apply['ppid'])->update(['status' => -1, 'update_time' => time()]);
                $apply_ids = db('order')->where(['ppid' => $apply['ppid'], 'target_agent_id' => $apply['agent_id'], 'status' => 5])->column('apply_id');
                foreach ($apply_ids as $id) {
                    $res = db('apply')->where('id' => $id)->update(['status2' => -2, 'update_time' => time()]);
                    $res = db('order')->where('apply_id' => $id)->update(['status2' => -2, 'update_time' => time()]);
                }
            }
            //买家置失败
            else if ($apply['type'] == 2 || $apply['type'] == 3) {
                $res = db('order')->where('apply_id' => $apply['id'])->update(['status' => -1, 'update_time' => time()]);
            }
        });
        return $res;
    }
}