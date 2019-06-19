<?php
namespace app\api\controller;

use think\Controller;
use app\common\Cache;
use app\common\Code;
use app\common\Wechat;

class Transfers extends Controller
{

    //账户流水
    public function transferslist()
    {
        $input = input();
        $record = model('UserTransfers')->transferslist($input['uuid']);
        Code::send(200, $record);
    }

    //提现到微信钱包
    public function trans()
    {
        $input = input();
        if (empty($input['money']) || $input['money'] <= 0) exit;
        $money = $input['money'] * 100;     //单位：分
        $account = model('UserAccount')->searchAccount($input['uuid']);
        if ($money < $account['amount'])
            Code::send(999, '余额不足');
        $order_no = makeOrder();
        \Db::transaction(function(){
            $transfers['uuid'] = $apply['user_id'];
            $transfers['money'] = $money;
            $transfers['type'] = 1;
            $transfers['order_no'] = $order_no;
            $transfers['title'] = "提现到微信钱包：" . ($payment_money/100) . "元";
            $res = model('UserTransfers')->insertTransfers($transfers);

            $account['amount'] -= $money;
            $account['frozen'] += $frozen;
            $res = model('UserAccount')->updateAccount($account);
        });
        Code::send(200, $res);
    }

    public function cancel()
    {
        $input = input();
        if (empty($input['order_no'])) exit;
        $transfers = model('UserTransfers')->searchTransfers($input['order_no']);
        $res = db('user_transfers')->where(['id' => $transfers['id']])->update(['status' => -1, 'update_time' => $_SERVER['REQUEST_TIME']]);
        Code::send(200, $res);
    }

}