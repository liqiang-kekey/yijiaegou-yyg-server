<?php
require_once dirname(__FILE__) ."/lib/WxPay.Api.php";
require_once dirname(__FILE__) .'/lib/WxPay.Notify.php';
require_once dirname(__FILE__) .'/log.php';


$lib_path = dirname(dirname( dirname(__FILE__) )).'/Lib/';

$data_path = dirname( dirname(dirname( dirname(__FILE__) )) ).'/Data/wxpaylogs/'.date('Y-m-d')."/";

RecursiveMkdir($data_path);




//初始化日志
//\Think\Log::record("begin notify222");

class PayPackNotifyCallBack extends WxPayNotify
{
    //查询订单
    public function Queryorder($transaction_id)
    {
        global $INI;
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = WxPayApi::orderQuery($input);
        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            //DO
            return true;
        }
        return false;
    }
    public function getOrdrId(){
        //其中：YYYY=年份，MM=月份，DD=日期，HH=24格式小时，II=分，SS=秒，NNNNNNNN=随机数，CC=检查码
        //飞鸟慕鱼博客
        @date_default_timezone_set("PRC");
        while(true) {
            //订单号码主体（YYYYMMDDHHIISSNNNNNNNN）
            $order_id_main = date('YmdHis') . rand(10000000, 99999999);
            //订单号码主体长度
            $order_id_len = strlen($order_id_main);
            $order_id_sum = 0;
            for ($i = 0; $i < $order_id_len; $i++) {
                $order_id_sum += (int)(substr($order_id_main, $i, 1));
            }
            //唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）
            $order_id = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100, 2, '0', STR_PAD_LEFT);
            return $order_id;
        }

    }
    public function crtArr($start, $length){
        $arr = array(); //声明一个空的数组
        for($i = $start; $i <= $length; $i ++){
            $arr[] = $i*2; // 向数组中添加值
        }
        return $arr;
    }



    //重写回调处理函数
    public function NotifyProcess($data, &$msg)
    {
        global $_W;
        global $_GPC;
        //global $_W;

        $data_path = dirname( dirname(dirname( dirname(__FILE__) )) ).'/Data/wxpaylogs/'.date('Y-m-d')."/";

        RecursiveMkdir($data_path);

        $file = $data_path.date('Y-m-d').'.txt';
        $handl = fopen($file,'a');
        fwrite($handl,"Queryorder");
        fwrite($handl,"call back:" . json_encode($data));
        fwrite($handl,"121套餐小程序开始查询支付：".$data['out_trade_no'].'============');
        fclose($handl);
        //\Think\Log::record("call back:" . json_encode($data));

        if(!array_key_exists("transaction_id", $data)){
            $msg = "输入参数不正确";
            return false;
        }
        //查询订单，判断订单真实性
        if(!$this->Queryorder($data["transaction_id"])){
            $msg = "订单查询失败";
            return false;
        }else {
            
            $total_fee = $data['total_fee'];
            $transaction_id = $data['transaction_id'];
            $out_trade_no = $data['out_trade_no'];
            $order_all = M('lionfish_comshop_package_order')->where( array('out_trade_no' => $out_trade_no ) )->find();
            if( in_array($order_all['status'], array(1,2)) ){
                $msg = "付款成功";
                return true;
            }
            $o = array();
            $o['status'] = 1;
            $o['paytime']=time();
            $o['pay_money'] = $total_fee / 100;
            $o['transaction_id'] = $transaction_id;
            $result = M('lionfish_comshop_package_order')->where( array('out_trade_no' => $out_trade_no) )->save($o);
            if($result){
                // $fenxiao_model = D('Home/Commission');//D('Home/Fenxiao');
                // $fenxiao_model->ins_member_package_order($order_all['member_id'],$order_all['id'],0,$order_all['goods_id'],$order_all['agent_id']);
                //$coin = M('lionfish_comshop_coin')->where( array('card_id' => $order_all['goods_id']) )->select();
                $coin = M('lionfish_comshop_coin')->where( array('card_id' => $order_all['goods_id'],'status'=>1) )->select();
                foreach ($coin as $key=>$val){
                    $arr = $this->crtArr(1,$val['num']);
                    foreach ($arr as  $k=>$v){
                        $data = [
                            'card_no'=>'CARD'.$this->getOrdrId(),
                            'goods_name'=>$val['goods_name'],
                            'card_name'=>$order_all['goods_name'],
                            'addtime'=>time(),
                            'goods_img'=>$val['goods_img'],
                            'member_id'=>$order_all['member_id'],
                            'openid'=>$order_all['openid'],
                            'package_id'=>$order_all['id'],
                            'goods_id'=>$val['gid']
                        ];
                        M('lionfish_comshop_coin_order')->add($data);
                    }
                }
            }
            return true;

        }
    }
}

