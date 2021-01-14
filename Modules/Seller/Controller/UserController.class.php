<?php

namespace Seller\Controller;

class UserController extends CommonController{
	
	public function index()
	{
		$condition = '';
		$gpc =  I('request.');
		$this->gpc = $gpc;
		
		$pindex =  I('request.page', 1);
		$psize = 20;

		$keyword = I('request.keyword');
		$this->keyword = $keyword;
		
		if (!empty($keyword)) {
			$condition .= ' and username like '.'"%' . $keyword . '%"';
		}
		
		$level_id = I('request.level_id',0);
		
		
		if( isset($level_id) && !empty($level_id) )
		{
			if($level_id == 'default')
			{
				$level_id = 0;
			}
			$condition .= ' and level_id = '.$level_id;
		}
		$this->level_id = $level_id;
		$groupid = I('request.groupid');
		$this->groupid = $groupid;
		
		//groupid/default
		if( isset($groupid) && !empty($groupid) && $groupid != 'default' )
		{
			$condition .= ' and groupid = '.$groupid;
		}
		
	
		
		if ($gpc['export'] == '1') {
			$list = M()->query('SELECT * FROM ' .C('DB_PREFIX') . "lionfish_comshop_member \r\n                
						WHERE 1=1 " . $condition . ' order by member_id desc');
		}else{
			$list = M()->query('SELECT * FROM ' .C('DB_PREFIX') . "lionfish_comshop_member \r\n                
						WHERE 1=1 " . $condition . ' order by member_id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);
		}
		
	
		$total = M('lionfish_comshop_member')->where("1=1 ". $condition )->count();
		
	
		$level_list = M('lionfish_comshop_member_level')->order('level asc')->select();	
		
		$keys_level = array();
		
		foreach($level_list as $vv)
		{
			$keys_level[$vv['id']] = $vv['levelname'];
		}
		
		$this->level_list = $level_list;
		
		$group_list = M('lionfish_comshop_member_group')->order('id asc')->select();
		
		$keys_group = array();
		
		if( !empty($group_list) )
		{
			foreach($group_list as $vv)
			{
				$keys_group[$vv['id']] = $vv['groupname'];
			}
		}
		
		
		$this->group_list = $group_list;
		
		foreach( $list as $key => $val )
		{
			//ims_ lionfish_comshop_order 1 2 4 6 11
			
					
			$ordercount = M('lionfish_comshop_order')->where( array('order_status_id' => array('in','1,2,4,6,11,14,12,13'),'member_id' => $val['member_id'] )  )->count();	
			$ordermoney = M('lionfish_comshop_order')->where( array('order_status_id' => array('in','1,2,4,6,11,14,12,13'),'member_id' => $val['member_id']) )->sum('total');
			
			if(empty($val['share_id'] )){
				$share_name['username'] = 0 ;
			}else{
				
				$share_name = M('lionfish_comshop_member')->where( array('member_id' => $val['share_id'] ) )->find();
				
			}
			
			// lionfish_community_history
			$community_history = M('lionfish_community_history')->field('head_id')->where( array('member_id' => $val['member_id'] ) )->order('addtime desc')->find();					
								
			if( !empty($community_history) )
			{
				$cur_community_info = M('lionfish_community_head')->where( array('id' => $community_history['head_id'] ) )->find();
				
				$val['cur_communityname'] = $cur_community_info['community_name'];
				
			}	else{
				
				$val['cur_communityname'] = '无';
			}
			
			$val['levelname'] = empty($val['level_id']) ? '普通会员':$keys_level[$val['level_id']];
			$val['groupname'] = empty($val['groupid']) ? '默认分组':$keys_group[$val['groupid']];
			

			$has_shopinfo = M('lionfish_comshop_member_shopinfo')->where( array('member_id' => $val['member_id']) )->find();
			
			if( !empty($has_shopinfo) )
			{
				
				$val['has_shopinfo'] = $has_shopinfo;
			}else{
				$val['has_shopinfo'] = array();
			}
			
			$val['ordercount'] = $ordercount;
			$val['ordermoney'] = $ordermoney;
			$val['share_name'] = $share_name['username'];
			$list[$key] = $val;
		}
		
		if ($gpc['export'] == '1') {
			
			foreach ($list as &$row) {
			    
			    
			    //推荐人  总店
			    $row['share_name'] = $row['share_name'] == '' ? '总店': $row['share_name'];
			    $row['create_time'] = date('Y-m-d H:i:s', $row['create_time']);
			    //状态
			    $row['isblack'] = $row['isblack'] == 1 ? '禁用':'启用';
			    //分销
				$row['comsiss'] = ($row['comsiss_flag'] == 1 && $row['comsiss_state'] == 1) ? '是':'否';
				//订单金额
				$row['ordermoney'] = $row['ordermoney'] == 0 ?  0 : $row['ordermoney'];
			}
			
			//unset($row);
			
			$columns = array(
				array('title' => 'ID', 'field' => 'member_id', 'width' => 12),
				array('title' => '会员名称', 'field' => 'username', 'width' => 12),
				array('title' => '推荐人', 'field' => 'share_name', 'width' => 12),
				array('title' => '小区名称', 'field' => 'cur_communityname', 'width' => 24),
				array('title' => 'openid', 'field' => 'openid', 'width' => 24),
				array('title' => '手机', 'field' => 'telephone', 'width' => 12),
				array('title' => '会员等级', 'field' => 'levelname', 'width' => 12),
			    array('title' => '会员分组', 'field' => 'groupname', 'width' => 12),
			    array('title' => '积分', 'field' => 'score', 'width' => 12),
			    array('title' => '余额', 'field' => 'account_money', 'width' => 12),
			    array('title' => '订单数', 'field' => 'ordercount', 'width' => 12),
			    array('title' => '订单金额', 'field' => 'ordermoney', 'width' => 12),
			    array('title' => '是否分销', 'field' => 'comsiss', 'width' => 12),
				
				array('title' => '注册时间', 'field' => 'create_time', 'width' => 24),
				array('title' => '状态', 'field' => 'isblack', 'width' => 12)
			);
			
			D('Seller/excel')->export($list, array('title' => '会员数据-' . date('Y-m-d-H-i', time()), 'columns' => $columns));

		}
		
		$is_get_formdata = D('Home/Front')->get_config_by_name('is_get_formdata');
		
		$this->is_get_formdata = $is_get_formdata;
		
		$pager = pagination2($total, $pindex, $psize);
		
		$this->pager = $pager;
		$this->list = $list;
		
		$commiss_level = D('Home/Front')->get_config_by_name('commiss_level');
		
		if( empty($commiss_level)  )
		{
			$commiss_level = 0;
		}
		
		$this->commiss_level = $commiss_level;
		
		$this->display();
	}
	
	
	public function shopinfo()
	{
		$member_id = I('get.id');
		
		$shop_info = M('lionfish_comshop_member_shopinfo')->where( array('member_id' => $member_id ) )->find();
		
		$level_list = M('lionfish_comshop_member_level')->order('level asc ')->select();
		
		if( !empty($shop_info['imggroup']) )
		{
			$shop_info['imggroup'] = explode(',' , $shop_info['imggroup']);
			
		}
		
		if( !empty($shop_info['otherimggroup']) )
		{
			$shop_info['otherimggroup'] = explode(',' , $shop_info['otherimggroup']);
		}
		
		
		$this->shop_info = $shop_info;
		
		$this->member_id = $member_id;
		
		
		$list = array(
			array('id' => 'default', 'level_money'=>'0','discount'=>'100' ,'level'=>0,'levelname' => '普通会员', 
						'membercount' => $membercount ) );
					
		
		if( empty($level_list) )
		{
			$level_list = array();
		}
		//$level_list = array_merge($list, $level_list);
		

		$this->level_list = $level_list;
		
		$this->display();
		
	}
	
	public function chose_community()
	{
		$_GPC = I('request.');
		
		$member_id = $_GPC['s_member_id'];
		$head_id = $_GPC['head_id'];
		
		
		D('Seller/community')->in_community_history($member_id, $head_id);
		//load_model_class('community')->in_community_history($member_id, $head_id);
		
		echo json_encode( array('code' => 0) );
		die();
		
	}
	
	
	public function lvconfig ()
	{
		$_GPC = I('request.');
		if (IS_POST) {
			
			$data = ((is_array($_GPC['data']) ? $_GPC['data'] : array()));
			
			D('Seller/Config')->update($data);
			
			show_json(1);
		}
		$data = D('Seller/Config')->get_all_config();
		
		$this->display();
	}
	//后台添加团队长佣金收益流水
	public function profit_flow()
	{
	    $_GPC = I('request.');
		$member_id = $_GPC['id'];
		//
		$condition = ' and member_id='.$member_id;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		//
		$list = M()->query('SELECT * FROM ' . C('DB_PREFIX'). "lionfish_comshop_member_profit_flow \r\n                
						WHERE 1 " . $condition . ' order by id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize );
		
		$total_arr = M()->query('SELECT count(id) as count FROM ' . C('DB_PREFIX') . 'lionfish_comshop_member_profit_flow WHERE 1 ' . $condition );
		
		$total = $total_arr[0]['count'];
		foreach( $list as $key => $val )
		{
			$val['addtime'] = date('Y-m-d H:i:s',$val['addtime'] );		

			$list[$key] = $val;
		}
		
		$pager = pagination2($total, $pindex, $psize);
		
		$this->list = $list;
		$this->pager = $pager;
		//
		$this->display();
	}
	public function recharge_flow ()
	{
		$_GPC = I('request.');
		 
		$member_id = $_GPC['id'];
		
		
		
		$condition = ' and member_id='.$member_id.' and state >0  ';
		
		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		
		$list = M()->query('SELECT * FROM ' . C('DB_PREFIX'). "lionfish_comshop_member_charge_flow \r\n                
						WHERE 1 " . $condition . ' order by id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize );
		
		$total_arr = M()->query('SELECT count(id) as count FROM ' . C('DB_PREFIX') . 'lionfish_comshop_member_charge_flow WHERE 1 ' . $condition );
		
		$total = $total_arr[0]['count'];
		
		foreach( $list as $key => $val )
		{
			$val['add_time'] = date('Y-m-d H:i:s',$val['add_time'] );		
		
			if($val['state'] == 3 || $val['state'] == 4)
			{
							
				$od_info = M('lionfish_comshop_order')->field('order_num_alias')->where( array('order_id' => $val['trans_id'] ) )->find();
				
				if( !empty($od_info) )
				{
					$val['trans_id'] = $od_info['order_num_alias'];
				}
			}
			
			$list[$key] = $val;
		}
		
		
		$pager = pagination2($total, $pindex, $psize);
		
		$this->list = $list;
		$this->pager = $pager;
		
		$this->display();
		
	}
	public function integral_clear(){
        $_GPC = I('request.');

        $type = 'score';


        if (IS_POST) {
            $map['score'] = array('gt',0);//查询积分大于0的用户
            $profile = M('lionfish_comshop_member')->field('member_id')->where($map)->select();
            $data = D('Seller/Config')->get_all_config();


            if(md5($_GPC['clear_password']) != $data['integral_clear']){
                show_json(0, array('message' => '密码错误!'));
            }
            $num = 0;
            $remark = trim($_GPC['remark']);
            if(empty($remark)){
                show_json(0, array('message' => '请输入清零原因!'));
            }
            $changetype = 2;

            if ($type == 'score') {
                //0 增加 1 减少 2 最终积分
                $ch_type = 'system_del';
                foreach ($profile as $k=>$v){
                    D('Seller/User')->sendMemberPointChange($v['member_id'], $num, $changetype, $remark, $ch_type);
                }

            }
            show_json(1,  array('url' => $_SERVER['HTTP_REFERER']));
        }
        $this->gpc = $_GPC;

        $this->display();
    }
	public function integral_flow ()
	{
		$_GPC = I('request.');
		 
		$member_id = $_GPC['id'];

        $export = I('get.export');
		$condition = ' and member_id='.$member_id.' and type >0  ';
		
		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		
		
		$list = M()->query('SELECT * FROM ' . C('DB_PREFIX'). "lionfish_comshop_member_integral_flow                 
						WHERE 1 " . $condition . ' order by id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize );
		
		$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX'). 'lionfish_comshop_member_integral_flow WHERE 1 ' . $condition );
		
		$total = $total_arr[0]['count'];		
		
		foreach( $list as $key => $val )
		{
			$val['add_time'] = date('Y-m-d H:i:s',$val['add_time'] );		
			
			if($val['type'] == 'goodsbuy' || $val['type'] == 'refundorder' || $val['type'] == 'orderbuy')
			{
				$od_info = M('lionfish_comshop_order')->field('order_num_alias')->where( array('order_id' => $val['order_id'] ) )->find();
				
				if( !empty($od_info) )
				{
					$val['order_id'] = $od_info['order_num_alias'];
				}
			}
			
			$list[$key] = $val;
		}
        if($export == 1){

            $list = M()->query('SELECT * FROM ' . C('DB_PREFIX'). "lionfish_comshop_member_integral_flow                 
						WHERE 1 " . $condition );
            foreach( $list as $key => $val )
            {
                $val['add_times'] = date('Y-m-d H:i:s',$val['addtime'] );

                if($val['type'] == 'goodsbuy' || $val['type'] == 'refundorder' || $val['type'] == 'orderbuy')
                {
                    $od_info = M('lionfish_comshop_order')->field('order_num_alias')->where( array('order_id' => $val['order_id'] ) )->find();

                    if( !empty($od_info) )
                    {
                        $val['order_id'] = $od_info['order_num_alias'];
                    }
                }
                if($val['type'] == 'goodsbuy'){
                    $val['type_name'] = '订单付款赠送积分';
                }elseif ($val['type'] == 'refundorder'){
                    $val['type_name'] = '订单付款赠送积分';
                }elseif ($val['type'] == 'system_add'){
                    $val['type_name'] = '后台增加积分';
                }elseif ($val['type'] == 'system_del'){
                    $val['type_name'] = '后台减少积分';
                }elseif ($val['type'] == 'integral_exchange'){
                    $val['type_name'] = '积分兑换商品';
                }elseif ($val['type'] == 'signin_send'){
                    $val['type_name'] = '签到送积分';
                }elseif ($val['type'] == 'from_game'){
                    $val['type_name'] = '小游戏转入积分';
                }else{
                    $val['type_name'] = '下单扣除积分';
                }
                $list[$key] = $val;
            }
            @set_time_limit(0);
            $columns = array(
                array('title' => '用户ID', 'field' => 'member_id', 'width' => 12),
                array('title' => '交易类型', 'field' => 'type_name', 'width' => 12),
                array('title' => '订单号', 'field' => 'order_id', 'width' => 12),
                array('title' => '积分', 'field' => 'score', 'width' => 12),
                array('title' => '所剩积分', 'field' => 'after_operate_score', 'width' => 12),
                array('title' => '备注', 'field' => 'remark', 'width' => 24),
                array('title' => '操作时间', 'field' => 'add_times', 'width' => 24),

            );
            $fileName = date('YmdHis', time());
            D('Seller/Excel')->export($list, array('title' => '积分流水数据'.$fileName, 'columns' => $columns));
        }
		
		$pager = pagination2($total, $pindex, $psize);
        $this->member_id = $member_id;
		$this->list = $list;
		$this->pager = $pager;
		
		$this->display();
		
	}
	
	
	public function editshopinfo()
	{
		$post_data = I('post.');
		
		$up_data = array();
		$up_data['shop_name'] = $post_data['shop_name'];
		$up_data['shop_mobile'] = $post_data['shop_mobile'];
		$up_data['state'] = $post_data['state'];
		
		M('lionfish_comshop_member_shopinfo')->where( array('member_id' => $post_data['member_id'] ) )->save($up_data);
		//oscshop_ lionfish_comshop_member_shopinfo
		
		if($post_data['state'] == 1)
		{
			M('lionfish_comshop_member')->where( array('member_id' =>$post_data['member_id'] ) )->save( array('level_id' => $post_data['level_id'] ) );
		}
		
		show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
	}
	
	//user.changelevel
	public function changelevel()
	{
		$_GPC = I('request.');
		
		$level = $_GPC['level'];
		$ids_arr = $_GPC['ids'];
		$toggle = $_GPC['toggle'];
		
		$ids = implode(',', $ids_arr);

		if($toggle == 'group')
		{
			
			M('lionfish_comshop_member')->where(  "member_id in ({$ids})" )->save( array('groupid' => $level ) );
			
		}else if($toggle == 'level'){
			
			M('lionfish_comshop_member')->where( "member_id in ({$ids})" )->save( array('level_id' =>$level ) );
		}
		
		
		show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
	}
	
	public function config()
	{
		if (IS_POST) {
			$data = I('request.data');
			
			D('Seller/Config')->update($data);
			
			
			show_json(1,  array('url' => $_SERVER['HTTP_REFERER']));
		}
		
		$data = D('Seller/Config')->get_all_config();
		
		$this->data = $data;
		$this->display();
	}
	
	public function usergroup()
	{
		
		$_GPC = I('request.');
		
		$membercount = M('lionfish_comshop_member')->where( array('groupid' => 0) )->count();		
		
		$list = array(
			array('id' => 'default', 'groupname' => '默认分组', 
				'membercount' => $membercount  )
			);
			
		$condition = ' ';
		$params = array(':uniacid' => $_W['uniacid']);
		
		$keyword= '';
		
		if (!(empty($_GPC['keyword']))) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and ( groupname like "%'.$_GPC['keyword'].'%")';
			$keyword = $_GPC['keyword'];
		}

		$alllist = M('lionfish_comshop_member_group')->where( '1'. $condition )->order('id asc')->select();
		
		foreach ($alllist as &$row ) {
			$membercount_arr = M()->query('select count(*) as count from ' . C('DB_PREFIX') . 
				'lionfish_comshop_member where groupid='.$row['id'].' ');
				
			$row['membercount'] = $membercount_arr[0]['count'];	
		}

		unset($row);

		if (empty($_GPC['keyword'])) {
			$list = array_merge($list, $alllist);
		}
		 else {
			$list = $alllist;
		}
		
		$this->keyword = $keyword;
		
		$this->list = $list;
		$this->display();
	}
	
	public function user()
	{
		
	}
	
	
	
	public function userjia()
	{
		$_GPC = I('request.');
		
		
		$condition = '1';
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		

		if (!empty($_GPC['keyword'])) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and username like "%' . $_GPC['keyword'] . '%"';
		}
		
		$list = M('lionfish_comshop_jiauser')->where( $condition )->order('id desc ')->limit( (($pindex - 1) * $psize) . ',' . $psize )->select();
		
		$total = M('lionfish_comshop_jiauser')->where($condition)->count();
		
		$pager = pagination2($total, $pindex, $psize);
		
		
		$this->list = $list;
		$this->pager = $pager;
		$this->gpc = $_GPC;
		$this->display();
	}
	public function userlevel()
	{
		$_GPC = I('request.');
		
		$membercount = M('lionfish_comshop_member')->where( array('level_id' => 0)  )->count();
		
		$list = array(
			array('id' => 'default', 'level_money'=>'0','discount'=>'100' ,'level'=>0,'levelname' => '普通会员', 
						'membercount' => $membercount ) );
						
		$condition = ' 1 ';

		if (!(empty($_GPC['keyword']))) {
			$_GPC['keyword'] = trim($_GPC['keyword']);
			$condition .= ' and ( levelname like "%'.$_GPC['keyword'].'%" )';
		}

		$alllist = M('lionfish_comshop_member_level')->where( $condition )->order('id asc')->select();

		foreach ($alllist as &$row ) {
			
			$row['membercount'] = M('lionfish_comshop_member')->where( "find_in_set(".$row['id'].",level_id)"  )->count();
		}

		unset($row);

		
		if (empty($_GPC['keyword'])) {
			if( empty($alllist) )
			{
				$alllist = array();
			}
			$list = array_merge($list, $alllist);
		}
		 else {
			$list = $alllist;
		}
		
		$this->gpc = $_GPC;
		$this->list = $list;
		
		$this->display();
	}
	
	public function adduserlevel()
	{
		$_GPC = I('request.');
		//ims_ 
		$id = intval($_GPC['id']);
		
		$group = M('lionfish_comshop_member_level')->where( array('id' => $id ) )->find();	

		if (IS_POST) {
			
			$data = array('logo' => trim($_GPC['logo']),'discount' => trim($_GPC['discount']),'level_money' =>  trim($_GPC['level_money']),'levelname' => trim($_GPC['levelname']), 
			'level' => trim($_GPC['level']), 'is_auto_grade' => $_GPC['is_auto_grade'] );
			
			if (!(empty($id))) {
				M('lionfish_comshop_member_level')->where(array('id' => $id))->save( $data );
			}
			 else {
				$id = M('lionfish_comshop_member_level')->add( $data );
			}

			show_json(1, array('url' => U('user/userlevel', array('op' => 'display'))));
		}
		
		$this->id = $id;
		$this->gpc = $_GPC;
		$this->group = $group;
		
		$this->display();
	}
	
	public function adduserjia()
	{
		$_GPC = I('request.');
		
		$id = intval($_GPC['id']);
	
		$group = array();
		if( $id > 0 )
		{
			$group = M('lionfish_comshop_jiauser')->where( array('id' => $id) )->find();
		}	

		if (IS_POST) {
			$data = array('avatar' => trim($_GPC['avatar']),
					'username' => trim($_GPC['username']),'mobile' =>  trim($_GPC['mobile']),'pay_time'=>strtotime(date($_GPC['time'])) );

			if (!(empty($id))) {
				M('lionfish_comshop_jiauser')->where( array('id' => $id) )->save( $data );
			}
			 else {
				 $id = M('lionfish_comshop_jiauser')->add($data);
				
			}

			show_json(1, array('url' => U('user/userjia', array('op' => 'display'))));
		}
		
		$this->group = $group;
		$this->display();
	}
	
	//--begin
	
	
	public function zhenquery_commission()
	{
		$_GPC = I('request.');
		
		$kwd = trim($_GPC['keyword']);
		$is_not_hexiao = isset($_GPC['is_not_hexiao']) ? intval($_GPC['is_not_hexiao']):0;
		$is_ajax = isset($_GPC['is_ajax']) ? intval($_GPC['is_ajax']) : 0;
		
		$this->kwd = $kwd;
		$this->is_not_hexiao = $is_not_hexiao;
		$this->is_ajax = $is_ajax;
		
		
		$condition = ' and comsiss_flag=1 and comsiss_state=1 ';

		if (!empty($kwd)) {
			$condition .= ' AND ( `username` LIKE "%'.$kwd.'%" or `telephone` like "%'.$kwd.'%" )';
		}
		
		if( $is_not_hexiao == 1 )
		{
			$condition .= " and pickup_id= 0 ";
		}

		 /**
			分页开始
		**/
		$page =  isset($_GPC['page']) ? intval($_GPC['page']) : 1;
		$page = max(1, $page);
		$page_size = 10;
		/**
			分页结束
		**/
		
		$ds = M()->query('SELECT * FROM ' . C('DB_PREFIX'). 'lionfish_comshop_member  WHERE 1 ' . $condition . 
				' order by member_id asc' .' limit ' . (($page - 1) * $page_size) . ',' . $page_size );
		
		$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX') . 
		'lionfish_comshop_member WHERE 1 ' . $condition );
		
		$total = $total_arr[0]['count'];
		
		foreach ($ds as &$value) {
			$value['nickname'] = htmlspecialchars($value['username'], ENT_QUOTES);
			
			$value['id'] = $value['member_id'];
			
			if($is_ajax == 1)
			{
				$ret_html .= '<tr>';
				$ret_html .= '	<td><img src="'.$value['avatar'].'" style="width:30px;height:30px;padding1px;border:1px solid #ccc" />'. $value['nickname'].'</td>';
				   
				$ret_html .= '	<td>'.$value['mobile'].'</td>';
				
				$ret_html .= '<td style="width:80px;"><a href="javascript:;" class="choose_dan_link" data-json=\''.json_encode($value).'\'>选择</a></td>';
				
				$ret_html .= '</tr>';
		
			}
		}
		
		$pager = pagination($total, $page, $page_size,'',$context = array('before' => 5, 'after' => 4, 'isajax' => 1));
		
		if( $is_ajax == 1 )
		{
			echo json_encode( array('code' => 0, 'html' => $ret_html,'pager' => $pager) );
			die();
		}
		

		unset($value);

		if ($_GPC['suggest']) {
			exit(json_encode(array('value' => $ds)));
		}

		
		$this->pager = $pager;
		$this->ds = $ds;
		
		$this->display('User/query_commission');
	}
	
	
	
	
	public function zhenquery()
	{
		$_GPC = I('request.');
		$kwd = trim($_GPC['keyword']);
		$is_not_hexiao = isset($_GPC['is_not_hexiao']) ? intval($_GPC['is_not_hexiao']):0;
		$is_ajax = isset($_GPC['is_ajax']) ? intval($_GPC['is_ajax']) : 0;
		$limit = isset($_GPC['limit']) ? intval($_GPC['limit']) : 0;
		
		$condition = ' ';

		if (!empty($kwd)) {
			$condition .= ' AND ( `username` LIKE "%'.$kwd.'%" or `telephone` like "%'.$kwd.'%" )';
			
		}
		
		if( $is_not_hexiao == 1 )
		{
			$condition .= " and pickup_id= 0 ";
			
		}

		 /**
			分页开始
		**/
		$page =  isset($_GPC['page']) ? intval($_GPC['page']) : 1;
		$page = max(1, $page);
		$page_size = 10;
		/**
			分页结束
		**/
		$sql ='SELECT * FROM ' . C('DB_PREFIX'). 'lionfish_comshop_member WHERE 1 ' . $condition .' order by member_id asc' .' limit ' . (($page - 1) * $page_size) . ',' . $page_size ;
				
		$ds = M()->query($sql);

		
		
		$total = M('lionfish_comshop_member')->where( '1 ' . $condition )->count();
		
		foreach ($ds as &$value) {
			$value['nickname'] = htmlspecialchars($value['username'], ENT_QUOTES);
			
			$value['id'] = $value['member_id'];
			
			//判断该会员是否已经是团长
			if($limit == 1)
			{
				$value['exist'] = M('lionfish_community_head')->where( array('member_id' => $value['id'] ) )->count();
			}else{
				$value['exist'] = 0;
			}
			
			
			
			if($is_ajax == 1)
			{
				$ret_html .= '<tr>';
				$ret_html .= '	<td><img src="'.$value['avatar'].'" style="width:30px;height:30px;padding1px;border:1px solid #ccc" />'. $value['nickname'].'</td>';
				   
				$ret_html .= '	<td>'.$value['mobile'].'</td>';
				
				if(!empty($value['exist'])){
					$ret_html .= '<td style="width:80px;border:#ccc">选择</td>';
				}else{
					$ret_html .= '<td style="width:80px;"><a href="javascript:;" class="choose_dan_link" data-json=\''.json_encode($value).'\'>选择</a></td>';
				}
			   
				
				
				$ret_html .= '</tr>';
		
			}
		}
		
		$pager = pagination($total, $page, $page_size,'',$context = array('before' => 5, 'after' => 4, 'isajax' => 1));
		
		if( $is_ajax == 1 )
		{
			echo json_encode( array('code' => 0, 'html' => $ret_html,'pager' => $pager) );
			die();
		}
		

		unset($value);

		if ($_GPC['suggest']) {
			exit(json_encode(array('value' => $ds)));
		}
		
		$this->ds = $ds;
		$this->pager = $pager;

		$this->display('User/query');
	}
	//--end
    public function zhenquery_many_jia()
    {
        $_GPC = I('request.');
        $kwd = trim($_GPC['keyword']);
        $is_ajax = isset($_GPC['is_ajax']) ? intval($_GPC['is_ajax']) : 0;
        $this->_GPC = $_GPC;
        $this->kwd = $kwd;
        $condition = '  ';
        if (!empty($kwd)) {
            $condition .= ' AND ( `username` LIKE "%'.$kwd.'%" or `mobile` like "%'.$kwd.'%" )';
        }
        /**
        分页开始
         **/
        $page =  isset($_GPC['page']) ? intval($_GPC['page']) : 1;
        $page = max(1, $page);
        $page_size = 10;
        /**
        分页结束
         **/
        $ds = M()->query('SELECT * FROM ' . C('DB_PREFIX') . 'lionfish_comshop_jiauser WHERE 1 ' . $condition .
            ' order by id asc' .' limit ' . (($page - 1) * $page_size) . ',' . $page_size );

        $total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX').'lionfish_comshop_jiauser WHERE 1 ' . $condition );

        $total = $total_arr[0]['count'];

        foreach ($ds as &$value) {
            $value['nickname'] = htmlspecialchars($value['username'], ENT_QUOTES);


            if($is_ajax == 1)
            {
                $ret_html .= '<tr>';
                $ret_html .= '	<td><img src="'.$value['avatar'].'" style="width:30px;height:30px;padding1px;border:1px solid #ccc" />'. $value['nickname'].'</td>';

                $ret_html .= '	<td>'.$value['mobile'].'</td>';

                $ret_html .= '<td style="width:80px;"><a href="javascript:;" class="choose_dan_link" data-json=\''.json_encode($value).'\'>选择</a></td>';

                $ret_html .= '</tr>';

            }
        }

        $pager = pagination($total, $page, $page_size,'',$context = array('before' => 5, 'after' => 4, 'isajax' => 1));

        if( $is_ajax == 1 )
        {
            echo json_encode( array('code' => 0, 'html' => $ret_html,'pager' => $pager) );
            die();
        }


        unset($value);

        if ($_GPC['suggest']) {
            exit(json_encode(array('value' => $ds)));
        }

        $this->ds = $ds;
        $this->pager;

        $this->display();


    }
	public function zhenquery_many()
	{
		$_GPC = I('request.');
		
		$kwd = trim($_GPC['keyword']);
		$is_not_hexiao = isset($_GPC['is_not_hexiao']) ? intval($_GPC['is_not_hexiao']):0;
		$is_ajax = isset($_GPC['is_ajax']) ? intval($_GPC['is_ajax']) : 0;
		
		$this->_GPC = $_GPC;
		$this->kwd = $kwd;
		
		$condition = '  ';

		if (!empty($kwd)) {
			$condition .= ' AND ( `username` LIKE "%'.$kwd.'%" or `telephone` like "%'.$kwd.'%" )';
		}
		
		if( $is_not_hexiao == 1 )
		{
			
			$condition .= " and pickup_id= 0 ";
			
		}

		 /**
			分页开始
		**/
		$page =  isset($_GPC['page']) ? intval($_GPC['page']) : 1;
		$page = max(1, $page);
		$page_size = 10;
		/**
			分页结束
		**/
		
		$ds = M()->query('SELECT * FROM ' . C('DB_PREFIX') . 'lionfish_comshop_member WHERE 1 ' . $condition . 
				' order by member_id asc' .' limit ' . (($page - 1) * $page_size) . ',' . $page_size );
		
		$total_arr = M()->query('SELECT count(1) as count FROM ' . C('DB_PREFIX').'lionfish_comshop_member WHERE 1 ' . $condition );
		
		$total = $total_arr[0]['count'];
		
		foreach ($ds as &$value) {
			$value['nickname'] = htmlspecialchars($value['username'], ENT_QUOTES);
			
			$value['id'] = $value['member_id'];
			
			if($is_ajax == 1)
			{
				$ret_html .= '<tr>';
				$ret_html .= '	<td><img src="'.$value['avatar'].'" style="width:30px;height:30px;padding1px;border:1px solid #ccc" />'. $value['nickname'].'</td>';
				   
				$ret_html .= '	<td>'.$value['mobile'].'</td>';
				
				$ret_html .= '<td style="width:80px;"><a href="javascript:;" class="choose_dan_link" data-json=\''.json_encode($value).'\'>选择</a></td>';
				
				$ret_html .= '</tr>';
		
			}
		}
		
		$pager = pagination($total, $page, $page_size,'',$context = array('before' => 5, 'after' => 4, 'isajax' => 1));
		
		if( $is_ajax == 1 )
		{
			echo json_encode( array('code' => 0, 'html' => $ret_html,'pager' => $pager) );
			die();
		}
		

		unset($value);

		if ($_GPC['suggest']) {
			exit(json_encode(array('value' => $ds)));
		}

		$this->ds = $ds;
		$this->pager;
		
		$this->display();
			

	} 
	
	
	public function query()
	{
		
		$kwd = I('request.keyword','');
		
		
		$condition = ' 1 ';

		if (!empty($kwd)) {
			$condition .= ' AND ( `username` LIKE '.'"%' . $kwd . '%"'.' )';
			
		}
		
		$ds = M('lionfish_comshop_jiauser')->where(  $condition )->select();
		
		$s_html = "";
		
		
		foreach ($ds as &$value) {
			$value['nickname'] = htmlspecialchars($value['username'], ENT_QUOTES);
			$value['avatar'] = tomedia($value['avatar']);
			$value['member_id'] = ($value['id']);
			$s_html .= "<tr><td><img src='".$value['avatar']."' style='width:30px;height:30px;padding1px;border:1px solid #ccc' /> {$value[nickname]}</td>";
           
            $s_html .= "<td>{$value['mobile']}</td>";
            $s_html .= '<td style="width:80px;"><a href="javascript:;" class="choose_dan_link" data-json=\''.json_encode($value).'\'>选择</a></td></tr>';
		
		}

		unset($value);

		if( isset($_GPC['is_ajax']) )
		{
			echo json_encode(  array('code' =>0, 'html' => $s_html) );
			die();
			
		}
		
		$url = 'user/query';
		
		$this->url = $url;
		
		$this->ds = $ds;
		$this->display();
	}
	public function addusergroup()
	{
		$_GPC = I('request.');
		$id = intval($_GPC['id']);
		
		
		if( $id >0 )
		{
			$group = M('lionfish_comshop_member_group')->where( array('id' => $id ) )->find();
			
			$this->group = $group;
		}

		if (IS_POST) {
			$data = array( 'groupname' => trim($_GPC['groupname']) );

			if (!(empty($id))) {
				M('lionfish_comshop_member_group')->where( array('id' => $id) )->save($data);
			}
			 else {
				$id = M('lionfish_comshop_member_group')->add( $data );
			}
			
			show_json(1, array('url' => U('user/usergroup', array('op' => 'display'))));
		}
		
		include $this->display();
	}

	public function recharge()
	{
		$_GPC = I('request.');
		$type = trim($_GPC['type']);
		
		if( empty($type) )
		{
			$type = 'score';
		}

		$id = intval($_GPC['id']);
		
		$profile = M('lionfish_comshop_member')->where( array('member_id' => $id) )->find();
		//增加用户收益相应查询
        $profitinfo = M('lionfish_comshop_member_commiss')->field('money')->where( array('member_id' => $id) )->find();
        if(!empty($profitinfo))
        {
        	$profile['account_fanseconomy'] = $profitinfo['money'];
        }
        else
        	$profile['account_fanseconomy'] = 0;
        //
		if (IS_POST) {
			$typestr = ($type == 'score' ? '积分' : '余额');
			/*
			if($type == 'score')
			{
			    $typestr = '积分';
			}
			else if($type == 'account_money')
			{
			    $typestr = '余额';
			}
			else
			{
			    $typestr = '收益';
			}
			*/
			$num = floatval($_GPC['num']);
			$remark = trim($_GPC['remark']);

			if ($num <= 0) {
				show_json(0, array('message' => '请填写大于0的数字!'));
			}

			$changetype = intval($_GPC['changetype']);

			
			if ($type == 'score') {
				//0 增加 1 减少 2 最终积分
				
				$ch_type = 'system_add';
				if($changetype == 1 )
				{
					$ch_type = 'system_del';
				}
				
				D('Seller/User')->sendMemberPointChange($profile['member_id'], $num, $changetype, $remark, $ch_type);

				//D('Seller/User')->sendMemberPointChange($profile['member_id'], $num, $changetype, $remark);
			}

			if ($type == 'account_money') {
				D('Seller/User')->sendMemberMoneyChange($profile['member_id'], $num, $changetype, $remark);
			}
			if($type == 'account_fanseconomy')
			{
			    //获取当前后台操作人的信息
			    $charger = $_SESSION['seller_name'];
			    //$charger = 'admin';
			    //增加收益相关代码
                D('Seller/User')->changeMemberProfit($profile['member_id'],$num, $changetype ,$remark,$charger);
			}
			show_json(1,  array('url' => $_SERVER['HTTP_REFERER']));
		}
	
		$this->profile = $profile;
		$this->id = $id;
		$this->type = $type;
		$this->gpc = $_GPC;
		
		$this->display();
	}
	
	public function detail()
	{
		$id = I('request.id');
		$_GPC = I('request.');
		$is_showform =  I('request.is_showform',0);
		
		$member = M('lionfish_comshop_member')->where( array('member_id' => $id) )->find();
		
		
		$ordercount = M('lionfish_comshop_order')->where( 'order_status_id in(1,2,4,6,11,14,12,13) and member_id='.$id )->count();	
		$ordermoney = M('lionfish_comshop_order')->where( 'order_status_id in(1,2,4,6,11,14,12,13) and member_id='.$id )->sum('total');	
		
			
		$member['self_ordercount'] = $ordercount;
		$member['self_ordermoney'] = $ordermoney;
		
		//commiss_formcontent is_writecommiss_form
		
		if( $member['is_writecommiss_form'] == 1 )
		{
			$member['commiss_formcontent'] = unserialize($member['commiss_formcontent']);
			
		}
		//增加用户收益相应查询
		$profitinfo = M('lionfish_comshop_member_commiss')->field('money')->where( array('member_id' => $id) )->find();
		if(!empty($profitinfo))
		{
		    $member['account_fanseconomy'] = $profitinfo['money'];
	    }
	    else
	        $member['account_fanseconomy'] = 0;
		//
		if (IS_POST) {
		
			$data = I('request.data');
			
			if($member['is_writecommiss_form'] == 1)
			{
				$commiss_formcontent_data = array();
				foreach( $member['commiss_formcontent'] as $val )
				{
					$key = $val['name'].'_'.$val['type'];
					if( isset($_GPC[$key]) )
					{
						$commiss_formcontent_data[] = array('type' => 'text','name' => $val['name'], 'value' => $_GPC[$key] );
					}
					
					$data['commiss_formcontent'] = serialize($commiss_formcontent_data);
				}				
			}
			
			//if( $commiss_level > 0 )
		//	{
				if(  $id == $data['agentid'] )
				{
					show_json(0, array('message' => '不能选择自己为上级分销商'));
				}
		//	}
			
			M('lionfish_comshop_member')->where( array('member_id' => $id) )->save($data);
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		
		$this->member = $member;
		
		$level_list = M('lionfish_comshop_member_level')->order('level asc')->select();	
		
		$keys_level = array();
		
		foreach($level_list as $vv)
		{
			$keys_level[$vv['id']] = $vv['levelname'];
		}
		
		$this->level_list = $level_list;
		
		$group_list = M('lionfish_comshop_member_group')->order('id asc')->select();
		
		
		$keys_group = array();
		
		if( !empty($group_list) )
		{
			foreach($group_list as $vv)
			{
				$keys_group[$vv['id']] = $vv['groupname'];
			}
		}
		
		
		$this->group_list = $group_list;
		
		$commiss_level = D('Home/Front')->get_config_by_name('commiss_level');
		
		if( empty($commiss_level)  )
		{
			$commiss_level = 0;
		}
		$this->commiss_level = $commiss_level;
		
		
		foreach( $list as $key => $val )
		{
			//ims_ lionfish_comshop_order 1 2 4 6 11
					
			$ordercount = M('lionfish_comshop_order')->where( array('member_id' => $val['member_id'] )  )->count();	
			$ordermoney = M('lionfish_comshop_order')->where( array('order_status_id' => array('in','1,2,4,6,11,14,12,13'),'member_id' => $val['member_id']) )->sum('total');
			
			
			$val['levelname'] = empty($val['level_id']) ? '普通会员':$keys_level[$val['level_id']];
			$val['groupname'] = empty($val['groupid']) ? '默认分组':$keys_group[$val['groupid']];
			

			$has_shopinfo = M('lionfish_comshop_member_shopinfo')->where( array('member_id' => $val['member_id']) )->find();
			
			if( !empty($has_shopinfo) )
			{
				
				$val['has_shopinfo'] = $has_shopinfo;
			}else{
				$val['has_shopinfo'] = array();
			}
			
			$val['ordercount'] = $ordercount;
			$val['ordermoney'] = $ordermoney;
			$list[$key] = $val;
		}
		
		
		$saler = array();
		
		//saler
		if( $member['agentid'] > 0 )
		{		
			$saler = M('lionfish_comshop_member')->field('avatar,username as nickname,member_id')->where( array('member_id' => $member['agentid'] ) )->find();		
		}
		$this->saler = $saler;
		
		$this->display();
	}
	
	public function deleteuserlevel()
	{
		$_GPC = I('request.');
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
		}

		$items = M('lionfish_comshop_member_level')->field('id')->where( ' id in( ' . $id . ' ) '  )->select();

		foreach ($items as $item ) {
			
			M('lionfish_comshop_member')->where(  array('level_id' => $item['id']) )->save( array('level_id' => 0) );		
			
			M('lionfish_comshop_member_level')->where( array('id' => $item['id']) )->delete();
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		
	}
	
	public function deleteuser()
	{
		$id = I('request.id');

		if (empty($id)) {
			$ids = I('request.ids');
			
			$id = ((is_array($ids) ? implode(',', $ids) : 0));
		}

		
		$items = M('lionfish_comshop_member')->field('member_id')->where( array('member_id' => array('in', $id)) )->select();	

		foreach ($items as $item ) {
			M('lionfish_comshop_member')->where( array('member_id' => $item['member_id']) )->delete();
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
	}
	
	public function deleteuserjia()
	{
		$_GPC = I('request.');
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
		}

		$items = M('lionfish_comshop_jiauser')->field('id')->where( 'id in( ' . $id . ' )' )->select();
		

		foreach ($items as $item ) {
			M('lionfish_comshop_jiauser')->where( array('id' => $item['id']) )->delete();
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		
	}
	
	public function deleteusergroup()
	{
		
		$_GPC = I('request.');
		
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
		}

		$items = M('lionfish_comshop_member_group')->where( "id in (".$id.")" )->select();	

		foreach ($items as $item ) {
			
			M('lionfish_comshop_member')->where( array('groupid' => $item['id'] ) )->save( array('groupid' => 0) );
			
			M('lionfish_comshop_member_group')->where( array('id' => $item['id']) )->delete();
		}

		show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
		
	}
	
	
}
?>