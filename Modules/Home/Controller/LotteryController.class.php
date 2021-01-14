<?php

namespace Home\Controller;

class LotteryController extends CommonController {
	
	protected function _initialize(){
	
		parent::_initialize();
	}
	//进行中
	public function index(){
	
	    $per_page = 10;
	    $page = I('post.page',1);
	    
	    $offset = ($page - 1) * $per_page;
	    
	    $sql = 'select sg.state,sg.begin_time,sg.end_time,g.goods_id,g.name,g.quantity,g.pinprice,g.price,g.image from  '.C('DB_PREFIX')."lottery_goods as sg , ".C('DB_PREFIX')."goods as g 
	        where sg.state =1 and sg.goods_id = g.goods_id and g.status =1 and g.quantity >0 and sg.begin_time < ".time()." and sg.end_time > ".time()." order by sg.begin_time asc limit {$offset},{$per_page}";
	    
		$list = M()->query($sql);
		
		foreach ($list as $k => $v) {
		    $list[$k]['image']=resize($v['image'], C('common_image_thumb_width'), C('common_image_thumb_height'));
		}
		
		
		$this->list = $list;
		
		if($page > 1) {
		    $result = array('code' => 0);
		    if(!empty($list)) {
		        $result['code'] = 1;
		        $result['html'] = $this->fetch('Widget:lottery_ajax_on_fetch');
		    }
		    echo json_encode($result);
		    die();
		}
		
		$this->display('index');	
	}	
	
	//未开始
	public function wait()
	{
	    $per_page = 10;
	    $page = I('post.page',1);
	     
	    $offset = ($page - 1) * $per_page;
	     
	    $sql = 'select sg.state,sg.begin_time,sg.end_time,g.goods_id,g.name,g.quantity,g.pinprice,g.price,g.image from  '.C('DB_PREFIX')."lottery_goods as sg , ".C('DB_PREFIX')."goods as g
	    where sg.state =1 and sg.goods_id = g.goods_id and g.status =1 and g.quantity >0 and sg.begin_time > ".time()." order by sg.begin_time asc limit {$offset},{$per_page}";
	   
	    $list = M()->query($sql);
	    
	    foreach ($list as $k => $v) {
	        $list[$k]['image']=resize($v['image'], C('spike_thumb_width'), C('spike_thumb_height'));
	    }
	    
	    
	    $this->list = $list;
	    
	   
        $result = array('code' => 0);
        if(!empty($list)) {
            $result['code'] = 1;
            $result['html'] = $this->fetch('Widget:lottery_ajax_wait_fetch');
        }
        echo json_encode($result);
        die();
	   
	}
	public function over()
	{
	    $per_page = 10;
	    $page = I('post.page',1);
	    
	    $offset = ($page - 1) * $per_page;
	    
	    $sql = 'select sg.state,sg.begin_time,sg.is_open_lottery,sg.end_time,g.goods_id,g.name,g.quantity,g.pinprice,g.price,g.image from  '.C('DB_PREFIX')."lottery_goods as sg , ".C('DB_PREFIX')."goods as g
	    where sg.state =1 and sg.goods_id = g.goods_id  and sg.end_time < ".time()."  order by sg.begin_time asc limit {$offset},{$per_page}";
	    
	    $list = M()->query($sql);
	     
	    foreach ($list as $k => $v) {
	        $list[$k]['image']=resize($v['image'], C('spike_thumb_width'), C('spike_thumb_height'));
	    }
	     
	     
	    $this->list = $list;
	     
	    
	    $result = array('code' => 0);
	    if(!empty($list)) {
	        $result['code'] = 1;
	        $result['html'] = $this->fetch('Widget:lottery_ajax_over_fetch');
	    }
	    echo json_encode($result);
	    die();
	}
	
	public function getLuckUrl(){
	   $luck_info = M("lionfish_comshop_lottery_category")->field('id')->where(array('status'=>1))->find();
	   if(empty($luck_info)){
	       $result['code'] = 1;
	       $result['msg'] = '没有活动';
	       $result['url'] = '/community/pages/lucky/lucky?id=6';
	       echo json_encode($result);
	       die();
	   }else{
	       $result['code'] = 0;
	       $result['msg'] = '有活动';
	       $result['url'] = '/community/pages/lucky/lucky?id='.$luck_info['id'];
	       echo json_encode($result);
	       die();
	   }
	}	
}