<?php



namespace Seller\Model;



class CommunityModel{

	
	public function ins_head_commiss_order2($order_id,$order_goods_id,$add_money)
	{
		$add_money = 0;
		
		$order_goods_info = M('lionfish_comshop_order_goods')->field('goods_id,total,shipping_fare,fullreduction_money,voucher_credit')->where( array('order_goods_id' => $order_goods_id) )->find();		
		
		if( empty($order_goods_info) )
		{
			return true;
		}else {
			//head_id
							
			$order_info = M('lionfish_comshop_order')->field('head_id,delivery')->where( array('order_id' => $order_id) )->find();	
			
			
			$head_commission_info = D('Home/Front')->get_goods_common_field($order_goods_info['goods_id'] , 'community_head_commission');
			
			if($order_info['delivery'] == 'tuanz_send')
			{
				$money = round( ($head_commission_info['community_head_commission'] * ($order_goods_info['total']  -$order_goods_info['fullreduction_money']-$order_goods_info['voucher_credit']))/100 ,2 );
			
			}else{
				$money = round( ($head_commission_info['community_head_commission'] * ($order_goods_info['total'] + $order_goods_info['shipping_fare'] -$order_goods_info['fullreduction_money']-$order_goods_info['voucher_credit']))/100 ,2 );
			
			}
			
			if($money <=0)
			{
				$money = 0;
			}
			
			
			//$money = round( ($head_commission_info['community_head_commission'] * ($order_goods_info['total'] - $order_goods_info['shipping_fare']))/100 ,2 );
			
			$community_info = M('lionfish_community_head')->where( array('id' => $order_info['head_id']) )->find();
			
			//退款才能取消的
			$ins_data = array();
			
			$ins_data['member_id'] = $community_info['member_id'];
			$ins_data['head_id'] = $order_info['head_id'];
			$ins_data['order_id'] = $order_id;
			$ins_data['order_goods_id'] = $order_goods_id;
			$ins_data['state'] = 0;
			$ins_data['money'] = $money +$add_money;
			$ins_data['add_shipping_fare'] = $add_money;
			$ins_data['addtime'] = time();
			
			M('lionfish_community_head_commiss_order')->add($ins_data);
			
			return true;
		}
		
	}
	
	/**
		团长佣金 金额
		ins_head_commiss_order2($order_id,$order_goods_id,$add_money)
	**/
	
