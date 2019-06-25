<?php
namespace app\api\controller;

use think\Controller;
use app\common\Cache;
use app\common\Code;
use app\common\Wechat;

class Apply extends Controller
{

    //服务列表
    public function applylist()
    {
        $input = input();

        $userlist = model('Apply')->userlist($input['uuid']);       
        foreach ($userlist as $k =>$v) {
            $res = self::applyAction($v);
            $userlist[$k]['action'] = $res['action1'];
            $userlist[$k]['message'] = $res['message'];
            $userlist[$k]['cancel'] = $res['cancel1'];
        }

        $agentlist = model('Apply')->agentlist($input['uuid']);      
        foreach ($agentlist as $k =>$v) {
            $res = self::applyAction($v);
            $agentlist[$k]['action'] = $res['action2'];
            $agentlist[$k]['message'] = $res['message'];
            $agentlist[$k]['cancel'] = $res['cancel2'];
        }

        $apply = array_merge($userlist->toArray(), $agentlist->toArray());
        Code::send(200, $apply);

    }

    //会员服务列表
    public function userlist()
    {
        $input = input();
        $apply = model('Apply')->userlist($input['uuid']);
        
        foreach ($apply as $k =>$v) {
            $res = self::applyAction($v);
            $apply[$k]['action'] = $res['action1'];
            $apply[$k]['message'] = $res['message'];
            $apply[$k]['cancel'] = $res['cancel1'];
        }
        Code::send(200, $apply);
    }

    //经纪人服务列表
    public function agentlist()
    {
        $input = input();
        $apply = model('Apply')->agentlist($input['uuid']);
        
        foreach ($apply as $k =>$v) {
            $res = self::applyAction($v);
            $apply[$k]['action'] = $res['action2'];
            $apply[$k]['message'] = $res['message'];
            $apply[$k]['cancel'] = $res['cancel2'];
        }
        Code::send(200, $apply);
    }

    public function applyAction($v)
    {
        $result['action1'] = '';
        $result['action2'] = '';
        $result['message'] = '';
        $result['cancel1'] = 0;
        $result['cancel2'] = 0;
        if ($v['status'] == 9 && $v['status2'] == 9)
            $result['message'] = '服务订单已完成';
        else if ($v['status'] == -1)
            $result['message'] = '会员已取消服务';
        else if ($v['status2'] == -1)
            $result['message'] = '经纪人已取消服务';
        else if ($v['status2'] == -2)
            $result['message'] = '服务提前失败';
        else {
            if ($v['type'] < 10) {
                if ($v['status'] == 1) {
                    $result['action2'] = 'A确认服务';
                    $result['message'] = '等待经纪人确认';
                    $result['cancel1'] = 1;
                }
                else if ($v['status'] == 2) {
                    $result['action1'] = 'a支付';
                    $result['message'] = '需要支付提前费用';
                    $result['cancel1'] = 1;
                }
                else if ($v['status'] == 5 && $v['status2'] == 5) {
                    if ($v['type'] == 1)
                        $result['action2'] = 'B服务完成，上传资料';
                    $result['message'] = '服务进行中';
                }
                else if ($v['status2'] == 9) {
                    $result['action1'] = 'b确认服务完成，支付服务费';
                    $result['message'] = '经纪人已置服务完成，请会员确认';
                }
            }
            else if ($v['type'] < 20) {
                if ($v['status'] == 1) {
                    $result['action2'] = 'A确认服务';
                    $result['message'] = '等待经纪人确认';
                    $result['cancel1'] = 1;
                }
                else if ($v['status'] == 2) {
                    $result['action'] = 'a支付';
                    $result['message'] = '需要支付提前费用';
                    $result['cancel1'] = 1;
                }
                else if ($v['status2'] == 2) {
                    $result['action2'] = 'B录入房产信息';
                    $result['message'] = '需要录入房产信息';
                    $result['cancel1'] = 1;
                }
                else if ($v['status'] == 5 && $v['status2'] == 5) {
                    // $result['action2'] = 'A服务完成，上传资料';
                    $result['message'] = '服务进行中';
                }
                else if ($v['status2'] == 9) {
                    $result['action1'] = 'b确认服务完成，支付服务费';
                    $result['message'] = '经纪人已置服务完成，请会员确认';
                }
            }
            else if ($v['type'] < 30) {
                if ($v['status'] == 1) {
                    $result['action2'] = 'A确认服务';
                    $result['message'] = '等待经纪人确认';
                    $result['cancel1'] = 1;
                }
                else if ($v['status'] == 2) {
                    $result['action1'] = 'a支付';
                    $result['message'] = '需要支付提前费用';
                    $result['cancel1'] = 1;
                }
                else if ($v['status'] == 5 && $v['status2'] == 5) {
                    $result['action2'] = 'B服务完成，上传资料';
                    $result['message'] = '服务进行中';
                }
                else if ($v['status2'] == 9) {
                    $result['action1'] = 'b确认服务完成，支付服务费';
                    $result['message'] = '经纪人已置服务完成，请会员确认';
                }
            }
        }

        return $result;
    }

