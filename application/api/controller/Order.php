<?php
namespace app\api\controller;

use think\Controller;
use app\common\Cache;
use app\common\Code;
use app\common\Wechat;

class Order extends Controller
{

    //订单列表
    public function orderlist()
    {
        $input = input();

        $buylist = model('Order')->buylist($input['uuid']);     
        foreach ($buylist as $k =>$v) {
            $res = self::orderAction($v);
            $buylist[$k]['action'] = $res['action1'];
            $buylist[$k]['message'] = $res['message'];
            $buylist[$k]['cancel'] = $res['cancel1'];
        }

        $selllist = model('Order')->selllist($input['uuid']);        
        foreach ($selllist as $k =>$v) {
            $res = self::orderAction($v);
            $selllist[$k]['action'] = $res['action2'];
            $selllist[$k]['message'] = $res['message'];
            $selllist[$k]['cancel'] = $res['cancel2'];
        }

        $order = array_merge($buylist->toArray(), $selllist->toArray());
        Code::send(200, $order);

    }

    //订单列表：作为买家
    public function buylist()
    {
        $input = input();
        $order = model('Order')->buylist($input['uuid']);
        
        foreach ($order as $k =>$v) {
            $res = self::orderAction($v);
            $order[$k]['action'] = $res['action1'];
            $order[$k]['message'] = $res['message'];
            $order[$k]['cancel'] = $res['cancel1'];
        }
        Code::send(200, $order);
    }

    //订单列表：作为卖家
    public function selllist()
    {
        $input = input();
        $order = model('Order')->selllist($input['uuid']);
        
        foreach ($order as $k =>$v) {
            $res = self::orderAction($v);
            $order[$k]['action'] = $res['action2'];
            $order[$k]['message'] = $res['message'];
            $order[$k]['cancel'] = $res['cancel2'];
        }
        Code::send(200, $order);
    }

    public function orderAction($v)
    {
        $result['action1'] = '';
        $result['action2'] = '';
        $result['message'] = '';
        $result['cancel1'] = 0;
        $result['cancel2'] = 0;
        if ($v['status'] == 9 && $v['status2'] == 9)
            $result['message'] = '交易订单已完成';
        else if ($v['status'] == -1)
            $result['message'] = '买方已取消服务';
        else if ($v['status2'] == -1)
            $result['message'] = '卖方已取消服务';
        else if ($v['status2'] == -2)
            $result['message'] = '交易提前失败';
        else {
            if ($v['status'] == 1) {
                $result['action2'] = 'A确认订单';
                $result['message'] = '等待卖方确认';
                $result['cancel1'] = 1;
                $result['cancel2'] = 1;
            }
            else if ($v['status'] == 5 && $v['status2'] == 5) {
                $result['action1'] = 'a订单成功，上传资料';
                $result['message'] = '订单进行中';
            }
            else if ($v['status'] == 9) {
                $result['action2'] = 'B确认订单完成';
                $result['message'] = '买家已置交易成功，请卖家确认已收到定金等';
            }

        }

        return $result;
    }

    //商品详情
    public function detail()
    {
        $input = input();
        if (empty($input['id'])) exit;
        $order = model('Order')->searchOrder($input['id']);
        if (!empty($order['ppid'])) {
            $order['propety'] = model('Propety')->searchPropety($order['ppid']);
        }
        Code::send(200, $order);
    }

    //卖家接受订单
    public function checkOrder()
    {
        $input = input();
        if (empty($input['id'])) 
            Code::send(999, '参数错误');
        $order = model('Order')->searchOrder($input['id']);
        if ($order['status'] <> 1) 
            Code::send(999, '状态错误1');
        if ($order['target_agent_id'] <> $input['uuid'])
            Code::send(999, '用户错误');
        \Db::transaction(function() use($order) {
            $status = 5;
            $status2 = 5;
            if ($status == 5 && ($order['type'] == 2 || $order['type'] == 3)) {
                $propety = model('Propety')->searchPropety($order['ppid']);
                if ($propety['status'] <> 5) 
                    Code::send(999, '商品在非销售状态');
                $count = $propety['count'] + 1;
                $res = \Db::name('propety')->where(['id' => $propety['id']])->update(['count' => $count, 'update_time' => $_SERVER['REQUEST_TIME']]);
            }
            $res = \Db::name('order')->where(['id' => $order['id']])->update(['status' => $status, 'status2' => $status2, 'update_time' => $_SERVER['REQUEST_TIME']]);
            $res = model('OrderRecord')->insertOrderRecord($order['target_agent_id'], $order, 2, '卖家接受交易订单');

            if (!$res) 
                Code::send(999, 'sql error');
        });
        Code::send(200);
    }