	public function ins_head_commiss_order($order_id,$order_goods_id, $add_money)
	{
		$order_goods_info = M('lionfish_comshop_order_goods')->field('goods_id,total,shipping_fare,fullreduction_money,voucher_credit,quantity')
							->where( array('order_goods_id' => $order_goods_id ) )->find();
		
		if( empty($order_goods_info) )
		{
			return true;
		}else {
			
			$order_info = M('lionfish_comshop_order')->field('head_id,delivery')->where( array('order_id' => $order_id) )->find();
			
			$head_commission_info = D('Home/Front')->get_goods_common_field($order_goods_info['goods_id'] , 'community_head_commission');
						
						
						
			
			$head_level_arr = D('Seller/Communityhead')->get_goods_head_level_bili( $order_goods_info['goods_id'] );
			
			$community_info = M('lionfish_community_head')->where( array('id' =>  $order_info['head_id'] ) )->find();
			
			$level = $community_info['level_id'];
			
			$is_head_takegoods = D('Home/Front')->get_config_by_name('is_head_takegoods');
		
			$is_head_takegoods = isset($is_head_takegoods) && $is_head_takegoods == 1 ? 1 : 0;
			
			if($is_head_takegoods == 0)
			{
				$level  = 0;
			}

			if( isset($head_level_arr['head_level'.$level]) )
			{
				$head_commission_info['community_head_commission'] = $head_level_arr['head_level'.$level];
			}
			
			if($order_info['delivery'] == 'tuanz_send')
			{
				$head_money = $order_goods_info['total']  -$order_goods_info['fullreduction_money']-$order_goods_info['voucher_credit'];
			
			}
			else{
				$add_money = 0;
				
				$head_money =  $order_goods_info['total']  -$order_goods_info['fullreduction_money']-$order_goods_info['voucher_credit'];
			
			}
			
			
			$money = round( ($head_commission_info['community_head_commission'] * $head_money )/100,2);
			 $max_comunity_money = D('Home/Front')->get_config_by_name('max_comunity_money');
            if($money > $max_comunity_money){
                $money = $max_comunity_money;
            }   
			
			
			//判断是否开启分销模式 开启了几级分销
			$community_head_leve = D('Home/Front')->get_config_by_name('open_community_head_leve');
			
			if( $community_head_leve > 0 )
			{
				$community_head_commiss1 = D('Home/Front')->get_config_by_name('community_head_commiss1');
				$community_head_commiss2 = D('Home/Front')->get_config_by_name('community_head_commiss2');
				$community_head_commiss3 = D('Home/Front')->get_config_by_name('community_head_commiss3');
				
				$community_head_money1 = round( ($head_money * $community_head_commiss1)/100  ,2);
				$community_head_money2 = round( ($head_money * $community_head_commiss2)/100  ,2);
				$community_head_money3 = round( ($head_money * $community_head_commiss3)/100  ,2);
				
				$parent_head1_id = $community_info['agent_id'];
				$parent_member1_id =0;
				
				$parent_head2_id = 0;
				$parent_member2_id =0;
				
				$parent_head3_id = 0;
				$parent_member3_id =0;
				
				if( $parent_head1_id > 0 )
				{
					$parent_community_info1 = M('lionfish_community_head')->field('agent_id,member_id')->where( array('id' => $parent_head1_id ) )->find();	
					
					if(!empty($parent_community_info1))
					{
						$parent_member1_id = $parent_community_info1['member_id'];
					}
					if( !empty($parent_community_info1) && $parent_community_info1['agent_id'] > 0 )
					{
						$parent_head2_id = $parent_community_info1['agent_id'];
						
						$parent_community_info2 = M('lionfish_community_head')->field('agent_id,member_id')->where( array('id' => $parent_head2_id ) )->find();
						
						if( !empty($parent_community_info2) )
						{
							$parent_member2_id = $parent_community_info2['member_id'];
						}
						
						if( !empty($parent_community_info2) && $parent_community_info2['agent_id'] > 0 )
						{
							$parent_head3_id = $parent_community_info2['agent_id'];
							
							if(!empty($parent_head3_id) && $parent_head3_id > 0)
							{
								$parent_community_info3 = M('lionfish_community_head')->field('member_id')->where( array('id' => $parent_head3_id ) )->find();
								
								$parent_member3_id = $parent_community_info3['member_id'];
							}
						}
					}
				}
				
				if( $community_head_leve == 1 )
				{
					if($parent_head1_id > 0)
					{
						//$money = $money - $community_head_money1;
					
						$one_data = array();
						$one_data['member_id'] = $parent_member1_id;
						$one_data['head_id'] = $parent_head1_id;
						$one_data['child_head_id'] = $order_info['head_id'];
						$one_data['level'] = 1;
						$one_data['order_id'] = $order_id;
						$one_data['order_goods_id'] = $order_goods_id;
						$one_data['bili'] = $community_head_commiss1;
						$one_data['money'] = $community_head_money1;
						$this->ins_head_parent_commission($one_data);
					}
				}
				if( $community_head_leve == 2 )
				{
					if($parent_head1_id > 0)
					{
						//$money = $money - $community_head_money1;
					
						$one_data = array();
						$one_data['member_id'] = $parent_member1_id;
						$one_data['head_id'] = $parent_head1_id;
						$one_data['child_head_id'] = $order_info['head_id'];
						$one_data['level'] = 1;
						$one_data['order_id'] = $order_id;
						$one_data['order_goods_id'] = $order_goods_id;
						$one_data['bili'] = $community_head_commiss1;
						$one_data['money'] = $community_head_money1;
						$this->ins_head_parent_commission($one_data);
					}
					
					if($parent_head2_id > 0)
					{
						//$money = $money - $community_head_money2;
					
						$one_data = array();
						$one_data['member_id'] = $parent_member2_id;
						$one_data['head_id'] = $parent_head2_id;
						$one_data['child_head_id'] = $order_info['head_id'];
						$one_data['level'] = 2;
						$one_data['order_id'] = $order_id;
						$one_data['bili'] = $community_head_commiss2;
						
						$one_data['order_goods_id'] = $order_goods_id;
						
						$one_data['money'] = $community_head_money2;
						$this->ins_head_parent_commission($one_data);
					}
					
				}
				if( $community_head_leve == 3 )
				{
					if($parent_head1_id > 0)
					{
						//$money = $money - $community_head_money1;
					
						$one_data = array();
						$one_data['member_id'] = $parent_member1_id;
						$one_data['head_id'] = $parent_head1_id;
						$one_data['child_head_id'] = $order_info['head_id'];
						$one_data['level'] = 1;
						$one_data['order_id'] = $order_id;
						$one_data['order_goods_id'] = $order_goods_id;
						$one_data['bili'] = $community_head_commiss1;
						$one_data['money'] = $community_head_money1;
						$this->ins_head_parent_commission($one_data);
					}
					
					if($parent_head2_id > 0)
					{
						//$money = $money - $community_head_money2;
					
						$one_data = array();
						$one_data['member_id'] = $parent_member2_id;
						$one_data['head_id'] = $parent_head2_id;
						$one_data['child_head_id'] = $order_info['head_id'];
						$one_data['level'] = 2;
						$one_data['order_id'] = $order_id;
						$one_data['order_goods_id'] = $order_goods_id;
						$one_data['bili'] = $community_head_commiss2;
						$one_data['money'] = $community_head_money2;
						$this->ins_head_parent_commission($one_data);
					}
					
					if($parent_head3_id > 0)
					{
						//$money = $money - $community_head_money3;
					
						$one_data = array();
						$one_data['member_id'] = $parent_member3_id;
						$one_data['head_id'] = $parent_head3_id;
						$one_data['child_head_id'] = $order_info['head_id'];
						$one_data['level'] = 3;
						$one_data['order_id'] = $order_id;
						$one_data['order_goods_id'] = $order_goods_id;
						$one_data['money'] = $community_head_money3;
						$one_data['bili'] = $community_head_commiss3;
						$this->ins_head_parent_commission($one_data);
					}
				}
			}
			
			
			$community_money_type = D('Home/Front')->get_config_by_name('community_money_type');
			
			
			if($money <=0)
			{
				$money = 0;
			}
			$fen_type = 0;
			
			//指定金额给团长
			if( !empty($community_money_type) && $community_money_type ==1 )
			{
				$money = $head_commission_info['community_head_commission'] * $order_goods_info['quantity'];
				$fen_type = 1;
				
			}
			
			
			
			
			if($order_info['delivery'] == 'tuanz_send')
			{
				$add_money = $order_goods_info['shipping_fare'];
			}
			//退款才能取消的
			$ins_data = array();
			
			$ins_data['member_id'] = $community_info['member_id'];
			$ins_data['head_id'] = $order_info['head_id'];
			$ins_data['order_id'] = $order_id;
			$ins_data['order_goods_id'] = $order_goods_id;
			$ins_data['state'] = 0;
			$ins_data['bili'] = $head_commission_info['community_head_commission'];
			$ins_data['money'] = $money +$add_money;
			$ins_data['fen_type'] = $fen_type;
			$ins_data['add_shipping_fare'] = $add_money;
			$ins_data['addtime'] = time();
			
			M('lionfish_community_head_commiss_order')->add( $ins_data );
			
			return true;
		}
		
	}
	