    //服务详情
    public function detail()
    {
        $input = input();
        if (empty($input['id']))
            Code::send(999, '参数错误');
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
        if (empty($input['id']))
            Code::send(999, '参数错误');
        $apply = model('Apply')->searchApply($input['id']);
        if ($apply['status'] <> 1) 
            Code::send(999, '状态错误1');
        if ($apply['agent_id'] <> $input['uuid'])
            Code::send(999, '用户错误');
        \Db::transaction(function() use($apply) {
            if($apply['type2'] == 2) {
                $status = 2;
            }
            else {
                $status = 5;
            }

            if ($apply['type'] == 11 || $apply['type'] == 12 || $apply['type'] == 13)   {
                $status2 = 2;
            }
            else {
                $status2 = 5;
            }
            if ($status == 5 && ($apply['type'] == 2 || $apply['type'] == 3)) {
                $propety = model('Propety')->searchPropety($apply['ppid']);
                if (!$propety || $propety['status'] <> 5) 
                    Code::send(999, '商品在非销售状态');
                $order = model('Order')->insertOrder($apply, $propety);
            }
            $res = \Db::name('apply')->where(['id' => $apply['id']])->update(['status' => $status, 'status2' => $status2, 'update_time' => $_SERVER['REQUEST_TIME']]);
            $res = model('ApplyRecord')->insertApplyRecord($apply['agent_id'], $apply, 2, '经纪人接受服务');

            if (!$res) 
                Code::send(999, 'sql error');
        });
        Code::send(200);
    }

    //收费服务提前付款
    public function payAdvByUser()
    {  
        $input = input();
        if (empty($input['id']))
            Code::send(999, '参数错误');
        $apply = model('Apply')->searchApply($input['id']);
        if ($apply['status'] <> 2) 
            Code::send(999, '状态错误2');
        if ($apply['user_id'] <> $input['uuid'])
            Code::send(999, '用户错误');

        $return = null;
        \Db::transaction(function() use($apply, &$return) {
            $money = $apply['money_adv'];
            $res = controller('Payment')->pay($apply, 1, $money);
            if (empty($res['return_code'])) {    //不用到微信支付
                $status = 5;
                if ($apply['status2'] == 5 && ($apply['type'] == 2 || $apply['type'] == 3)) {
                    $propety = model('Propety')->searchPropety($apply['ppid']);
                    $order = model('Order')->insertOrder($apply, $propety);
                }
                $res = \Db::name('apply')->where(['id' => $apply['id']])->update(['status' => $status, 'update_time' => $_SERVER['REQUEST_TIME']]);
                $res = model('ApplyRecord')->insertApplyRecord($apply['user_id'], $apply, 1, '会员支付预先费用');
            }
            else {
                $return = $res;
            }

            if (!$res) 
                Code::send(999, 'sql error');
        });
        Code::send(200, $return);
    }

    //会员置成功
    public function succByUser()
    {  
        $input = input();
        if (empty($input['id']))
             Code::send(999, '参数错误');
        $apply = model('Apply')->searchApply($input['id']);
        if ($apply['status2'] <> 9) 
            Code::send(999, '状态错误9');
        if ($apply['user_id'] <> $input['uuid'])
            Code::send(999, '用户错误');

        $return = null;
        \Db::transaction(function() use($apply, &$return) {
            //支付服务费
            $money = $apply['money'];
            $res = controller('Payment')->pay($apply, 2, $money);
            if (empty($res['return_code'])) {    //不用到微信支付
                $status = 9;
                $res = \Db::name('apply')->where(['id' => $apply['id']])->update(['status' => $status, 'update_time' => $_SERVER['REQUEST_TIME']]);
                $res = model('ApplyRecord')->insertApplyRecord($apply['user_id'], $apply, 1, '会员置成功服务，支付服务费');
            }
            else {
                $return = $res;
            }

            if (!$res) 
                Code::send(999, 'sql error');
        });
        Code::send(200, $return);
    }

