<?php

namespace Home\Controller;

class AdvertisementController extends CommonController
{

    protected function _initialize()
    {
        parent::_initialize();
    }

    public function get_info()
    {
//         $_GPC = I('request.');
//         $token = $_GPC['token'];
//         $weprogram_token = M('lionfish_comshop_weprogram_token')->field('member_id')->where( array('token' => $token) )->find();
//         $member_id = $weprogram_token['member_id'];
//         if( empty($member_id))
//         {
//             $result = array('code' =>4);
//             echo json_encode($result);
//             die();
//         }
        $advm_list = M('lionfish_comshop_advertisement')->field('id,advmname,thumbimg,link,linktype,appid')->where("enabled=1")->order('displayorder desc')->select();

        if (!empty($advm_list)) {
            foreach ($advm_list as $key => $val) {
                $val['thumbimg'] = tomedia($val['thumbimg']);
                $advm_list[$key] = $val;
            }
        } else {
            $result = array('code' =>1);
            echo json_encode($result);
            die();
        }   
        $result = array('code' => 0, 'data' => $advm_list[0]);
        echo json_encode($result);
        die();
    }

}