	//begin
	/**
		退回订单中的分佣金额
		如果已经分成，那么就要对订单金额进行扣除（目前无）
	**/
	public function back_order_commission($order_id,$order_goods_id = 0)
	{
		
		$list = M('lionfish_community_head_commiss_order')->field('id,order_goods_id')->where( "type in ('orderbuy','commiss') and order_id={$order_id} and state=0" )->select();
		
		foreach($list as $val )
		{
			
			//state =2
			if( !empty($order_goods_id) && $order_goods_id > 0 )
			{
				if($order_goods_id == $val['order_goods_id'])
				{
					M('lionfish_community_head_commiss_order')->where( array('id' => $val['id'] ) )->save( array('state' => 2) );
				}
			}else{
				M('lionfish_community_head_commiss_order')->where( array('id' => $val['id'] ) )->save( array('state' => 2) );
			}
		}
		
		
		$list =  M('lionfish_supply_commiss_order')->field('id,order_goods_id')->where( " order_id={$order_id} and state=0 " )->select();
		
		foreach($list as $val )
		{
			if( !empty($order_goods_id) && $order_goods_id > 0 )
			{
				if($order_goods_id == $val['order_goods_id'])
				{
					M('lionfish_supply_commiss_order')->where( array('id' => $val['id'] ) )->save( array('state' => 2) );
				}
			}else{
				M('lionfish_supply_commiss_order')->where( array('id' => $val['id'] ) )->save( array('state' => 2) );	
			}
			//state =2
		}
		
		
		$list = M('lionfish_comshop_member_commiss_order')->field('id,order_goods_id')->where( array('order_id' => $order_id, 'state' => 0) )->select();
		
		foreach($list as $val )
		{
			if( !empty($order_goods_id) && $order_goods_id > 0 )
			{
				if($order_goods_id == $val['order_goods_id'])
				{
					M('lionfish_comshop_member_commiss_order')->where( array('id' => $val['id'] ) )->save( array('state' => 2) );
				}
			}else{
				
				M('lionfish_comshop_member_commiss_order')->where( array('id' => $val['id'] ) )->save( array('state' => 2) );
			}
		}
		
		
	}
	
	
	/**
		插入多级团长分佣的数据
	**/
	public function ins_head_parent_commission($data = array())
	{
		
		$ins_data = array();
		$ins_data['member_id'] = $data['member_id'];
		$ins_data['head_id'] = $data['head_id'];
		$ins_data['child_head_id'] = $data['child_head_id'];
		$ins_data['type'] = 'commiss';
		$ins_data['level'] = $data['level'];
		$ins_data['order_id'] = $data['order_id'];
		$ins_data['order_goods_id'] = $data['order_goods_id'];
		$ins_data['bili'] = $data['bili'];
		$ins_data['state'] = 0;
		$ins_data['money'] = $data['money'];
		$ins_data['add_shipping_fare'] = 0;
		$ins_data['addtime'] = time();
		
		M('lionfish_community_head_commiss_order')->add( $ins_data );
	}
	
	
	public function ins_agent_community( $head_id )
	{
		$check_info = M('lionfish_community_head_invite_recod')->where( "head_id={$head_id}" )->find();
		
		if( empty($check_info) )
		{
			$head_info = M('lionfish_community_head')->where( "id={$head_id}" )->find();
			
			if( $head_info['state'] == 1 && !empty($head_info['agent_id']) && $head_info['agent_id'] > 0 )
			{
				$zhi_tui_reward_money = D('Home/Front')->get_config_by_name('zhi_tui_reward_money');
				
				if( empty($zhi_tui_reward_money) )
				{
					$zhi_tui_reward_money = 0;
				}
				$ins_data = array();
				$ins_data['head_id'] = $head_id;
				$ins_data['agent_member_id'] = $head_info['agent_id'];
				$ins_data['money'] = $zhi_tui_reward_money;
				$ins_data['addtime'] = time();
				
				M('lionfish_community_head_invite_recod')->add($ins_data);
				
				
				$agent_head_info = M('lionfish_community_head')->where( "id=".$head_info['agent_id'] )->find();
				
						
				//ims_ 
				
				$chco_data = array();
				
				$chco_data['member_id'] = $agent_head_info['member_id'];
				$chco_data['head_id'] = $head_info['agent_id'];
				
				$chco_data['child_head_id'] = $head_id;
				$chco_data['type'] = 'tuijian';
				$chco_data['order_id'] = 0;
				$chco_data['order_goods_id'] = 0;
				$chco_data['state'] = 1;
				$chco_data['money'] = $zhi_tui_reward_money;
				$chco_data['add_shipping_fare'] = 0;
				$chco_data['addtime'] = time();
				
				M('lionfish_community_head_commiss_order')->add( $chco_data );
				
				M('lionfish_community_head_commiss')->where( "head_id=".$agent_head_info['id'] )->setInc('money',$zhi_tui_reward_money);
			}
		}
	}
	//end
	
