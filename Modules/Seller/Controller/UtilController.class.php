<?php

namespace Seller\Controller;

class UtilController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
		
		$this->full       = intval($_GPC['full']);
        $this->platform   = trim($_GPC['platform']);
        $this->defaultUrl = trim($_GPC['url']);
        $this->allUrls    = array(
            array(
                'name' => '商城页面',
                'list' => array(
                    array('name' => '商城首页', 'url' => '/community/pages/index/index', 'url_wxapp' => '/community/pages/index/index'),
                    array('name' => '购物车', 'url' => '/community/pages/order/shopCart', 'url_wxapp' => '/community/pages/order/shopCart'),
                    array('name' => '团长申请页面', 'url' => '/community/moduleA/groupCenter/apply', 'url_wxapp' => '/community/moduleA/apply'),
					array('name' => '团长申请介绍页面', 'url' => '/community/moduleA/groupCenter/recruit', 'url_wxapp' => '/community/moduleA/groupCenter/recruit'),
					array('name' => '会员表单信息收集页面', 'url' => '/community/pages/form/apply', 'url_wxapp' => '/community/pages/form/apply'),
					
					array('name' => '分类页', 'url' => '/community/pages/type/index', 'url_wxapp' => '/community/pages/type/index'),
                    array('name' => '余额充值', 'url' => '/community/pages/user/charge', 'url_wxapp' => '/community/pages/user/charge'),

					array('name' => '视频商品列表', 'url' => '/community/moduleA/video/index', 'url_wxapp' => '/community/moduleA/video/index'),
				
				),
            ),
			/**
            array(
                'name' => '商品属性',
                'list' => array(
                    array('name' => '分类搜索', 'url' => '/community/pages/goods/search', 'url_wxapp' => '/community/pages/goods/search'),
                ),
            ),
			**/
            array(
                'name' => '会员中心',
                'list' => array(
                    array('name' => '会员中心', 'url' => '/community/pages/user/me', 'url_wxapp' => '/community/pages/user/me'),
                    array('name' => '订单列表', 'url' => '/community/pages/order/index', 'url_wxapp' => '/community/pages/order/index'),
					
					array('name' => '关于我们', 'url' => '/community/pages/user/articleProtocol?about=1', 'url_wxapp' => '/community/pages/user/articleProtocol?about=1'),
                    array('name' => '常见帮助', 'url' => '/community/pages/user/protocol', 'url_wxapp' => '/community/pages/user/protocol'),
                    array('name' => '会员套餐', 'url' => '/community/pages/packages/index', 'url_wxapp' => '/community/pages/packages/index'),
                    array('name' => 'VIP页面', 'url' => '/community/moduleA/pin/vip', 'url_wxapp' => '/community/moduleA/pin/vip'),
                    array('name' => '兑换券页面', 'url' => '/community/pages/coin/index', 'url_wxapp' => '/community/pages/coin/index'),
				   // array('name' => '订单列表', 'url' => '/community/pages/order/pintuan', 'url_wxapp' => '/community/pages/order/pintuan'),
                   // array('name' => '拼团列表', 'url' => '/community/pages/order/pintuan', 'url_wxapp' => '/community/pages/order/pintuan'),
                   // array('name' => '我的收藏', 'url' => '/community/pages/dan/myfav', 'url_wxapp' => '/community/pages/dan/myfav'),
                   // array('name' => '我的优惠券', 'url' => '/community/pages/dan/quan', 'url_wxapp' => '/community/pages/dan/quan'),

                ),
            ),
			array(
                'name' => '其他',
                'list' => array(
                    array('name' => '商圈', 'url' => '/community/pages/business/index', 'url_wxapp' => '/community/pages/business/index'),
                    array('name' => '专题列表', 'url' => '/community/moduleA/special/list', 'url_wxapp' => '/community/pages/special/list'),
                    array('name' => '拼团首页', 'url' => '/community/moduleA/pin/index', 'url_wxapp' => '/community/moduleA/pin/index'),
					array('name' => '付费会员首页', 'url' => '/community/moduleA/vip/upgrade', 'url_wxapp' => '/community/moduleA/vip/upgrade'),
					array('name' => '积分签到', 'url' => '/community/moduleA/score/signin', 'url_wxapp' => '/community/moduleA/score/signin'),
					
                    array('name' => '整点秒杀', 'url' => '/community/moduleA/seckill/list', 'url_wxapp' => '/community/moduleA/seckill/list'),
                    array('name' => '优惠券', 'url' => '/community/pages/quan/quan', 'url_wxapp' => '/community/pages/quan/quan'),
				)
            )
        );
	}
	
	
	public function selecturl()
    {

        $platform = $this->platform;
        $full     = $this->full;

        $allUrls = $this->allUrls;

         $this->display();

    }
	
	public function query()
    {
        
        $type     = I('request.type');
        $kw       = I('request.kw');
        $full     = I('request.full');
        $platform = I('request.platform');
		$this->type = $type;
		$this->kw = $kw;
		$this->full = $full;
		$this->platform = $platform;
		
        if (!empty($kw) && !empty($type)) {

            if ($type == 'good') {
				
                $list = M()->query('SELECT id,goodsname as title,productprice,price as marketprice,sales,type FROM ' .
                    C('DB_PREFIX') . 'lionfish_comshop_goods WHERE  grounding=1 and total > 0 
					AND goodsname LIKE "'.'%' . $kw . '%'.'" ');

                if (!empty($list)) {
                    foreach ($list as &$val) {
                        
						$thumb = M('lionfish_comshop_goods_images')->where( array('goods_id' => $val['id']) )->order('id asc')->find();
					
                        $val['thumb'] = tomedia($thumb['image']);
                    }
                }

                //$list = set_medias($list, 'thumb');
                //thumb
            } else if ($type == 'article') {
                
				$list = M('lionfish_comshop_article')->field('id,title')->where( " (title LIKE '%".$kw."%' or id like '%".$kw."%' ) and enabled=1" )->select();
            } else if ($type == 'coupon') {
                
            } else if ($type == 'groups') {
               
            } else if ($type == 'sns') {
                
            } else if ($type == 'url') {
            	$list = $this->searchUrl($this->allUrls, "name", $kw);
			} else if ($type == 'special') {
                
				$list = M('lionfish_comshop_special')->field('id,name')->where("name LIKE '%{$kw}%' and enabled=1  ")->select();
            } 
			else if ($type == 'category') {
                	
				$list = M('lionfish_comshop_goods_category')->field('id,name')->where( " name LIKE '%{$kw}%' " )->select();
            }
			else if ($type == 'creditshop') {

            }else if ($type == 'poster'){
                $list = M('lionfish_comshop_posterlist')->field('id,name')->where( " name LIKE '%{$kw}%' " )->select();

            }elseif($type == 'lottery'){
                $list = M('lionfish_comshop_lottery_category')->field('id,name')->where( " name LIKE '%{$kw}%' " )->select();
            }elseif($type == 'business'){
                $list = M('lionfish_comshop_business')->field('id,title')->where( " title LIKE '%{$kw}%' " )->select();
            }else{

            }
        }
		
		$this->list = $list;

        $this->display('Util/selecturl_tpl');
    }
	
	
	
}
?>