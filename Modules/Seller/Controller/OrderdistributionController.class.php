<?php

namespace Seller\Controller;

class OrderdistributionController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
	}
	
	public function index()
	{
		$pindex    = I('request.page', 1);
        $psize     = 20;

		$keyword = I('request.keyword');
		$this->keyword = $keyword;
		
        if (!empty($keyword)) {
            $condition .= ' and (username like "%'.$keyword.'%" or mobile like "%'.$keyword.'%" )';
        }

		$enabled = I('request.state',-1);
		
        if (isset($enabled) && $enabled >= 0) {
           
            $condition .= ' and state = ' . $enabled;
        } else {
            $enabled = -1;
        }
		$this->enabled = $enabled;
		

		
        $list = M()->query('SELECT * FROM ' . C('DB_PREFIX'). "lionfish_comshop_orderdistribution  
		WHERE 1=1 " . $condition . ' order by id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);
        
		if( !empty($list) )
		{
			foreach( $list as $key => $val )
			{
				$mb_info = M('lionfish_comshop_member')->field('member_id, username as nickname,avatar')->where( array('member_id' => $val['member_id'] ) )->find();
				
				$val['mb_info'] = $mb_info;
				
				$list[$key] = $val;
			}
		}
		
		$total = M('lionfish_comshop_orderdistribution')->where("1=1 ".$condition)->count();
			
        $pager = pagination2($total, $pindex, $psize);

		$this->list = $list;
		$this->pager = $pager;
		
		$this->display();
	}
	
	
	/**
     * 改变状态
     */
    public function change()
    {

        $id = I('request.id');

        //ids
        if (empty($id)) {
			$ids = 	I('request.ids');
            $id = ((is_array($ids) ? implode(',', $ids) : 0));
        }

        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }

        $type  = I('request.type');
        $value = I('request.value');

        if (!(in_array($type, array('state')))) {
            show_json(0, array('message' => '参数错误'));
        }

		$items = M('lionfish_comshop_orderdistribution')->where( array('id' => array('in', $id) ) )->select();
		
        foreach ($items as $item) {
           
			M('lionfish_comshop_orderdistribution')->where( array('id' => $item['id']) )->save( array('state' => $value) );
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

    }
	
	public function deletedistribution()
	{
		 $id = I('request.id');

        if (empty($id)) {
			$ids = I('request.ids');
            $id = (is_array($ids) ? implode(',', $ids) : 0);
        }

		$items = M('lionfish_comshop_orderdistribution')->field('id')->where( array('id' => array('in', $id) ) )->select();

        if (empty($item)) {
            $item = array();
        }

        foreach ($items as $item) {
			if($item['has_send_count'] <= 0)
				M('lionfish_comshop_orderdistribution')->where( array('id' => $item['id']) )->delete();
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	
	public function adddistribution()
	{
		
		if (IS_POST) {
            
			$id = I('post.id');
			
			if( empty($id) )
			{
				$id = 0;
			}
			
			$data = array();
			$data['username'] = I('post.username');
			$data['mobile'] = I('post.mobile');
			$data['member_id'] = I('post.member_id');
			$data['state'] = I('post.state');
			$data['always_address'] = I('post.always_address');
			
			if( empty($data['username']) )
			{
				show_json(0, array('message' => '请填写配送员姓名'));
			}
			if( empty($data['mobile']) )
			{
				show_json(0, array('message' => '请填写手机号'));
			}
			if( empty($data['member_id']) )
			{
				show_json(0, array('message' => '请选择关联会员'));
			}
			
			//检测配送员是否关联过了
			
			$ck_pes = M('lionfish_comshop_orderdistribution')->where( "member_id=".$data['member_id']." and id != " .$id )->find();
			
			if( !empty($ck_pes) )
			{
				show_json(0, array('message' => '该会员已经关联配送员，请选择其他会员'));
			}
			
			
			if( $id > 0 )
			{
				M('lionfish_comshop_orderdistribution')->where( array('id' => $id) )->save( $data );
			}else{
				
				$data['has_send_count'] = 0;
				$data['addtime'] = time();
				
				
				M('lionfish_comshop_orderdistribution')->add( $data );
			}
           
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
        }
		
		$id = I('get.id', 'intval',0);
		
		if( $id > 0 )
		{
			$distribution = M('lionfish_comshop_orderdistribution')->where( array('id' => $id) )->find();
			
			$this->distribution = $distribution;
			
			$saler = M('lionfish_comshop_member')->field('member_id, username as nickname,avatar')->where( array('member_id' => $distribution['member_id'] ) )->find();
			
			$saler['username'] = str_replace("'","",$saler['username']);
			$saler['nickname'] = str_replace("'","",$saler['nickname']);
			
			$this->saler = $saler;
			
		}
		
		$this->display();
	}
}
?>