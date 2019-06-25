<?php
namespace app\api\controller;

use think\Controller;
use app\common\Cache;
use app\common\Code;
use app\common\Wechat;

class Payment extends Controller
{

    //账户流水
    public function paymentlist()
    {
        $input = input();
        $record = model('UserPayment')->paymentlist($input['uuid']);
        Code::send(200, $record);
    }

    //支付费用
    public function pay($apply, $type, $money)
    {
        if ($money <= 0) 
            return 'money < 0';
        $account = model('UserAccount')->searchAccount($apply['user_id']);
        if ($account['gift'] > $money) {
            $gift_money = $money;
            $amount_money = 0;
            $payment_money = 0;
        }
        else if ($account['gift'] + $account['amount'] > $money) {
            $gift_money = $account['gift'];
            $amount_money = $money - $account['gift'];
            $payment_money = 0;
        }
        else {
            $gift_money = $account['gift'];
            $amount_money = $account['amount'];
            $payment_money = $money - $account['gift'] - $account['amount'];
        }

        $order_no = makeOrder();
        if ($payment_money == 0) {
            $res = self::succPayment($account->toArray(), $type, $order_no, $gift_money, $amount_money, $payment_money, $apply['title']);
        }
        else {
            if ($type == 1) {
                $payment['title'] = "付费服务";
            }
            else if ($type == 2) {
                $payment['title'] = "服务结束";
            }
            $payment['uuid'] = $apply['user_id'];
            $payment['money'] = $payment_money;
            $payment['amount_money'] = $amount_money;
            $payment['gift_money'] = $gift_money;
            $payment['apply_id'] = $apply['id'];
            $payment['type'] = $type;
            $payment['order_no'] = $order_no;
            $payment['title'] .= "：<" . $apply['title'] . ">" . $payment_money . "元";
            $res = model('UserPayment')->insertPayment($payment);
            if ($res) {
                $wechat = new Wechat();
                $notify_url = "https://" . $_SERVER['SERVER_NAME'] . "/api/payment/notify";
                $res = $wechat->unifiedorder($payment['order_no'], $payment['money'], $notify_url);
            }
        }
        return $res;
    }

    //微信支付后回调
    public function notify()
    {
        $wechat = new Wechat();
        $cache = new cache();
        $xml = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");
        if (empty($xml)) 
            Code::send(999, '回调错误');
        $notify = $wechat->FromXml($xml);
        if (!empty($notify['return_msg'])) 
            Code::send(999, $notify['return_msg']);
        //log
        $cache->set($notify, 'log_notify_payment', $notify['order_no'], true, 360000);

        $sign = $wechat->getSign($notify);
        if ($sign !== $notify('sign')) 
            Code::send(999, '签名错误');

        //锁线程
        $cache->lock('payment_notify_' . $notify['openid']);
        $payment = model('Payment')->searchPayment($notify['order_no']);
        if (!$payment) 
            Code::send(999, '订单号不存在');
        if ($payment['status'] <> 1) 
            Code::send(999, '订单已处理或失效');

        if ($notify['result_code'] == 'FAIL') {
            \Db::transaction(function() use($payment) {
                $res = db('user_payment')->where(['id' => $payment['id']])->update(['status' => -1, 'transaction_id' => $notify['transaction_id'], 'update_time' => $_SERVER['REQUEST_TIME']]);
            });
        }
        else {
            //成功
            \Db::transaction(function() use($payment) {
                $account = model('UserAccount')->searchAccount($payment['uuid']);
                $apply = model('Apply')->searchApply($payment['apply_id']);
                $res = self::succPayment($account->toArray(), $payment['type'], $payment['order_no'], $payment['gift_money'], $payment['amount_money'], $payment['money'], $apply['title']);
                if ($payment['type'] == 1) {
                    $status = 5;
                    if ($apply['status2'] == 5 && ($apply['type'] == 2 || $apply['type'] == 3)) {
                        $propety = model('Propety')->searchPropety($apply['ppid']);
                        $order = model('Order')->insertOrder($apply, $propety);
                    }
                    $res = db('apply')->where(['id' => $apply['id']])->update(['status' => $status, 'update_time' => $_SERVER['REQUEST_TIME']]);
                    $res = model('ApplyRecord')->insertApplyRecord($apply['user_id'], $apply, 1, '会员支付提前服务费');
                }
                else if ($payment['type'] == 2) {
                    $status = 9;
                    $res = db('apply')->where(['id' => $apply['id']])->update(['status' => $status, 'update_time' => $_SERVER['REQUEST_TIME']]);
                    $res = model('ApplyRecord')->insertApplyRecord($apply['user_id'], $apply, 1, '会员置成功服务，支付服务费（微信支付）');
                }

                $res = db('user_payment')->where(['id' => $payment['id']])->update(['status' => 9, 'transaction_id' => $notify['transaction_id'], 'update_time' => $_SERVER['REQUEST_TIME']]);
            });
        }

        $cache->unlock('payment_notify_' . $notify['openid']);
        //reply
        $replynotify['return_code'] = 'SUCCESS';
        $wechat->replynotify($replynotify);
    }


    public function succPayment($account, $type, $order_no, $gift_money, $amount_money, $payment_money, $title = '')
    {
        $account_record = $account;
        if ($type == 1) {
            $account_record['title'] = "付费服务";
        }
        else if ($type == 2) {
            $account_record['title'] = "服务结束";
        }

        if ($gift_money > 0) {
            $account_record['change'] = $gift_money;
            $account_record['gift'] -= $gift_money;
            $account_record['type'] = $type;
            $account_record['order_no'] = $order_no;
            $account_record['title'] .= "：<" . $title . ">-积分支付：" . $gift_money . "元";
            $res = model('UserAccountRecord')->insertAccountRecord($account_record);
        }

        if ($amount_money > 0) {
            $account_record['change'] = $amount_money;
            $account_record['amount'] -= $amount_money;
            $account_record['type'] = $type;
            $account_record['order_no'] = $order_no;
            $account_record['title'] .= "：<" . $title . ">-余额支付：" . $amount_money . "元";
            $res = model('UserAccountRecord')->insertAccountRecord($account_record);
        }

        if ($payment_money > 0) {
            $account_record['change'] = $payment_money;
            $account_record['payment'] -= $payment_money;
            $account_record['type'] = $type;
            $account_record['order_no'] = $order_no;
            $account_record['title'] .= "：<" . $title . ">-微信支付：" . $payment_money . "元";
            $res = model('UserAccountRecord')->insertAccountRecord($account_record);
        }
        $account['amount'] -= $amount_money;
        $account['gift'] -= $gift_money;
        $account['payment'] += $payment_money;
        $res = model('UserAccount')->updateAccount($account);
        return $res;
    }

}