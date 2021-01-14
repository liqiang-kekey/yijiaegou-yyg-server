<?php

namespace Seller\Controller;
use Admin\Model\PluginsSliderModel;
class PluginsSliderController extends CommonController {
   	protected function _initialize(){
		parent::_initialize();
		$this->breadcrumb1='插件';
		$this->breadcrumb2='广告位';
			//	
		$this->type_config = array(
		'index_ad_nav' => '首页导航',
		'index_ad_head' => '首页滚动广告',
		'index_ad_list' => '首页横条广告',
		'lottery_wepro_head' =>'小程序抽奖首页单图',
		'newman_wepro_head' =>'小程序老带新首页单图',
		'index_wepro_line' => '小程序自营首页横条广告(2图)',
		'index_wepro_ziying_line' => '小程序自营首页横条广告(1图)',
		'index_wepro_head' =>'小程序首页滚动广告',
		
		'paihang_wepro_head' => '小程序自营排行横条广告',
		'index_wepro_iconnav' =>'小程序首页小图标导航',
		'new_wepro_head' => '小程序自营新品横条广告',
		'wepro_bargain_ad' => '小程序自营砍价顶部广告',
		'wepro_integral_mall' => '小程序自营积分商城顶部广告',
		'wepro_integral_mall_bottom' => '小程序积分详情底部广告',
		);
	}
    public function index(){
		$model=new PluginsSliderModel();   
		
		$data=$model->show_slider_page();		
		
		$this->assign('empty',$data['empty']);// 赋值数据集
		$this->assign('list',$data['list']);// 赋值数据集
		$this->assign('page',$data['page']);// 赋值分页输出	
		
    	$this->display();
	}
	
	function add(){
		
		if(IS_POST){
			
			$model=new PluginsSliderModel();  
			$data=I('post.');
			$return=$model->add_slider($data);			
			$this->osc_alert($return);
		}
		
		$this->crumbs='新增';		
		$this->action=U('PluginsSliderdan/add');
		$this->display('edit');
	}

	function edit(){
		if(IS_POST){
			$model=new PluginsSliderModel();  
			$data=I('post.');
			
			$return=$model->edit_slider($data);		
		
			$this->osc_alert($return);
		}
		$this->crumbs='编辑';		
		$this->action=U('PluginsSlider/edit');
		$this->slider=M('PluginsSlider')->find(I('id'));
		$this->thumb_image=resize($this->slider['image'], 100, 100);
		$this->display('edit');		
	}
	public function del(){
		$r=M('PluginsSlider')->delete(I('id'));
		if($r){
			$this->redirect('PluginsSlider/index');
		}
	}
}