	function get_community_head_member_count($head_id, $where="")
	{
		
		
		$condition = "  head_id ='{$head_id}' ";
		
		if( !empty($where) )
		{
			$condition .= $where;
		}
		
		
		$sql_count = "select count(DISTINCT(member_id)) as count from ".C('DB_PREFIX')."lionfish_community_history where {$condition} ";
		$count_arr = M()->query($sql_count);
		
		$count = $count_arr[0]['count'];
		
		return $count;
	}
	
	public function get_head_commission_info($member_id, $head_id)
	{
		//ims_ lionfish_community_head_commiss
			
		$head_commiss_info = M('lionfish_community_head_commiss')->where( array('head_id' => $head_id,'member_id' => $member_id) )->find();
		
		if( empty($head_commiss_info) )
		{
			$data = array();
			$data['member_id'] = $member_id;
			$data['head_id'] = $head_id;
			$data['money'] = 0;
			$data['dongmoney'] = 0;
			$data['getmoney'] = 0;
			$data['bankname'] = '';
			$data['bankaccount'] = '';
			$data['bankusername'] = '';
			
			M('lionfish_community_head_commiss')->add($data);
			
			$head_commiss_info = M('lionfish_community_head_commiss')->where( array('head_id' => $head_id, 'member_id' => $member_id) )->find();
		}
		
		return $head_commiss_info;
		
	}
	
