<?php
namespace Home\Controller;

class MemberShareController extends CommonController {
    public function getMemberShare(){
        $json['code'] =0;
        $json['msg']='签名验证失败!!!';
        $data = file_get_contents("php://input");
        $tmp = new RsaController('web/key/pri.key','web/key/pub.key');

        $data = json_decode($data,true);
        $sign = $data['sign'];
        $datas = $this->array_remove_by_key($data,'sign');
        //1.按字段排序
        ksort($datas);
        // 2.拼接字符串数据 &
        $string = http_build_query($datas);
        $verify = $tmp->verify($string,$sign);
        if(!isset($datas['unionid']) || !isset($datas['source'])){
            $json['msg']='参数缺失!!!';
            $json['code'] =0;
            echo json_encode($json);
            die();
        }
        if($verify){
            $list = M('lionfish_comshop_member')
                ->field('member_id,openid,unionid,share_id,agentid,username,avatar')
                ->where( array('unionid' => $datas['unionid']) )
                ->find();
            if($list){
                if($list['agentid']){
                    $parent_info = M('lionfish_comshop_member')
                        ->field('member_id,openid,unionid,share_id,agentid,username,avatar')
                        ->where( array('member_id' => $list['agentid']) )
                        ->find();
                    $list['parent'] = $parent_info;
                }
//                $child = M('lionfish_comshop_member')
//                    ->field('member_id,openid,unionid,share_id,agentid,username,avatar')
//                    ->where( array('agentid' => $list['member_id']) )
//                    ->select();
//                $list['child'] = $child;
                $json['msg']='请求成功!';
            }else{
                $json['msg']='无此用户!';
            }
            $json['code'] =1;
            $json['data'] = $list;

            echo json_encode($json);
            die();
        }else{
            $json['code'] =0;
            $json['msg']='签名验证失败!!!';
            echo json_encode($json);
            die();
        }
    }
    /**
     * 根据key删除数组中指定元素
     * @param  array  $arr  数组
     * @param  string/int  $key  键（key）
     * @return array
     */
    public function array_remove_by_key($arr, $key){
        if(!array_key_exists($key, $arr)){
            return $arr;
        }
        $keys = array_keys($arr);
        $index = array_search($key, $keys);
        if($index !== FALSE){
            array_splice($arr, $index, 1);
        }

        return $arr;
    }
    public function countMemberSharePrice(){
        $json['code'] =0;
        $json['msg']='签名验证失败!!!';
        $data = file_get_contents("php://input");
        $tmp = new RsaController('web/key/pri.key','web/key/pub.key');

        $data = json_decode($data,true);
        $sign = $data['sign'];
        $datas = $this->array_remove_by_key($data,'sign');
        //1.按字段排序
        ksort($datas);
        // 2.拼接字符串数据 &
        $string = http_build_query($datas);
        $verify = $tmp->verify($string,$sign);
        if(!isset($datas['unionid']) || !isset($datas['source'])){
            $json['msg']='参数缺失!!!';
            $json['code'] =0;
            echo json_encode($json);
            die();
        }
        if($datas['goodsprice'] < 1){
            $json['msg']='价格不能小于1!!!';
            $json['code'] =0;
            echo json_encode($json);
            die();
        }
        \Think\Log::write('小游戏购买土鸡接口提供的数据:__'.serialize($datas),'WARN');
        if($verify){
            $fenxiao_model = D('Home/Commission');//D('Home/Fenxiao');
            $result = $fenxiao_model->mimigames_member_commiss_order($datas['unionid'],$datas['goodsprice'],$datas['avatar'],$datas['goodsname'],$datas['goodsimage'],$datas['goodsnum'],$datas['username'],$datas['buytime']);

            echo json_encode($result);
            die();
        }
    }
}