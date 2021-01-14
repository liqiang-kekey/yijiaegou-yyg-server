<?php

namespace Seller\Controller;

class AttachmentController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
		
		//'pinjie' => '拼团介绍',
	}
	
	public function index()
	{
		if (IS_POST) {
			
			$data = I('request.parameter'); 
			
			
			D('Seller/Config')->update($data);
			
			
			show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
		}
		$data = D('Seller/Config')->get_all_config();
		
		$this->data = $data;
		
		$this->display();
	}
	
	
}
?>