    //经纪人置成功
    public function succByAgent()
    {  
        $input = input();
        if (empty($input['id']))
             Code::send(999, '参数错误');
        $apply = model('Apply')->searchApply($input['id']);
        if ($apply['agent_id'] <> $input['uuid'])
            Code::send(999, '用户错误');
        if ($apply['type'] == 2 || $apply['type'] == 3) {
            Code::send(999, '无法操作，等待交易订单完成');
        }
        else if ($apply['type'] == 11 || $apply['type'] == 12 || $apply['type'] == 13) {
            Code::send(999, '无法操作，等待交易订单完成');
        }

        \Db::transaction(function() use($apply) {
            $res = \Db::name('apply')->where(['id' => $apply['id']])->update(['status2' => 9, 'update_time' => $_SERVER['REQUEST_TIME']]);
            $res = model('ApplyRecord')->insertApplyRecord($apply['agent_id'], $apply, 2, '经纪人成功服务');

            if (!$res) 
                Code::send(999, 'sql error');
        });
        Code::send(200);
    }

    //会员置失败
    public function failByUser()
    {  
        $input = input();
        if (empty($input['id']))
            Code::send(999, '参数错误');
        $apply = model('Apply')->searchApply($input['id']);
        if ($apply['user_id'] <> $input['uuid'])
            Code::send(999, '用户错误');

        \Db::transaction(function() use($apply) {
            $res = self::failApply($apply, 0);

            if (!$res) 
                Code::send(999, 'sql error');
        });
        Code::send(200);
    }

    //经纪人置失败
    public function failByAgent()
    {  
        $input = input();
        if (empty($input['id']))
            Code::send(999, '参数错误');
        $apply = model('Apply')->searchApply($input['id']);
        if ($apply['agent_id'] <> $input['uuid'])
            Code::send(999, '用户错误');

        \Db::transaction(function() use($apply) {
            $res = self::failApply($apply, 1);

            if (!$res) 
                Code::send(999, 'sql error');
        });
        Code::send(200);
    }


    public function failApply($apply, $utype)
    {  
        if ($utype == 0){
            $res = \Db::name('apply')->where(['id' => $apply['id']])->update(['status' => -1, 'update_time' => $_SERVER['REQUEST_TIME']]);
            $res = model('ApplyRecord')->insertApplyRecord($apply['user_id'], $apply, 1, '会员取消服务');
        }
        else if ($utype == 1){
            $res = \Db::name('apply')->where(['id' => $apply['id']])->update(['status2' => -1, 'update_time' => $_SERVER['REQUEST_TIME']]);
            $res = model('ApplyRecord')->insertApplyRecord($apply['agent_id'], $apply, 2, '经纪人取消服务');
        }

        //卖家置失败，订单关联的所有买家也失败
        if ($apply['type'] == 11 || $apply['type'] == 12 || $apply['type'] == 13) {
            $res = \Db::name('propety')->where(['id' => $apply['ppid']])->update(['status' => -1, 'update_time' => $_SERVER['REQUEST_TIME']]);
            $order_list = \Db::name('order')->where(['target_apply_id' => $apply['id']])->where('status', '>', 0)->select();
            foreach ($order_list as $o) {
                $res = \Db::name('apply')->where(['id' => $o['apply_id']])->update(['status2' => -2, 'update_time' => $_SERVER['REQUEST_TIME']]);
                $res = \Db::name('order')->where(['id' => $o['id']])->update(['status2' => -1, 'update_time' => $_SERVER['REQUEST_TIME']]);
            }
        }
        //买家置失败
        else if ($apply['type'] == 2 || $apply['type'] == 3) {
            $res = \Db::name('order')->where(['apply_id' => $apply['id']])->update(['status' => -1, 'update_time' => $_SERVER['REQUEST_TIME']]);
        }

        return $res;
    }

}