	public function in_community_history($member_id,$head_id)
	{
	
		if( !empty($head_id) && $head_id > 0 )
		{
			$history_info = M('lionfish_community_history')->where( array('head_id' => $head_id,'member_id' =>$member_id ) )->find();
		
			if( empty($history_info) )
			{
				$data = array();
				$data['member_id'] = $member_id;
				$data['head_id'] = $head_id;
				$data['addtime'] = time();
				
				M('lionfish_community_history')->add($data);
				
				
				$this->upgrade_head_level($head_id);
				
			} else {
				
				$sql = 'UPDATE '.C('DB_PREFIX'). 'lionfish_community_history SET addtime = '.time().' where id = '.$history_info['id'].'  order by id desc limit 1';
				M()->execute($sql);
			}
		}
		
	}
	
	
	/***
		判断团长是否达到了升级的条件
	****/
	public function upgrade_head_level($head_id)
	{
		$head_info = M('lionfish_community_head')->where( array('id' => $head_id ) )->find();
		
		if( empty($head_info) )
		{
			return false;
		}else{
							
			$membercount = M('lionfish_community_history')->where( array('head_id' => $head_id ) )->count();
			
			$all_pay_where = "  and order_status_id in (6,11) ";
			
			$total_order_money = M('lionfish_comshop_order')->where( " head_id ={$head_id} ". $all_pay_where )->sum('total+shipping_fare-voucher_credit-fullreduction_money');
			
			$head_level = M('lionfish_comshop_community_head_level')->where( "auto_upgrade = 1 and condition_type =1 and condition_two <= {$membercount} " )->order('id desc')->find();
			
			if( !empty($head_level) && $head_level['id'] > $head_info['level_id']  )
			{
				//团长升级了
				M('lionfish_community_head')->where(  array('id' => $head_id ) )->save( array('level_id' => $head_level['id']) );
				
				$head_info['level_id'] = $head_level['id'];
			}
			
			$head_level = M('lionfish_comshop_community_head_level')->where( " auto_upgrade = 1 and condition_type =0 and condition_one <= {$total_order_money} " )->order("id desc")->find();
			
			if( !empty($head_level) && $head_level['id'] > $head_info['level_id']  )
			{
				//团长升级了
				M('lionfish_community_head')->where( array('id' => $head_id ) )->save( array('level_id' => $head_level['id']) );
			}

			// start @modify - 刘鑫芮 2020-03-02 增加团长升级条件 订单单数
            $order_total = M('lionfish_comshop_order')->where( " head_id ={$head_id} ". $all_pay_where )->count();
            $head_level = M('lionfish_comshop_community_head_level')->where( " auto_upgrade = 1 and condition_type =2 and condition_order_total <= {$order_total} " )->order("id desc")->find();

            if( !empty($head_level) && $head_level['id'] > $head_info['level_id'])
            {
                //团长升级了
                $rs = M('lionfish_community_head')->where( array('id' => $head_id ) )->save( array('level_id' => $head_level['id']) );
            }
            // end @modify - 刘鑫芮 2020-03-02 增加团长升级条件 订单单数

        }
	}
	
