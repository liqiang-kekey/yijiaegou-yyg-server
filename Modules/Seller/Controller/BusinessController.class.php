<?php

namespace Seller\Controller;

class BusinessController extends CommonController{

    protected function _initialize(){
        parent::_initialize();
    }
    public function category(){
        $pindex    = I('request.page', 1);
        $psize     = 20;

        $keyword = I('request.keyword');
        $this->keyword = $keyword;

        if (!empty($keyword)) {
            $condition .= ' and title like "%'.$keyword.'%"';
        }

        $enabled = I('request.enabled',-1);

        if (isset($enabled) && $enabled >= 0) {

            $condition .= ' and enabled = ' . $enabled;
        } else {
            $enabled = -1;
        }
        $this->enabled = $enabled;
        $list = M()->query('SELECT id,title,displayorder,enabled FROM ' .
            C('DB_PREFIX'). "lionfish_comshop_business_category  
		WHERE 1=1 " . $condition . ' order by displayorder desc, id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);

        $total = M('lionfish_comshop_business_category')->where("1=1 ".$condition)->count();

        $pager = pagination2($total, $pindex, $psize);

        $this->list = $list;
        $this->pager = $pager;

        $this->display();
    }
    public function index()
    {

        $pindex    = I('request.page', 1);
        $psize     = 20;

        $keyword = I('request.keyword');
        $this->keyword = $keyword;

        if (!empty($keyword)) {
            $condition .= ' and title like "%'.$keyword.'%"';
        }

        $enabled = I('request.enabled',-1);

        if (isset($enabled) && $enabled >= 0) {

            $condition .= ' and enabled = ' . $enabled;
        } else {
            $enabled = -1;
        }
        $this->enabled = $enabled;



        $list = M()->query('SELECT id,title,content,displayorder,enabled,category_id FROM ' .
            C('DB_PREFIX'). "lionfish_comshop_business  
		WHERE 1=1 " . $condition . ' order by displayorder desc, id desc limit ' . (($pindex - 1) * $psize) . ',' . $psize);
        foreach ($list as &$item){
            $data = M('lionfish_comshop_business_category')->where( array('id' => $item['category_id']) )->find();
            $item['category'] = $data['title'];
        }
        $total = M('lionfish_comshop_business')->where("1=1 ".$condition)->count();

        $pager = pagination2($total, $pindex, $psize);

        $this->list = $list;
        $this->pager = $pager;

        $this->display();
    }
    /**
     * 编辑添加
     */
    public function addcategory()
    {
        $id = I('request.id');

        if (!empty($id)) {
            $item = M('lionfish_comshop_business_category')->where( array('id' => $id) )->find();
            $this->id = $id;
            $this->item = $item;
        }

        if (IS_POST) {
            $data = I('request.data');
            if($data['id']>0){
                $res = [
                    'title'=>$data['title'],
                    'displayorder'=>$data['displayorder'],
                    'enabled'=>$data['enabled']
                ];
                $item = M('lionfish_comshop_business_category')->where(array('id'=>$data['id']))->save($res);
            }else{
                $res = [
                    'title'=>$data['title'],
                    'displayorder'=>$data['displayorder'],
                    'enabled'=>$data['enabled'],
                    'addtime'=>time()
                ];
                $item = M('lionfish_comshop_business_category')->add($res);
            }


            show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
        }
        $this->display('Business/addcategory');
    }
    /**
     * 编辑添加
     */
    public function add()
    {
        $id = I('request.id');
        if (!empty($id)) {
            $item = M('lionfish_comshop_business')->where( array('id' => $id) )->find();
            $this->id = $id;
            $this->item = $item;

        }
        $category = M('lionfish_comshop_business_category')->field('title,id')->where( array('enabled' =>1 ))->select();
        $this->category = $category;
        if (IS_POST) {
            $data = I('request.data');
            D('Seller/business')->update($data);

            show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
        }
        $this->display('Business/post');
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

        if (!(in_array($type, array('enabled', 'displayorder')))) {
            show_json(0, array('message' => '参数错误'));
        }

        $items = M('lionfish_comshop_business')->where( array('id' => array('in', $id) ) )->select();

        foreach ($items as $item) {

            M('lionfish_comshop_business')->where( array('id' => $item['id']) )->save( array($type => $value) );
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

    }

    /**
     * 删除公告
     */
    public function delete()
    {

        $id = I('request.id');

        if (empty($id)) {
            $ids = I('request.ids');
            $id = (is_array($ids) ? implode(',', $ids) : 0);
        }

        $items = M('lionfish_comshop_business')->field('id,title')->where( array('id' => array('in', $id) ) )->select();

        if (empty($item)) {
            $item = array();
        }

        foreach ($items as $item) {
            M('lionfish_comshop_business')->where( array('id' => $item['id']) )->delete();
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
    }
}
?>