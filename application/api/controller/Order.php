<?php
namespace app\api\controller;

use think\Controller;
use app\common\Cache;
use app\common\Code;
use app\common\Wechat;

class Order extends Controller
{
    //订单列表：作为买家
    public function buylist()
    {
        $input = input();
        if (empty($input['uuid'])) exit;
        $order = model('Order')->buylist($input['uuid']);
        Code::send(200, $order);
    }

    //订单列表：作为卖家
    public function selllist()
    {
        $input = input();
        if (empty($input['uuid'])) exit;
        $order = model('Order')->selllist($input['uuid']);
        Code::send(200, $order);
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
            $apply_ids = \Db::name('order')->where(['target_apply_id' => $order['target_apply_id'], 'status2' => 5])->column('apply_id');
            print_r($apply_ids);
            foreach ($apply_ids as $id) {
                $status2 = ($id == $order['apply_id']) ? 9 : -2;
                $res = \Db::name('apply')->where(['id' => $id])->update(['status2' => $status2, 'update_time' => $_SERVER['REQUEST_TIME']]);
                $res = \Db::name('order')->where(['apply_id' => $id, 'status2' => 5])->update(['status2' => $status2, 'update_time' => $_SERVER['REQUEST_TIME']]);
                print_r($res);
            }

            if (!$res) 
                Code::send(999, 'sql error');
        });
        Code::send(200);
    }

    //买家置失败
    public function failByBuy()
    {  
        $input = input();
        if (empty($input['id'])) exit;
        $order = model('Order')->searchOrder($input['id']);
        $res = self::failOrder($order, 0);
        Code::send(200, $res);
    }

    //买家置失败
    public function failBySell()
    {  
        $input = input();
        if (empty($input['id'])) exit;
        $order = model('Order')->searchOrder($input['id']);
        $res = self::failOrder($order, 1);
        Code::send(200, $res);
    }

    public function failOrder($order, $utype)
    {  
        \Db::transaction(function(){
            //买家置失败
            if ($utype == 0){
                $res = db('order')->where(['id' => $order['id']])->update(['status' => -1, 'update_time' => $_SERVER['REQUEST_TIME']]);
                $res = db('apply')->where(['id' => $order['apply_id']])->update(['status2' => -1, 'update_time' => $_SERVER['REQUEST_TIME']]);
            }
            //卖家置失败
            else if ($utype == 1){
                $res = db('order')->where(['id' => $order['id']])->update(['status2' => -1, 'update_time' => $_SERVER['REQUEST_TIME']]);
                $res = db('apply')->where(['id' => $order['apply_id']])->update(['status2' => -1, 'update_time' => $_SERVER['REQUEST_TIME']]);
            }
        });
        return $res;
    }
}