	public function send_head_success_msg($head_id)
	{
		
		
		$head_info = $this->get_community_info_by_head_id($head_id);
		
		$member_info = M('lionfish_comshop_member')->where( array('member_id' => $head_info['member_id']) )->find();
		
		
		$province = D('Seller/Front')->get_area_info($head_info['province_id']); 
		$city = D('Seller/Front')->get_area_info($head_info['city_id']); 
		$area = D('Seller/Front')->get_area_info($head_info['area_id']); 
		$country = D('Seller/Front')->get_area_info($head_info['country_id']); 
		
		$full_name = $province['name'].$city['name'].$area['name'].$country['name'].$head_info['address'];
				
		$template_data = array();
		$template_data['keyword1'] = array('value' => date('Y-m-d H:i:s',$head_info['addtime'] ), 'color' => '#030303');
		$template_data['keyword2'] = array('value' => $full_name, 'color' => '#030303');
		$template_data['keyword3'] = array('value' => $head_info['community_name'], 'color' => '#030303');
		$template_data['keyword4'] = array('value' => $head_info['head_name'], 'color' => '#030303');
		$template_data['keyword5'] = array('value' => $head_info['head_mobile'], 'color' => '#030303');
		$template_data['keyword6'] = array('value' => '审核通过', 'color' => '#030303');
		$template_data['keyword7'] = array('value' => date('Y-m-d H:i:s',$head_info['apptime']), 'color' => '#030303');
		
		
		$template_id = D('Seller/Front')->get_config_by_name('weprogram_template_apply_community');
		$url = D('Seller/Front')->get_config_by_name('url');
		
		
		$pagepath = 'community/pages/user/me';
		
		
				
			$mb_subscribe = M('lionfish_comshop_subscribe')->where( array('member_id' => $head_info['member_id'] , 'type' => 'apply_community' ) )->find();
			
			//...todo 
			if( !empty($mb_subscribe) )
			{
				$template_id = D('Home/Front')->get_config_by_name('weprogram_subtemplate_apply_community');
			
				$full_name = mb_substr( $full_name ,0,10,'utf-8');
			
				$template_data = array();
				$template_data['date1'] = array('value' => date('Y-m-d H:i:s', $head_info['apptime']) );
				$template_data['thing2'] = array('value' => $full_name );
				$template_data['name3'] = array('value' =>  $head_info['head_name'] );
				$template_data['phone_number4'] = array('value' => $head_info['head_mobile'] );
				$template_data['phrase5'] = array('value' => '审核通过' );
				
				D('Seller/User')->send_subscript_msg( $template_data,$url,$pagepath,$member_info['we_openid'],$template_id );
				
				M('lionfish_comshop_subscribe')->where( array('id' => $mb_subscribe['id'] ) )->delete();
			}
			
		
		$wx_template_data = array(); 
		$weixin_appid = D('Seller/Front')->get_config_by_name('weixin_appid');
		$weixin_template_apply_community = D('Seller/Front')->get_config_by_name('weixin_template_apply_community');
		
		if( !empty($weixin_appid) && !empty($weixin_template_apply_community) )
		{
			//  
			$wx_template_data = array(
								'appid' => $weixin_appid,
								'template_id' => $weixin_template_apply_community,
								'pagepath' => $pagepath,
								'data' => array(
												'first' => array('value' => '恭喜您的申请审核成功','color' => '#030303'),
												'keyword1' => array('value' => '审核通过','color' => '#030303'),
												'keyword2' => array('value' => date('Y-m-d H:i:s'),'color' => '#030303'),
												'remark' => array('value' => '请记得随身带走贵重物品哦','color' => '#030303'),
										)
							);
		}
		
		
		D('Seller/User')->send_wxtemplate_msg(array(),$url,$pagepath,$member_info['we_openid'],$template_id,'', 0,$wx_template_data);
		
		

		
			
	}
	