    //买家置成功
    public function succByBuy()
    {  
        $input = input();
        if (empty($input['id']))
            Code::send(999, '参数错误');
        $order = model('Order')->searchOrder($input['id']);
        if ($order['agent_id'] <> $input['uuid'])
            Code::send(999, '用户错误');
        
        \Db::transaction(function() use($order) {
            $res = \Db::name('order')->where(['id' => $order['id']])->update(['status' => 9, 'update_time' => $_SERVER['REQUEST_TIME']]);
            $res = model('OrderRecord')->insertOrderRecord($order['agent_id'], $order, 1, '买家置成功交易订单');

            if (!$res) 
                Code::send(999, 'sql error');
        });
        Code::send(200);
    }

    //卖家置成功
    public function succBySell()
    {  
        $input = input();
        if (empty($input['id']))
            Code::send(999, '参数错误');
        $order = model('Order')->searchOrder($input['id']);
        if ($order['status'] <> 9) 
            Code::send(999, '状态错误9');
        if ($order['target_agent_id'] <> $input['uuid'])
            Code::send(999, '用户错误');

        \Db::transaction(function() use($order) {
            //卖家置成功，买家卖家服务单经纪人同时成功，其他买家订单和服务单失败，商品置成功
            $res = \Db::name('propety')->where(['id' => $order['ppid']])->update(['status' => 9, 'update_time' => $_SERVER['REQUEST_TIME']]);
            $res = \Db::name('apply')->where(['id' => $order['target_apply_id']])->update(['status2' => 9, 'update_time' => $_SERVER['REQUEST_TIME']]);
            $order_list = \Db::name('order')->where(['target_apply_id' => $order['target_apply_id']])->where('status', '>', 0)->select();

            foreach ($order_list as $o) {
                $status2 = ($o['id'] == $order['id']) ? 9 : -2;
                $res = \Db::name('apply')->where(['id' => $o['apply_id']])->update(['status2' => $status2, 'update_time' => $_SERVER['REQUEST_TIME']]);
                $res = \Db::name('order')->where(['id' => $o['id']])->update(['status2' => $status2, 'update_time' => $_SERVER['REQUEST_TIME']]);
            }
            $res = model('OrderRecord')->insertOrderRecord($order['target_agent_id'], $order, 2, '卖家置成功交易订单');

            if (!$res) 
                Code::send(999, 'sql error');
        });
        Code::send(200);
    }

    //买家置失败
    public function failByBuy()
    {  
        $input = input();
        if (empty($input['id'])) 
            Code::send(999, '参数错误');
        $order = model('Order')->searchOrder($input['id']);
        if ($order['agent_id'] <> $input['uuid'])
            Code::send(999, '用户错误');

        \Db::transaction(function() use($order) {
            $res = self::failOrder($order, 0);

            if (!$res) 
                Code::send(999, 'sql error');
        });
        Code::send(200);
    }

    //卖家置失败
    public function failBySell()
    {  
        $input = input();
        if (empty($input['id'])) 
            Code::send(999, '参数错误');
        $order = model('Order')->searchOrder($input['id']);
        if ($order['target_agent_id'] <> $input['uuid'])
            Code::send(999, '用户错误');

        \Db::transaction(function() use($order) {
            $res = self::failOrder($order, 1);

            if (!$res) 
                Code::send(999, 'sql error');
        });
        Code::send(200);
    }

    public function failOrder($order, $utype)
    {  
        //买家置失败，买家服务申请也失败
        if ($utype == 0){
            $res = \Db::name('order')->where(['id' => $order['id']])->update(['status' => -1, 'update_time' => $_SERVER['REQUEST_TIME']]);
            $res = \Db::name('apply')->where(['id' => $order['apply_id']])->update(['status2' => -1, 'update_time' => $_SERVER['REQUEST_TIME']]);
            $res = model('OrderRecord')->insertOrderRecord($order['agent_id'], $order, 1, '买家取消交易订单');
        }
        //卖家置失败，买家服务申请也失败
        else if ($utype == 1){
            $res = \Db::name('order')->where(['id' => $order['id']])->update(['status2' => -1, 'update_time' => $_SERVER['REQUEST_TIME']]);
            $res = \Db::name('apply')->where(['id' => $order['apply_id']])->update(['status2' => -2, 'update_time' => $_SERVER['REQUEST_TIME']]);
            $res = model('OrderRecord')->insertOrderRecord($order['target_agent_id'], $order, 2, '卖家取消交易订单');
        }

        return $res;
    }
}