<?phpnamespace Seller\Model;class GeneralmallModel{	public function update($data)	{		$ins_data = array();		$ins_data['advname'] = $data['advname'];		$ins_data['thumb'] = save_media($data['thumb']);		$ins_data['link'] = $data['link'];		$ins_data['displayorder'] = $data['displayorder'];		$ins_data['enabled'] = $data['enabled'];		$ins_data['addtime'] = time();		$ins_data['type'] = $data['type'];		$ins_data['linktype'] = $data['linktype'];		$ins_data['appid'] = $data['appid'];				$id = $data['id'];		if( !empty($id) && $id > 0 )		{			unset($ins_data['addtime']);						M('lionfish_comshop_generalmall_adv')->where( array('id' => $id) )->save( $ins_data );						$id = $data['id'];					}else{			$id = M('lionfish_comshop_generalmall_adv')->add( $ins_data );		}	}			// 导航图标更新	public function navigat_update($data)	{		$ins_data = array();		$ins_data['navname'] = $data['navname'];		$ins_data['appid'] = $data['appid'];		$ins_data['thumb'] = save_media($data['thumb']);		$ins_data['link'] = $data['link'];		$ins_data['displayorder'] = $data['displayorder'];		$ins_data['enabled'] = $data['enabled'];		$ins_data['addtime'] = time();		$ins_data['type'] = $data['type'];				$id = $data['id'];		if( !empty($id) && $id > 0 )		{			unset($ins_data['addtime']);			M('lionfish_comshop_generalmall_navigat')->where( array('id' => $id) )->save( $ins_data );			$id = $data['id'];		}else{			$id = M('lionfish_comshop_generalmall_navigat')->add( $ins_data );		}	}}?>