	public function get_member_community_info($member_id)
	{
		$head_info = M('lionfish_community_head')->where( array('member_id' => $member_id) )->find();
		
		return $head_info;
	}
	
	public function send_head_commission($order_id, $head_id)
	{
		
		$list = M('lionfish_community_head_commiss_order')->where( array('order_id' => $order_id) )->select();
		
		
		foreach($list as $commiss)
		{
			if( $commiss['state'] == 0)
			{
				M('lionfish_community_head_commiss_order')->where( array('id' => $commiss['id'] ) )->save( array('state' => 1) );
				
				//ims_ 
				M()->execute("update ".C('DB_PREFIX')."lionfish_community_head_commiss set money=money+{$commiss[money]} 
						where  head_id=".$commiss['head_id'] );
				
				//发送佣金到账TODO。。。
			}
		}
		
		
		
	}
	
	public function get_community_info_by_head_id($head_id)
	{
		
		$head_info = M('lionfish_community_head')->where( array('id' => $head_id ) )->find();
		
		return $head_info;
	}
	
	public function send_apply_success_msg($apply_id)
	{
		$apply_info = M('lionfish_community_head_tixian_order')->where( array('id' => $apply_id ) )->find();
						
						
		$head_id = $apply_info['head_id'];
		
		$head_info = $this->get_community_info_by_head_id($head_id);
		
		$member_info = M('lionfish_comshop_member')->field('we_openid')->where( array('member_id' => $head_info['member_id']) )->find();
		
			
		$community_head_commiss_info = M('lionfish_community_head_commiss')->where( array('head_id' => $head_id ) )->find();				
						
		
		if( $apply_info['type'] > 0 )
		{
			switch($apply_info['type'])
			{
				case 1:
					$community_head_commiss_info['bankname'] = '账户余额';
					break;
				case 2:
					$community_head_commiss_info['bankname'] = '微信零钱';	
					break;
				case 3:
					$community_head_commiss_info['bankname'] = '支付宝';
					break;
				case 4:
					$community_head_commiss_info['bankname'] = '银行卡';
					break;
			}
		}
		
		
		//提现金额 手续费 到账金额 提现至 提现状态 提现申请时间 到账时间
		
		$dao_zhang = floatval( $apply_info['money']-$apply_info['service_charge']);
		
		$template_data = array();
		$template_data['keyword1'] = array('value' => sprintf("%01.2f", $apply_info['money']), 'color' => '#030303');
		$template_data['keyword2'] = array('value' => $apply_info['service_charge'], 'color' => '#030303');
		
		$template_data['keyword3'] = array('value' => sprintf("%01.2f", $dao_zhang), 'color' => '#030303');
		$template_data['keyword4'] = array('value' => $community_head_commiss_info['bankname'], 'color' => '#030303');
		$template_data['keyword5'] = array('value' => '提现成功', 'color' => '#030303');
		$template_data['keyword6'] = array('value' => date('Y-m-d H:i:s' , $apply_info['addtime']), 'color' => '#030303');
		$template_data['keyword7'] = array('value' => date('Y-m-d H:i:s' , $apply_info['shentime']), 'color' => '#030303');
		
		
		$template_id = D('Home/Front')->get_config_by_name('weprogram_template_apply_tixian');
		
		$url = 'https://'.$_SERVER['SERVER_NAME'];
		$pagepath = 'community/pages/user/me';
		
		
			/**
				提现成功通知
				
				提现金额
				{{amount1.DATA}}

				手续费
				{{amount2.DATA}}

				打款方式
				{{thing3.DATA}}

				打款原因
				{{thing4.DATA}}
			**/
						
			$mb_subscribe = M('lionfish_comshop_subscribe')->where( array('member_id' => $head_info['member_id'] , 'type' => 'apply_tixian' ) )->find();
			
			//...todo
			if( !empty($mb_subscribe) )
			{
				
				$template_id = D('Home/Front')->get_config_by_name('weprogram_subtemplate_apply_tixian');
			
				
				
				$template_data = array();
				$template_data['amount1'] = array('value' => sprintf("%01.2f", $apply_info['money']) );
				$template_data['amount2'] = array('value' => sprintf("%01.2f", $apply_info['service_charge']) );
				$template_data['thing3'] = array('value' => $community_head_commiss_info['bankname'] );
				$template_data['thing4'] = array('value' => '提现成功,请及时进行对账' );
				
				D('Seller/User')->send_subscript_msg( $template_data,$url,$pagepath,$member_info['we_openid'],$template_id );
				
				M('lionfish_comshop_subscribe')->where( array('id' => $mb_subscribe['id'] ) )->delete();
			}
		
		$wx_template_data = array(); 
		$weixin_appid = D('Home/Front')->get_config_by_name('weixin_appid');
		$weixin_template_apply_tixian = D('Home/Front')->get_config_by_name('weixin_template_apply_tixian');
		
		if( !empty($weixin_appid) && !empty($weixin_template_apply_tixian) )
		{
			$wx_template_data = array(
								'appid' => $weixin_appid,
								'template_id' => $weixin_template_apply_tixian,
								'pagepath' => $pagepath,
								'data' => array(
												'first' => array('value' => '尊敬的用户，您的提现已到账','color' => '#030303'),
												'keyword1' => array('value' => date('Y-m-d H:i:s' , $apply_info['addtime']),'color' => '#030303'),
												'keyword2' => array('value' => $community_head_commiss_info['bankname'],'color' => '#030303'),
												'keyword3' => array('value' => sprintf("%01.2f", $apply_info['money']),'color' => '#030303'),
												'keyword4' => array('value' => $apply_info['service_charge'],'color' => '#030303'),
												'keyword5' => array('value' => sprintf("%01.2f", $dao_zhang),'color' => '#030303'),
												'remark' => array('value' => '请及时进行对账','color' => '#030303'),
										)
							);
		}
		
		D('Seller/User')->send_wxtemplate_msg($template_data,$url,$pagepath,$member_info['we_openid'],$template_id,$member_formid_info['formid'] , $uniacid,$wx_template_data);
		
		
	}
	

}

?>