<?php

namespace Seller\Controller;

class CoinController extends CommonController{

    protected function _initialize(){
        parent::_initialize();
         $this->breadcrumb1='兑换券管理';
        $this->breadcrumb2='兑换券信息';
        $this->sellerid = SELLERUID;
    }

    public function index(){

        $pindex = I('get.page', 1);
        $psize = 20;

        $starttime_arr = I('get.time');

        $starttime = isset($starttime_arr['start']) ? strtotime($starttime_arr['start']) : strtotime(date('Y-m-d'.' 00:00:00'));

        $endtime = isset($starttime_arr['end']) ? strtotime($starttime_arr['end']) : strtotime(date('Y-m-d'.' 23:59:59'));


        $this->starttime = $starttime;
        $this->endtime = $endtime;

        $searchtime = I('get.searchtime','');

        $this->searchtime = $searchtime;

        //---begin
        $psize = 20;

        $condition = ' WHERE 1=1';

        $sqlcondition = "";

        $keyword = I('get.keyword','');

        $this->keyword = $keyword;

        if (!(empty($keyword))) {
           $condition .= " AND  (`id` = '{$keyword}' or `name` LIKE '%{$keyword}%' or `member_id` = '{$keyword}' ) ";
           // $condition .= " AND  (`id` = '{$keyword}' or `name` LIKE '%{$keyword}%' ) ";
        }
        if( !empty($starttime_arr) )
        {
            $condition .= ' AND  (addtime >='.$starttime.' and addtime <= '.$endtime.' )';
        }

        $sql = 'SELECT COUNT(id) as count FROM ' .C('DB_PREFIX'). 'lionfish_comshop_coin ' .$sqlcondition.  $condition ;

        //dump($sql);die;
        $total_arr = M()->query($sql);
        $total = $total_arr[0]['count'];


        //'sortby' =>$sortby,'sortfield' => 'day_salescount',

        $sortby = I('get.sortby');
        $sortfield = I('get.sortfield');

        $this->sortfield = $sortfield;

        $sortby = (!empty($sortby) ? ($sortby== 'asc' ?'desc':'asc') : ( !empty($sortfield) ? 'desc':'' ) );

        $this->sortby = $sortby;

        $pager = pagination2($total, $pindex, $psize);
        $sort_way = '`id` DESC';
        if (!(empty($total))) {
            $sql = 'SELECT * FROM ' .C('DB_PREFIX'). 'lionfish_comshop_coin '  .$sqlcondition . $condition . ' 
					ORDER BY   '.$sort_way.' ';
            if (I('export') != 1) { // 如果导出excel 就显示全部
                $sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
            }
            //$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
            $list = M()->query($sql);
            foreach ($list as &$item){
                $card = M('lionfish_comshop_package_goods')->field('id,goodsname')->where(array('id'=>$item['card_id']))->find();
                $goods = M('lionfish_comshop_goods')->field('id,goodsname')->where(array('id'=>$item['gid']))->find();
                $item['goodsname'] = $goods['goodsname'];
                $item['cardname'] = $card['goodsname'];
            }

            $this->card = $card;
            $this->goods = $goods;
        }else{
            $list = [];
        }
        //dump($sql);die;
        $this->keyword = $keyword;
        $this->total = $total;
        $this->all_count = $total;
        $this->pager = $pager;
        $this->assign('list',$list);// 赋值数据集
        $this->assign('pager',$pager);// 赋值分页输出
        $this->display();
    }

    /**
     * 商品列表到处excel
     * @author 刘鑫芮 2020-03-02
     * @param $list 商品列表集合数据 全部-根据where 条件搜索的
     * */
    public function goods_listexcel($list) {
//        dump($list);die;
        $printList = $list;
        foreach($list as $key => $value) {
            $printList[$key] = array(
                'id' => $value['id']
            );
        }
        $columns = array(
            array(
                'title' => '商品ID(禁止修改)',
                'field' => 'id',
                'width' => 24
            ) ,
            array(
                'title' => '商品名称',
                'field' => 'goodsname',
                'width' => 24
            ) ,
            array(
                'title' => '商品简称',
                'field' => 'print_sub_title',
                'width' => 24
            ) ,
            array(
                'title' => '商品编码',
                'field' => 'codes',
                'width' => 24
            ) ,
            array(
                'title' => '一级分类ID',
                'field' => 'cate1_id',
                'width' => 24
            ) ,
            array(
                'title' => '一级分类名称',
                'field' => 'cate1_name',
                'width' => 24
            ) ,
            array(
                'title' => '二级分类ID',
                'field' => 'cate2_id',
                'width' => 24
            ) ,
            array(
                'title' => '二级分类名称',
                'field' => 'cate2_name',
                'width' => 24
            ) ,

            array(
                'title' => '商品价格',
                'field' => 'price',
                'width' => 24
            ) ,
            array(
                'title' => '商品成本价',
                'field' => 'costprice',
                'width' => 24
            ) ,
            array(
                'title' => '会员卡价格',
                'field' => 'card_price',
                'width' => 24
            ) ,
            array(
                'title' => '商品原价',
                'field' => 'productprice',
                'width' => 24
            ) ,
            array(
                'title' => '商品库存',
                'field' => 'total',
                'width' => 24
            ) ,
            array(
                'title' => '每日销量',
                'field' => 'day_salescount',
                'width' => 24
            ) ,

            array(
                'title' => '1上架/0下架',
                'field' => 'grounding',
                'width' => 24
            ) ,
            array(
                'title' => '首页推荐(0:取消/1:是)',
                'field' => 'is_index_show',
                'width' => 24
            ) ,
            array(
                'title' => '限时秒杀(0:取消/1:是)',
                'field' => 'is_spike_buy',
                'width' => 24
            ) ,
            array(
                'title' => '所有团长',
                'field' => 'is_all_sale_str',
                'width' => 24
            ) ,
            array(
                'title' => '新人专享',
                'field' => 'is_new_buy',
                'width' => 24
            ) ,

            array(
                'title' => '商品排序(数字)',
                'field' => 'index_sort',
                'width' => 24
            ) ,
            array(
                'title' => '每天限购',
                'field' => 'oneday_limit_count',
                'width' => 24
            ) ,
            array(
                'title' => '单次限购',
                'field' => 'one_limit_count',
                'width' => 24
            ) ,
            array(
                'title' => '历史限购',
                'field' => 'total_limit_count',
                'width' => 24
            ) ,
            array(
                'title' => '开始时间',
                'field' => 'pin_begin_time',
                'width' => 24
            ) ,
            array(
                'title' => '结束时间',
                'field' => 'pin_end_time',
                'width' => 24
            ) ,
            array(
                'title' => '商品重量(单位:g)',
                'field' => 'weight',
                'width' => 24
            ) ,
            array(
                'title' => '规格(1:开启/0:关闭)',
                'field' => 'hasoption',
                'width' => 24
            ) ,
            array(
                'title' => '规格id(禁止修改)',
                'field' => 'option_id',
                'width' => 24
            ) ,
            array(
                'title' => '规格名称(禁止修改)',
                'field' => 'option_title',
                'width' => 24
            ) ,
            array(
                'title' => '规格库存',
                'field' => 'option_stock',
                'width' => 24
            ) ,
            array(
                'title' => '规格现价',
                'field' => 'option_marketprice',
                'width' => 24
            ) ,
            array(
                'title' => '规格原价',
                'field' => 'option_productprice',
                'width' => 24
            ) ,
            array(
                'title' => '规格会员价',
                'field' => 'option_card_price',
                'width' => 24
            ) ,
            array(
                'title' => '规格成本价',
                'field' => 'option_costprice',
                'width' => 24
            ) ,
            array(
                'title' => '规格编码',
                'field' => 'option_goodssn',
                'width' => 24
            ) ,
            array(
                'title' => '规格重量(单位:g)',
                'field' => 'option_weight',
                'width' => 24
            )
        );
        sellerLog('导出商品excel', 3);
        D('Seller/Excel')->export_goods_list_pi(array(
            'title' => '商品列表',
            'columns' => $columns
        ), $list);
    }

    public function edittags()
    {
        $_GPC = I('request.');

        $id = intval($_GPC['id']);
        if (!empty($id)) {

            $item = M('lionfish_comshop_goods_tags')->field('id,tagname,tagcontent,state,sort_order')->where( array('id' =>$id ) )->find();

            if (json_decode($item['tagcontent'], true)) {
                $labelname = json_decode($item['tagcontent'], true);
            }
            else {
                $labelname = unserialize($item['tagcontent']);
            }
            $this->item = $item;
            $this->labelname = $labelname;
        }

        if (IS_POST) {

            $data = $_GPC['data'];

            D('Seller/Tags')->update($data);

            show_json(1, array('url' => U('goods/goodstag') ));
        }

        $this->display('Goods/addtags');
    }

    public function addtags()
    {
        $_GPC = I('request.');

        if (IS_POST) {

            $data = $_GPC['data'];

            D('Seller/Tags')->update($data);

            show_json(1, array('url' => U('goods/goodstag')));
        }

        $this->display();
    }


    public function show_logs()
    {
        $goods_id = I('get.goods_id');

        D('Seller/Redisorder')->show_logs($goods_id);

    }

    public function tagsstate()
    {
        $_GPC = I('request.');
        $id = intval($_GPC['id']);

        if (empty($id)) {
            $id = $_GPC['ids'];
        }


        if( is_array($id) )
        {
            $items = M('lionfish_comshop_goods_tags')->field('id,tagname')->where( array('id' => array('in', $id)) )->select();

        }else{
            $items = M('lionfish_comshop_goods_tags')->field('id,tagname')->where( array('id' =>$id ) )->select();

        }



        if (empty($item)) {
            $item = array();
        }

        foreach ($items as $item) {
            M('lionfish_comshop_goods_tags')->where( array('id' => $item['id']) )->save( array('state' => intval($_GPC['state'])) );
        }

        show_json(1, array('url' => U('goods/goodstag')));
    }

    public function deletetags()
    {
        $_GPC = I('request.');
        $id = intval($_GPC['id']);

        if (empty($id)) {
            $id = $_GPC['ids'];
        }

        if( is_array($id) )
        {
            $items = M('lionfish_comshop_goods_tags')->field('id,tagname')->where( array('id' => array('in', $id)) )->select();

        }else{
            $items = M('lionfish_comshop_goods_tags')->field('id,tagname')->where( array('id' =>$id ) )->select();

        }
        //$items = M('lionfish_comshop_goods_tags')->field('id,tagname')->where( array('id' => array('in', $id )) )->select();

        if (empty($item)) {
            $item = array();
        }

        foreach ($items as $item) {
            M('lionfish_comshop_goods_tags')->where( array('id' => $item['id']) )->delete();
        }

        show_json(1, array('url' => U('goods/goodstag')));
    }


    public function labelquery()
    {
        $_GPC = I('request.');

        $kwd = trim($_GPC['keyword']);
        $type = isset($_GPC['type']) ? $_GPC['type'] : 'normal';


        $condition = ' and state = 1 and tag_type="'.$type.'" ';

        if (!empty($kwd)) {
            $condition .= ' AND tagname LIKE "%'.$kwd.'%" ';
        }

        $labels = M('lionfish_comshop_goods_tags')->field('id,tagname,tagcontent')->where(  '1 '. $condition )->order('id desc')->select();

        if (empty($labels)) {
            $labels = array();
        }
        $html = '';

        foreach ($labels as $key => $value) {
            if (json_decode($value['tagcontent'], true)) {
                $labels[$key]['tagcontent'] = json_decode($value['tagcontent'], true);
            }
            else {
                $labels[$key]['tagcontent'] = unserialize($value['tagcontent']);
            }


            $html  .= '<nav class="btn btn-default btn-sm choose_dan_link" data-id="'.$value['id'].'" data-json=\''.json_encode(array("id"=>$value["id"],"tagname"=>$value["tagname"])).'\'>';
            $html  .=	$value['tagname'];
            $html  .= '</nav>';
        }


        if( isset($_GPC['is_ajax']) )
        {
            echo json_encode( array('code' => 0, 'html' => $html) );
            die();
        }

        $this->labels = $labels;

        $this->display();
    }


    public function deletecomment()
    {
        $_GPC = I('request.');


        $id = intval($_GPC['id']);

        if (empty($id)) {
            $id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
        }

        $items = M()->query('SELECT comment_id FROM ' . C('DB_PREFIX') .
            'lionfish_comshop_order_comment WHERE comment_id in( ' . $id . ' ) ');



        if (empty($item)) {
            $item = array();
        }

        foreach ($items as $item) {
            M('lionfish_comshop_order_comment')->where( array('comment_id' => $item['comment_id']) )->delete();
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));

    }


    public function addvircomment()
    {
        $_GPC = I('request.');

        if (IS_POST) {
            $data = $_GPC['data'];
            $jia_id = $_GPC['jiaid'];
            $goods_id = $_GPC['goods_id'];

            if( empty($goods_id) )
            {
                show_json(0, array('message' => '请选择评价商品!'));
            }

            if( empty($jia_id) )
            {
                show_json(0, array('message' => '请选择机器人!'));
            }

            $goods_info = M('lionfish_comshop_goods')->field('goodsname')->where( array('id' => $goods_id) )->find();

            $goods_image = isset($_GPC['goods_image']) && !empty($_GPC['goods_image']) ? $_GPC['goods_image'] : array();
            $time = empty($_GPC['time']) ? time() : $_GPC['time'];

            $jia_info = M('lionfish_comshop_jiauser')->where( array('id' => $jia_id ) )->find();

            $commen_data = array();
            $commen_data['order_id'] = 0;
            $commen_data['state'] = 1;
            $commen_data['type'] = 1;
            $commen_data['member_id'] = $jia_id;
            $commen_data['avatar'] = $jia_info['avatar'];
            $commen_data['user_name'] = $jia_info['username'];
            $commen_data['order_num_alias'] = 1;
            $commen_data['star'] = $data['star'];
            $commen_data['star3'] = $data['star3'];
            $commen_data['star2'] = $data['star2'];
            $commen_data['is_picture'] = !empty($goods_image) ? 1: 0;
            $commen_data['content'] = $data['content'];
            $commen_data['images'] = serialize(implode(',', $goods_image));


            $image  = D('Home/Pingoods')->get_goods_images($goods_id);

            $seller_id = 1;


            if(!empty($image))
            {
                $commen_data['goods_image'] = $image['image'];
            }else{
                $commen_data['goods_image'] = '';
            }

            $commen_data['goods_id'] = $goods_id;
            $commen_data['goods_name'] = $goods_info['goodsname'];
            $commen_data['add_time'] = strtotime($time);


            M('lionfish_comshop_order_comment')->add($commen_data);

            show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));

        }


        $this->display();
    }


    public function change_cm()
    {
        $_GPC = I('request.');
        $id = intval($_GPC['id']);

        //ids
        if (empty($id)) {
            $id = ((is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0));
        }


        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }


        $type = trim($_GPC['type']);
        $value = trim($_GPC['value']);

        if (!(in_array($type, array('is_take_fullreduction')))) {
            show_json(0, array('message' => '参数错误'));
        }

        $items = M('lionfish_comshop_goods')->field('id')->where( 'id in( ' . $id . ' )' )->select();
        foreach ($items as $item ) {

            //--
            if($type == 'is_take_fullreduction' && $value == 1)
            {
                $gd_common = M('lionfish_comshop_good_common')->field('supply_id')->where( array('goods_id' => $item['id'] ) )->find();

                if( !empty($gd_common['supply_id']) && $gd_common['supply_id'] > 0)
                {
                    $supply_info = M('lionfish_comshop_supply')->field('type')->where( array('id' => $gd_common['supply_id'] ) )->find();


                    if( !empty($supply_info) && $supply_info['type'] == 1 )
                    {
                        continue;
                    }
                }
            }
            //---

            M('lionfish_comshop_good_common')->where( array('goods_id' => $item['id']) )->save( array($type => $value) );
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
    }


    public function commentstate()
    {
        $_GPC = I('request.');
        $id = intval($_GPC['id']);

        if (empty($id)) {
            $id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
        }

        $items = M('lionfish_comshop_order_comment')->field('comment_id')->where( "comment_id in ({$id})")->select();

        if (empty($item)) {
            $item = array();
        }

        foreach ($items as $item) {
            M('lionfish_comshop_order_comment')->where( array('comment_id' => $item['comment_id']) )->save( array('state' => intval($_GPC['state'])) );
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
    }


    /**
     * 一键设置商品时间
     */
    public function settime()
    {

        if (IS_POST) {

            $data = I('request.time', array());

            $param = array();
            $param['goods_same_starttime'] = strtotime(trim($data['start'])) ? strtotime(trim($data['start'])) : time();
            $param['goods_same_endtime'] = strtotime(trim($data['end'])) ? strtotime(trim($data['end'])) : time();


            $is_samedefault_now = I('request.is_samedefault_now');
            $is_sametihuo_time = I('request.is_sametihuo_time');
            $pick_up_type = I('request.pick_up_type');
            $pick_up_modify = I('request.pick_up_modify');


            if( $pick_up_type == 4 )
            {
                $pick_up_type = 0;
            }

            $param['is_samedefault_now'] = $is_samedefault_now;
            $param['is_sametihuo_time'] = $is_sametihuo_time;
            $param['pick_up_type'] = $pick_up_type;
            $param['pick_up_modify'] = $pick_up_modify;

            // lionfish_comshop_good_common begin_time end_time
            D('Seller/Config')->update($param);

            $param1 = array();
            $param1['begin_time'] = $param['goods_same_starttime'];
            $param1['end_time'] = $param['goods_same_endtime'];


            //--begin

            if (defined('ROLE') && ROLE == 'agenter' )
            {
                $supper_info = get_agent_logininfo();


                if( $is_samedefault_now == 2 )
                {
                    $sql = " update  ".C('DB_PREFIX')."lionfish_comshop_good_common set end_time = ".$param['goods_same_endtime'].", begin_time=".$param['goods_same_starttime']." 
						where ".'goods_id in (select id from '.C('DB_PREFIX').'lionfish_comshop_goods where `grounding` =1 and type != "pin") and supply_id='.$supper_info['id'];

                    M()->execute($sql);
                }

                if( $is_sametihuo_time == 2 )
                {
                    $sql = " update ".C('DB_PREFIX')."lionfish_comshop_good_common set pick_up_type = ".$pick_up_type.", pick_up_modify=".$pick_up_modify." 
						where ".'goods_id in (select id from '.C('DB_PREFIX').'lionfish_comshop_goods where `grounding` =1 and type != "pin"  ) and supply_id='.$supper_info['id'];

                    M()->execute($sql);
                }

            }else{



                //取出所有独立供应商
                $all_du_supply = M('lionfish_comshop_supply')->where( array('type' => 1) )->select();

                $all_du_sids = array();

                if( !empty($all_du_supply) )
                {
                    foreach( $all_du_supply as $val )
                    {
                        $all_du_sids[] = $val['id'];
                    }
                }

                if( $is_samedefault_now == 1 )
                {
                    //仅平台
                    $where = "";
                    if( !empty($all_du_sids) )
                    {
                        $where = " and supply_id not in(".implode(',', $all_du_sids ).") ";
                    }

                    $sql = " update  ".C('DB_PREFIX')."lionfish_comshop_good_common set end_time = ".$param['goods_same_endtime'].", begin_time=".$param['goods_same_starttime']." 
						where ".'goods_id in (select id from '.C('DB_PREFIX').'lionfish_comshop_goods where `grounding` =1 and type != "pin" '.$where.' ) ';

                    M()->execute($sql);

                }else if( $is_samedefault_now == 2 )
                {
                    //所有商品除了拼团
                    $sql = " update  ".C('DB_PREFIX')."lionfish_comshop_good_common set end_time = ".$param['goods_same_endtime'].", begin_time=".$param['goods_same_starttime']." 
						where ".'goods_id in (select id from '.C('DB_PREFIX').'lionfish_comshop_goods where `grounding` =1 and type != "pin") ';

                    M()->execute($sql);
                }

                if( $is_sametihuo_time == 1 )
                {
                    //仅平台
                    $where = "";
                    if( !empty($all_du_sids) )
                    {
                        $where = " and supply_id not in(".implode(',', $all_du_sids ).") ";
                    }

                    $sql = " update ".C('DB_PREFIX')."lionfish_comshop_good_common set pick_up_type = ".$pick_up_type.", pick_up_modify='".$pick_up_modify."' 
						where ".'goods_id in (select id from '.C('DB_PREFIX').'lionfish_comshop_goods where `grounding` =1 and type != "pin" '.$where.' ) ';

                    M()->execute($sql);


                }else if( $is_sametihuo_time == 2 )
                {
                    $sql = " update ".C('DB_PREFIX')."lionfish_comshop_good_common set pick_up_type = ".$pick_up_type.", pick_up_modify='".$pick_up_modify."'  
						where ".'goods_id in (select id from '.C('DB_PREFIX').'lionfish_comshop_goods where `grounding` =1 and type != "pin"  ) ';

                    M()->execute($sql);
                }

                /**
                $sql = 'UPDATE '.C('DB_PREFIX'). 'lionfish_comshop_good_pin SET begin_time = '.$param['goods_same_starttime'].
                ',end_time='.$param['goods_same_endtime'].'  where goods_id in (select id from '.C('DB_PREFIX').'lionfish_comshop_goods where `grounding` =1) ';
                M()->execute($sql);
                 **/

            }



            //--end

            //M('lionfish_comshop_good_common')->where("1")->save($param1);

            show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
        }

        $data = D('Seller/Config')->get_all_config();

        if(empty($data['goods_same_starttime']))
        {
            $data['goods_same_starttime'] = time();
        }

        if(empty($data['goods_same_endtime']))
        {
            $data['goods_same_endtime'] = time()+86400;
        }



        $this->data = $data;

        $this->display();
    }
    public function buyorder(){
        $pindex = I('get.page', 1);
        $psize = 20;

        $starttime_arr = I('get.time');
        $export = I('get.export');
        $searchfield = I('get.searchfield');
        $starttime = isset($starttime_arr['start']) ? strtotime($starttime_arr['start']) : strtotime(date('Y-m-d'.' 00:00:00'));

        $endtime = isset($starttime_arr['end']) ? strtotime($starttime_arr['end']) : strtotime(date('Y-m-d'.' 23:59:59'));


        $this->starttime = $starttime;
        $this->endtime = $endtime;

        $searchtime = I('get.searchtime','');

        $this->searchtime = $searchtime;

        //---begin
        $psize = 10;

        $condition = ' WHERE status in (1,2)';
        $sqlcondition = "";

        $keyword = I('get.keyword','');
        $package_id = I('get.package_id','');
        $this->keyword = $keyword;

        if (!(empty($keyword))) {
            if(!empty($searchfield)){
                $condition .= " AND  (`{$searchfield}` = '{$keyword}' ) ";
            }else{
                $condition .= " AND  ( `card_name` LIKE '%{$keyword}%' or `member_id` = '{$keyword}' ) ";
            }
        }
        if (!(empty($package_id))) {
            $condition .= " AND  ( `package_id` = '{$package_id}' ) ";
        }
        if( !empty($starttime_arr) )
        {
            $condition .= ' AND  (addtime >='.$starttime.' and addtime <= '.$endtime.' )';
        }
        $sql = 'SELECT COUNT(id) as count FROM ' .C('DB_PREFIX'). 'lionfish_comshop_coin_order ' .$sqlcondition.  $condition ;

        //dump($sql);die;
        $total_arr = M()->query($sql);
        $total = $total_arr[0]['count'];


        //'sortby' =>$sortby,'sortfield' => 'day_salescount',

        $sortby = I('get.sortby');
        $sortfield = I('get.sortfield');

        $this->sortfield = $sortfield;

        $sortby = (!empty($sortby) ? ($sortby== 'asc' ?'desc':'asc') : ( !empty($sortfield) ? 'desc':'' ) );

        $this->sortby = $sortby;

        $pager = pagination2($total, $pindex, $psize);
        $sort_way = '`hxtime` DESC';
        if (!(empty($total))) {
            $sql = 'SELECT * FROM ' .C('DB_PREFIX'). 'lionfish_comshop_coin_order '  .$sqlcondition . $condition . ' 
					ORDER BY   '.$sort_way.' ';
            if (I('export') != 1) { // 如果导出excel 就显示全部
                $sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
            }
            //$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
            $list = M()->query($sql);
            foreach ($list as &$item){
                $member = M('lionfish_comshop_member')->where( array('member_id' => $item['member_id'] ) )->find();
                $item['username'] = $member['username'];
                $item['avatar'] = $member['avatar'];
            }

        }else{
            $list = [];
        }

        if($export == 1){
            $sql = 'SELECT * FROM ' .C('DB_PREFIX'). 'lionfish_comshop_coin_order '  .$sqlcondition . $condition . ' 
					ORDER BY   '.$sort_way.' ';
            $list = M()->query($sql);
            if ($list) {
                foreach ($list as &$item){
                    $member = M('lionfish_comshop_member')->where( array('member_id' => $item['member_id'] ) )->find();
                    $item['username'] = $member['username'];
                    $item['goods_num'] = 1;
                    $item['addtime'] = date('Y-m-d H:i:s',$item['addtime']);
                    if(!empty($item['hxtime'])){
                        $item['hxtime'] = date('Y-m-d H:i:s',$item['hxtime']);
                    }
                    if(!empty($item['enhxtime'])){
                        $item['enhxtime'] = date('Y-m-d H:i:s',$item['enhxtime']);
                    }
                    if($item['status'] == 0){
                        $item['status'] = '待核销';
                    }elseif ($item['status'] == 1){
                        $item['status'] = '待确认核销';
                    }elseif ($item['status'] == 2){
                        $item['status'] = '订单完成';
                    }elseif ($item['status'] == 3){
                        $item['status'] = '冻结中';
                    }else{
                        $item['status'] = '已失效';
                    }
                    //$item['address_name']?$item['address_name']:$item['tuan_send_address']
                    $address = $item['address_name']?$item['address_name']:$item['tuan_send_address'];
                    $item['address'] =  $item['province_name'].$item['city_name'].$item['country_name'].$address;
                }
            }else{
                $list = [];
            }
            @set_time_limit(0);
            $columns = array(
                array('title' => '兑换券卡号', 'field' => 'card_no', 'width' => 30),
                array('title' => '商品名称', 'field' => 'goods_name', 'width' => 24),
                array('title' => '套餐名称', 'field' => 'card_name', 'width' => 12),
                array('title' => '会员姓名', 'field' => 'username', 'width' => 12),
                array('title' => '生成时间', 'field' => 'addtime', 'width' => 18),
                array('title' => '订单状态', 'field' => 'status', 'width' => 12),
                array('title' => '核销人', 'field' => 'hxname', 'width' => 12),
                array('title' => '核销人手机号', 'field' => 'hxmobile', 'width' => 14),
                array('title' => '地址', 'field' => 'address', 'width' => 20),
                array('title' => '门牌号', 'field' => 'lou_men_hao', 'width' => 12),
                array('title' => '用户兑换备注', 'field' => 'user_remake', 'width' => 12),
                array('title' => '团长姓名', 'field' => 'disname', 'width' => 12),
                array('title' => '团长位置', 'field' => 'pickaddress', 'width' => 20),                
                array('title' => '核销时间', 'field' => 'hxtime', 'width' => 18),
                array('title' => '确认核销时间', 'field' => 'enhxtime', 'width' => 18)
            );
            $fileName = date('YmdHis', time());
            D('Seller/Excel')->export($list, array('title' => '卡券订单数据'.$fileName, 'columns' => $columns));
        }

        $this->searchfield = $searchfield;
        //dump($sql);die;
        $this->keyword = $keyword;
        $this->total = $total;
        $this->all_count = $total;
        $this->pager = $pager;
        $this->assign('list',$list);// 赋值数据集
        $this->assign('pager',$pager);// 赋值分页输出
        $this->display();
    }
    public function orderdetail(){
        $id = I('get.id');
        $shop_domain = D('Home/Front')->get_config_by_name('shop_domain');
        $item = M('lionfish_comshop_coin_order')->where(array('id'=>$id))->find();
        $member = M('lionfish_comshop_member')->where( array('member_id' => $item['member_id'] ) )->find();

        $this->item = $item;
        $this->member = $member;
        $this->display();
    }
    public function order(){
        $pindex = I('get.page', 1);
        $psize = 20;

        $starttime_arr = I('get.time');
        $export = I('get.export');
        $starttime = isset($starttime_arr['start']) ? strtotime($starttime_arr['start']) : strtotime(date('Y-m-d'.' 00:00:00'));

        $endtime = isset($starttime_arr['end']) ? strtotime($starttime_arr['end']) : strtotime(date('Y-m-d'.' 23:59:59'));


        $this->starttime = $starttime;
        $this->endtime = $endtime;

        $searchtime = I('get.searchtime','');

        $this->searchtime = $searchtime;

        //---begin
        $psize = 10;

        $condition = ' WHERE 1=1';

        $sqlcondition = "";

        $keyword = I('get.keyword','');
        $package_id = I('get.package_id','');
        $this->keyword = $keyword;

        if (!(empty($keyword))) {
            $condition .= " AND  ( `card_name` LIKE '%{$keyword}%' or `member_id` = '{$keyword}' ) ";
        }
        if (!(empty($package_id))) {
            $condition .= " AND  ( `package_id` = '{$package_id}' ) ";
        }
        if( !empty($starttime_arr) )
        {
            $condition .= ' AND  (addtime >='.$starttime.' and addtime <= '.$endtime.' )';
        }
        $sql = 'SELECT COUNT(id) as count FROM ' .C('DB_PREFIX'). 'lionfish_comshop_coin_order ' .$sqlcondition.  $condition ;

        //dump($sql);die;
        $total_arr = M()->query($sql);
        $total = $total_arr[0]['count'];


        //'sortby' =>$sortby,'sortfield' => 'day_salescount',

        $sortby = I('get.sortby');
        $sortfield = I('get.sortfield');

        $this->sortfield = $sortfield;

        $sortby = (!empty($sortby) ? ($sortby== 'asc' ?'desc':'asc') : ( !empty($sortfield) ? 'desc':'' ) );

        $this->sortby = $sortby;

        $pager = pagination2($total, $pindex, $psize);
        $sort_way = '`id` DESC';
        if (!(empty($total))) {
            $sql = 'SELECT * FROM ' .C('DB_PREFIX'). 'lionfish_comshop_coin_order '  .$sqlcondition . $condition . ' 
					ORDER BY   '.$sort_way.' ';
            if (I('export') != 1) { // 如果导出excel 就显示全部
                $sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
            }
            //$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
            $list = M()->query($sql);
            foreach ($list as &$item){
                $member = M('lionfish_comshop_member')->where( array('member_id' => $item['member_id'] ) )->find();
                $item['username'] = $member['username'];
                $item['avatar'] = $member['avatar'];
            }

        }else{
            $list = [];
        }

        if($export == 1){
            $sql = 'SELECT * FROM ' .C('DB_PREFIX'). 'lionfish_comshop_coin_order '  .$sqlcondition . $condition . ' 
					ORDER BY   '.$sort_way.' ';
            $list = M()->query($sql);
            if ($list) {
                foreach ($list as &$item){
                    $member = M('lionfish_comshop_member')->where( array('member_id' => $item['member_id'] ) )->find();
                    $item['username'] = $member['username'];
                    $item['addtime'] = date('Y-m-d H:i:s',$item['addtime']);
                    if(!empty($item['hxtime'])){
                        $item['hxtime'] = date('Y-m-d H:i:s',$item['hxtime']);
                    }
                    if(!empty($item['enhxtime'])){
                        $item['enhxtime'] = date('Y-m-d H:i:s',$item['enhxtime']);
                    }
                  	if($item['status'] == 0){
                        $item['status'] = '已生成';
                    }elseif ($item['status'] == 1){
                        $item['status'] = '代发货';
                    }elseif ($item['status'] == 2){
                        $item['status'] = '订单完成';
                    }elseif ($item['status'] == 3){
                        $item['status'] = '冻结中';
                    }else{
                        $item['status'] = '已失效';
                    }
                    $item['address'] =  $item['province_name'].','.$item['city_name'].','.$item['country_name'].$item['address_name']?$item['address_name']:$item['tuan_send_address'];
                }
            }else{
                $list = [];
            }
            @set_time_limit(0);
            $columns = array(
                array('title' => '兑换券卡号', 'field' => 'card_no', 'width' => 24),
                array('title' => '商品名称', 'field' => 'goods_name', 'width' => 24),
                array('title' => '套餐名称', 'field' => 'card_name', 'width' => 12),
                array('title' => '会员姓名', 'field' => 'username', 'width' => 12),
                array('title' => '生成时间', 'field' => 'addtime', 'width' => 24),
              	array('title' => '订单状态', 'field' => 'status', 'width' => 12),
                array('title' => '核销人', 'field' => 'hxname', 'width' => 12),
                array('title' => '核销人手机号', 'field' => '	hxmobile', 'width' => 12),
                array('title' => '地址', 'field' => 'address', 'width' => 12),
                array('title' => '门牌号', 'field' => 'lou_men_hao', 'width' => 12),
                array('title' => '用户兑换备注', 'field' => 'user_remake', 'width' => 12),
                array('title' => '团长姓名', 'field' => 'disname', 'width' => 12),
                array('title' => '团长位置', 'field' => 'pickaddress', 'width' => 12),

                array('title' => '确认核销日期', 'field' => 'enhxtime', 'width' => 12),

            );
            $fileName = date('YmdHis', time());
            D('Seller/Excel')->export($list, array('title' => '卡券订单数据'.$fileName, 'columns' => $columns));
        }


        //dump($sql);die;
        $this->keyword = $keyword;
        $this->total = $total;
        $this->all_count = $total;
        $this->pager = $pager;
        $this->assign('list',$list);// 赋值数据集
        $this->assign('pager',$pager);// 赋值分页输出
        $this->display();
    }
    public function hexiaoorder(){
        $id = I('get.id');
        $shop_domain = D('Home/Front')->get_config_by_name('shop_domain');
        $goods = M('lionfish_comshop_coin_order')->where(array('id'=>$id))->find();
        if($goods['status'] == 1){
            $data = [
                'enhxtime'=>time(),
                'remake'=>'确认核销',
                'status'=>2
            ];
            M('lionfish_comshop_coin_order')->where(array('id'=>$id))->save($data);

            show_json(1, array('message' => '确认核销成功！','url' => $shop_domain.'/seller.php?s=/coin/order' ));
        }
    }
    public function addgoods()
    {
        if (IS_POST) {
            $_GPC = I('request.');

            $data = [
                'name'=>$_GPC['name'],
                'gid'=>$_GPC['gid'],
                'card_id'=>$_GPC['card_id'],
                'num'=>$_GPC['num'],
                'status'=>$_GPC['status'],
                'addtime'=>time(),
                'remake'=>$_GPC['remake'],
                'coinunit'=>empty($_GPC['coinunit']) ? '张' : $_GPC['coinunit']
            ];
            $goods = M('lionfish_comshop_goods')->field('id,goodsname')->where(array('id'=>$_GPC['gid']))->find();
            $goodsImg = M('lionfish_comshop_goods_images')->where(array('goods_id'=>$_GPC['gid']))->find();
            $data['goods_img'] = $goodsImg['image'];
            $data['goods_name'] = $goods['goodsname'];
            $list = M('lionfish_comshop_coin')->add($data);
            $http_refer = S('HTTP_REFERER');

            $http_refer = empty($http_refer) ? $_SERVER['HTTP_REFERER'] : $http_refer;
            sellerLog('添加新增商品', 3);

            show_json(1, array('message' => '添加商品成功！','url' => $http_refer ));
        }
        S('HTTP_REFERER', $_SERVER['HTTP_REFERER']);
        $category = D('Seller/GoodsCategory')->getFullCategory(true, true);
        $this->category = $category;

        $spec_list = D('Seller/Spec')->get_all_spec();
        $this->spec_list = $spec_list;

        $card = M('lionfish_comshop_package_goods')->field('id,goodsname')->select();
        $goods = M('lionfish_comshop_goods')->field('id,goodsname')->where('is_package = 1')->select();
        $set =  D('Seller/Config')->get_all_config();
        $this->set = $set;

        $config_data = $set;
        $this->config_data = $config_data;
        $this->card = $card;
        $this->goods = $goods;

        $this->display();
    }


    public function category_enabled()
    {

        $id = I('request.id');

        if (empty($id)) {
            $ids = I('request.ids');
            $id = (is_array($ids) ? implode(',', $ids) : 0);
        }


        $items = M('lionfish_comshop_goods_category')->field('id,name')->where( 'id in( ' . $id . ' )' )->select();

        $enabled = I('request.enabled');

        foreach ($items as $item) {

            M('lionfish_comshop_goods_category')->where( array('id' => $item['id']) )->save(  array('is_show' => intval($enabled)) );

        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
    }


    public function category_typeenabled()
    {
        $_GPC = I('request.');

        $id = intval($_GPC['id']);

        if (empty($id)) {
            $id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
        }

        $items = M('lionfish_comshop_goods_category')->field('id,name')->where( 'id in( ' . $id . ' )' )->select();

        foreach ($items as $item) {

            M('lionfish_comshop_goods_category')->where( array('id' => $item['id']) )->save( array('is_type_show' => intval($_GPC['enabled']))  );

        }
        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
    }

    public function mult_tpl()
    {

        $tpl = I('get.tpl','','trim');
        $spec_str = I('post.spec_str', '', 'trim');
        $options_name = I('post.options_name','','trim');
        $cur_cate_id = I('post.cur_cate_id',0);


        if ($tpl == 'spec') {
            $spec = array('id' => random(32), 'title' => $options_name);

            $need_items = array();
            $spec_list = explode('@', $spec_str);
            foreach($spec_list as $itemname)
            {
                $tmp_item = array('id' =>random(32),'title' => $itemname, 'show' => 1);
                $need_items[] = $tmp_item;
            }
            $spec['items'] = $need_items;

            $this->spec = $spec;
            $this->tmp_item = $tmp_item;

            $this->tpl = $tpl;
            $this->spec_str = $spec_str;
            $this->options_name = $options_name;
            $this->cur_cate_id = $cur_cate_id;

            $this->display('Goods/tpl/spec');
        }
    }
    public function ajax_batchtime_pintuan()
    {
        $begin_time = I('request.begin_time');
        $goodsids = I('request.goodsids');
        $end_time = I('request.end_time');

        foreach ($goodsids as $goods_id ) {
            if($begin_time && $end_time){
                $param = array();
                $param['begin_time'] = strtotime($begin_time);
                $param['end_time'] = strtotime($end_time);

                M('lionfish_comshop_good_common')->where( array('goods_id' => $goods_id) )->save( $param );

                M('lionfish_comshop_good_pin')->where( array('goods_id' => $goods_id) )->save( $param );
            }
        }
        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

    }

    public function ajax_batchtime()
    {

        $begin_time = I('request.begin_time');
        $goodsids = I('request.goodsids');
        $end_time = I('request.end_time');

        foreach ($goodsids as $goods_id ) {
            if($begin_time && $end_time){
                $param = array();
                $param['begin_time'] = strtotime($begin_time);
                $param['end_time'] = strtotime($end_time);

                M('lionfish_comshop_good_common')->where( array('goods_id' => $goods_id) )->save( $param );
            }
        }
        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
    }

    public function tpl()
    {

        $tpl = I('get.tpl');
        $title = I('get.title','');

        if ($tpl == 'spec') {
            $spec = array('id' => random(32), 'title' => $title);

            $this->title = $title;
            $this->spec = $spec;
            $this->display('Goods/tpl/spec');
        }else  if($tpl == 'specitem')
        {
            $specid = I('get.specid');
            $spec = array('id' => $specid);
            $specitem = array('id' => random(32), 'title' => $title, 'show' => 1);

            $this->specid = $specid;
            $this->spec = $spec;
            $this->specitem = $specitem;

            $this->display('Goods/tpl/spec_item');
        }


    }


    public function config()
    {

        if (IS_POST) {

            $data = I('post.parameter', array());
            $data['goods_stock_notice'] = trim($data['goods_stock_notice']);
            $data['instructions'] = trim($data['instructions']);
            $data['is_show_buy_record'] = trim($data['is_show_buy_record']);
            $data['is_show_list_timer'] = intval($data['is_show_list_timer']);
            $data['is_show_list_count'] = intval($data['is_show_list_count']);
            $data['is_show_comment_list'] = intval($data['is_show_comment_list']);
            $data['is_show_new_buy'] = intval($data['is_show_new_buy']);
            $data['is_show_ziti_time'] = intval($data['is_show_ziti_time']);


            $data['is_show_spike_buy'] = intval($data['is_show_spike_buy']);
            $data['goodsdetails_addcart_bg_color'] = $data['goodsdetails_addcart_bg_color'];
            $data['goodsdetails_buy_bg_color'] = $data['goodsdetails_buy_bg_color'];
            $data['is_show_guess_like'] = $data['is_show_guess_like'];

            $data['show_goods_guess_like'] = $data['show_goods_guess_like'];
            if(!empty($data['num_guess_like'])){
                $data['num_guess_like'] = $data['num_guess_like'];
            }else{
                $data['num_guess_like'] = 8;
            }



            $data['isopen_community_group_share'] = intval($data['isopen_community_group_share']);

            $data['group_share_avatar'] = save_media($data['group_share_avatar']);
            $data['group_share_title'] = trim($data['group_share_title']);
            $data['group_share_desc'] = trim($data['group_share_desc']);
            $data['is_close_details_time'] = intval($data['is_close_details_time']);

            $data['videolist_nav_title'] = trim($data['videolist_nav_title']);
            $data['videolist_share_title'] = trim($data['videolist_share_title']);
            $data['videolist_share_poster'] = save_media($data['videolist_share_poster']);

            $data['goods_details_title_bg'] = save_media($data['goods_details_title_bg']);

            D('Seller/Config')->update($data);

            //旧的的域名
            $present_realm_name = I('post.present_realm_name');
            //修改商品详情域名
            $new_realm_name = I('post.new_realm_name');

            if(!empty($new_realm_name)){

                $str="/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/";
                if (!preg_match($str,$present_realm_name)){
                    show_json(0, array('message' => '旧的域名格式不正确'));
                }

                if (!preg_match($str,$new_realm_name)){
                    show_json(0, array('message' => '新的域名格式不正确'));
                }
                $sql = " update ". C('DB_PREFIX') ."lionfish_comshop_good_common set content = replace( content , '".$present_realm_name."' , '".$new_realm_name."' ) ";
                $list = M()->execute($sql);
                if(empty($list)){
                    show_json(0, array('message' => '商品详情中不存在该域名，或者不能填写相同的域名，请检查后重新填写'));
                }
            }

            show_json(1, array('url'=> U('goods/config')));
        }
        $data = D('Seller/Config')->get_all_config();
        $this->data = $data;
        $this->display();
    }

    function addspec()
    {
        global $_W;
        global $_GPC;

        if (IS_POST) {

            $data = I('post.data');

            D('Seller/Spec')->update($data);

            show_json(1, array('url' => U('goods/goodsspec')));
        }

        $this->display();
    }

    public function editspec()
    {

        $id =  I('request.id');
        if (!empty($id)) {

            $item = M('lionfish_comshop_spec')->where( array('id' => $id) )->find();

            if (json_decode($item['value'], true)) {
                $labelname = json_decode($item['value'], true);
            }
            else {
                $labelname = unserialize($item['value']);
            }
        }

        if (IS_POST) {

            $data = I('post.data');

            D('Seller/Spec')->update($data);

            show_json(1, array('url' => U('goods/goodsspec')));
        }
        $this->item = $item;
        $this->labelname = $labelname;
        $this->display('Goods/addspec');
    }

    public function deletespec()
    {
        $id = I('get.id');

        if (empty($id)) {
            $ids = I('post.ids');
            $id = (is_array($ids) ? implode(',', $ids) : 0);
        }

        $items = M('lionfish_comshop_spec')->field('id,name')->where( array('id' => array('in', $id)) )->select();

        if (empty($item)) {
            $item = array();
        }

        foreach ($items as $item) {
            M('lionfish_comshop_spec')->where( array('id' => $item['id']) )->delete();
        }

        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));

    }

    public function addcategory()
    {

        $data = array();
        $pid = I('get.pid', 0);
        $id = I('get.id', 0);

        if (IS_POST) {

            $data = I('post.data');

            D('Seller/GoodsCategory')->update($data);

            show_json(1, array('url' => U('goods/goodscategory')));
        }

        if($id >0 )
        {
            $data = M('lionfish_comshop_goods_category')->where( array('id' => $id) )->find();

            $this->data = $data;
        }

        $this->pid = $pid;
        $this->id = $id;

        $this->display();
    }

    public function category_delete()
    {

        $id = I('get.id');

        $item = M('lionfish_comshop_goods_category')->field('id, name, pid')->where( array('id' => $id) )->find();


        M('lionfish_comshop_goods_category')->where( "id={$id} or pid={$id}" )->delete();


        //m('shop')->getCategory(true);
        show_json(1, array('url' => U('goods/goodscategory')));
    }

    public function goodscategory()
    {

        if (IS_POST) {
            $datas = I('post.datas');
            if (!empty($datas)) {
                D('Seller/GoodsCategory')->goodscategory_modify($datas);
                show_json(1 , array('url' => U('goods/goodscategory') ));
            }

            $parameter = I('post.parameter');

            if (!empty($parameter)) {
                $data = ((is_array($parameter) ? $parameter : array()));
                D('Seller/Config')->update($data);
                show_json(1);
            }
        }

        $children = array();


        $category = M('lionfish_comshop_goods_category')->where(' cate_type="normal" ')->order('pid ASC, sort_order DESC')->select();


        foreach ($category as $index => $row) {
            if (!empty($row['pid'])) {
                $children[$row['pid']][] = $row;
                unset($category[$index]);
            }
        }


        $data = D('Seller/Config')->get_all_config();

        $this->data = $data;

        $this->children = $children;
        $this->category = $category;
        $this->display();
    }

    public function goodsspec()
    {
        $condition = ' 1=1 and spec_type="normal" ';
        $pindex =  I('get.page',1);
        $psize = 20;

        $enabled = I('get.enabled');

        if ($enabled != '') {
            $condition .= ' and state=' . intval($enabled);
        }

        $keyword = I('get.keyword','','trim');

        if (!empty($keyword)) {
            $condition .= ' and name like "%'.$keyword.'%" ';
        }

        $offset = ($pindex - 1) * $psize;


        $label = M('lionfish_comshop_spec')->field('id,name,value')->where($condition)->order(' id desc ')->limit($offset, $psize)->select();

        $total = M('lionfish_comshop_spec')->where( $condition )->count();

        $cur_url = U('goods/goodsspec', array('enabled' => $enabled,'keyword' => $keyword));
        $pager = pagination2($total, $pindex, $psize);
        foreach( $label as &$val )
        {
            $val['value'] = unserialize($val['value']);
            $val['value_str'] = !empty($val['value']) ? implode(',', $val['value']) : '';
        }

        $this->keyword = $keyword;
        $this->label = $label;
        $this->total = $total;
        $this->pager = $pager;

        $this->display();
    }
    /**
    搜索全部商品，可添加虚拟评价
     **/
    public function goods_search_all()
    {
        $goods_name = I('post.goods_name','');

        $where = "   status=1 and quantity>0 and store_id = " . $this->sellerid;
        if(!empty($goods_name))
        {
            $where .=  "  and name like '%".$goods_name."%' ";
        }


        $goods_list = M('goods')->where($where)->limit(20)->select();

        $this->goods_list = $goods_list;
        $result = array();

        $result['html'] = $this->fetch('Goods:goods_list_fetch');


        echo json_encode($result);
        die();
    }

    function toggle_statues_show()	{
        $goods_id = I('post.gid',0);
        $goods_info =M('Goods')->where( array('goods_id' => $goods_id) )->find();
        $status = $goods_info['status'] == 1 ? 0: 1;
        $res = M('Goods')->where( array('goods_id' => $goods_id) )->save( array('status' => $status) );
        echo json_encode( array('code' => 1) );
        die();
    }

    /**
    搜索可报名的商品
     **/
    public function goods_search_voucher()
    {
        $goods_name = I('post.goods_name','');

        $where = "  (type='normal' or type ='pintuan')  and status=1 and quantity>0 and store_id = " . $this->sellerid;
        if(!empty($goods_name))
        {
            $where .=  "  and ( name like '%".$goods_name."%'  or goods_id like '%".$goods_name."%') ";
        }

        $goods_list = M('goods')->where($where)->limit(10)->select();

        $this->goods_list = $goods_list;
        $result = array();

        $result['html'] = $this->fetch('Goods:goods_list_fetch');


        echo json_encode($result);
        die();
    }
    /**
    搜索可报名的商品
     **/
    public function goods_search()
    {

        $goods_name = I('post.goods_name','');

        $where = "  type='normal'  and status=1 and quantity>0 and store_id = " . $this->sellerid;
        if(!empty($goods_name))
        {
            $where .=  "  and name like '%".$goods_name."%' ";
        }


        $goods_list = M('goods')->where($where)->limit(20)->select();

        $this->goods_list = $goods_list;
        $result = array();

        $result['html'] = $this->fetch('Goods:goods_list_fetch');


        echo json_encode($result);
        die();
    }

    public function query_normal()
    {
        $_GPC = I('request.');
        $kwd = trim($_GPC['keyword']);
        $is_recipe = isset($_GPC['is_recipe']) ? intval($_GPC['is_recipe']) : 0 ;

        $is_soli = isset($_GPC['is_soli']) ? intval($_GPC['is_soli']) : 0 ;


        $params = array();


        $type = isset($_GPC['type']) ? $_GPC['type']:'normal';

        $condition = '  type = "'.$type.'" and grounding = 1 and is_seckill =0 ';


        if (!empty($kwd)) {
            $condition .= ' AND `goodsname` LIKE "%' . $kwd . '%" ';
        }

        if( isset($_GPC['unselect_goodsid']) && $_GPC['unselect_goodsid'] > 0 )
        {
            $condition .= ' AND `id` != '.$_GPC['unselect_goodsid'];
        }

        if( $is_soli == 1 )
        {
            $head_id = $_GPC['head_id'];

            $goods_ids_arr = M('lionfish_community_head_goods')->field('goods_id')->where(  "head_id in ({$head_id})" )->order('id desc')->select();

            $ids_arr = array();
            foreach($goods_ids_arr as $val){
                $ids_arr[] = $val['goods_id'];
            }
            if( !empty($ids_arr) )
            {
                $ids_str = implode(',',$ids_arr);

                $condition .= "  and ( is_all_sale = 1 or id in ({$ids_str}) )   ";
            }else{
                $condition .= "  and ( is_all_sale = 1  )  ";
            }
            //is_all_sale
        }
        //todo....

        $ds = M('lionfish_comshop_goods')->field('id as gid,goodsname,subtitle,price,productprice')->where( $condition )->select();
        $s_html = "";
        foreach ($ds as &$d) {
            //thumb
            $thumb = M('lionfish_comshop_goods_images')->where( array('goods_id' =>$d['gid'] ) )->order('id asc')->find();
            $d['thumb'] =  tomedia($thumb['image']);



            $s_html.= '<tr>';
            $s_html.="  <td><img src='".tomedia($d['thumb'])."' style='width:30px;height:30px;padding1px;border:1px solid #ccc' /> ".$d['goodsname']."</td>";


            if (  isset($_GPC['template'])  && $_GPC['template'] == 'mult' ) {
                if( $is_recipe == 1 )
                {
                    $s_html.='  <td style="width:80px;"><a href="javascript:;" class="choose_dan_link_recipe" data-json=\''.json_encode($d).'\'>选择</a></td>';
                }else{
                    $s_html.='  <td style="width:80px;"><a href="javascript:;" class="choose_dan_link_goods" data-json=\''.json_encode($d).'\'>选择</a></td>';
                }

            }else{
                $s_html.='  <td style="width:80px;"><a href="javascript:;" class="choose_dan_link" data-json=\''.json_encode($d).'\'>选择</a></td>';
            }



            $s_html.="</tr>";
        }

        unset($d);


        if( isset($_GPC['is_ajax']) )
        {
            echo json_encode( array('code' => 0, 'html' =>$s_html ) );
            die();
        }

        $this->ds = $ds;
        $this->_GPC = $_GPC;

        if (  isset($_GPC['template'])  && $_GPC['template'] == 'mult' ) {

            if( $is_recipe == 1 )
            {
                $this->display('Goods/query_normal_mult_recipe');
            }else{
                $this->display('Goods/query_normal_mult');
            }
        }else{
            $this->display();
        }

    }

    /**
     * 获取商品规格情况
     */

    function get_ajax_search_goods_info()
    {
        $goods_id = I('get.goods_id');
        $is_hide = I('get.is_hide',0);
        $type = I('get.type','pin');
        //'type' => 'bargain'

        $this->is_hide = $is_hide;
        $goods_info = M('goods')->field('name,goods_id,price,danprice')->where( array('goods_id' => $goods_id) )->find();

        $model=new GoodsModel();
        $this->goods_options=$model->get_goods_options($goods_id, UID);

        $goods_option_mult_value = M('goods_option_mult_value')->where( array('goods_id' => $goods_id ) )->select();
        $goods_option_mult_str = '';

        if( !empty($goods_option_mult_value) )
        {
            $goods_option_mult_arr = array();
            foreach($goods_option_mult_value as $key => $val)
            {
                $goods_option_mult_arr[] = 'mult_id:'.$val['rela_goodsoption_valueid'].'@@mult_qu:'.$val['quantity'].'@@mult_image:'.$val['image'];
                //option_value  option_value_id  value_name
                $option_name_arr = explode('_', $val['rela_goodsoption_valueid']);
                $option_name_list = array();


                foreach($option_name_arr as $option_value_id_tp)
                {
                    $tp_op_val_info =M('option_value')->where( array('option_value_id' => $option_value_id_tp) )->find();
                    $option_name_list[] = $tp_op_val_info['value_name'];
                }

                $val['option_name_list'] = $option_name_list;
                $goods_option_mult_value[$key] = $val;
            }
            $goods_option_mult_str = implode(',', $goods_option_mult_arr);
        }

        $this->goods_option_mult_value = $goods_option_mult_value;
        $this->goods_option_mult_str = $goods_option_mult_str;
        $this->goods_info = $goods_info;

        $result = array();
        if($type == 'bargain')
        {
            $result['html'] = $this->fetch('Goods:goods_option_fetch_bargain');
        }
        else if($type == 'integral'){
            $result['html'] = $this->fetch('Goods:goods_option_fetch_integral');
        }else{
            $result['html'] = $this->fetch('Goods:goods_option_fetch');
        }

        echo json_encode($result);
        die();
    }
    function toggle_index_sort()
    {
        $goods_id = I('post.gid',0);
        $index_sort = I('post.index_sort',0,'intval');
        $res = M('Goods')->where( array('goods_id' => $goods_id) )->save( array('index_sort' => $index_sort) );
        echo json_encode( array('code' => 1) );
        die();
    }
    function toggle_index_show()
    {
        $goods_id = I('post.gid',0);
        $goods_info =M('Goods')->where( array('goods_id' => $goods_id) )->find();
        $is_index_show = $goods_info['is_index_show'] == 1 ? 0: 1;

        $res = M('Goods')->where( array('goods_id' => $goods_id) )->save( array('is_index_show' => $is_index_show) );
        echo json_encode( array('code' => 1) );
        die();
    }
    /**
     * 活动商品
     */
    public function activity()
    {
        $this->breadcrumb2='活动商品信息';

        $model=new GoodsModel();

        $filter=I('get.');


        $search=array('store_id' => SELLERUID);

        if(isset($filter['name'])){
            $search['name']=$filter['name'];
        }
        if(isset($filter['category'])){
            $search['category']=$filter['category'];
            $this->get_category=$search['category'];
        }
        if(isset($filter['status'])){
            $search['status']=$filter['status'];
            $this->get_status=$search['status'];
        }

        if(isset($filter['type'])){
            $search['type']=$filter['type'];
            $this->type=$search['type'];
        }else {
            $search['type']='activity';
            $this->type=$search['type'];
        }
        //type

        $data=$model->show_goods_page($search);

        $store_bind_class = M('store_bind_class')->where( array('seller_id' => SELLERUID) )->select();

        $cate_ids = array();
        foreach($store_bind_class as $val)
        {
            if( !empty($val['class_1'])) {
                $cate_ids[] = $val['class_1'];
            }
            if( !empty($val['class_2'])) {
                $cate_ids[] = $val['class_2'];
            }
            if( !empty($val['class_3'])) {
                $cate_ids[] = $val['class_3'];
            }
        }
        if(empty($cate_ids)) {
            $this->category = array();
        } else {
            $cate_ids_str = implode(',', $cate_ids);
            $category=M('goods_category')->where( array('id' => array('in',$cate_ids_str)) )->select();
            $category_tree =list_to_tree($category);
            $this->category = $category_tree;
        }

        foreach($data['list'] as $key => $goods)
        {
            $all_comment  =  M('order_comment')->where( array('goods_id' => $goods['goods_id']) )->count();
            $wait_comment =  M('order_comment')->where( array('state' => 0 ,'goods_id' => $goods['goods_id']) )->count();
            $goods['all_comment']  = $all_comment;
            $goods['wait_comment'] = $wait_comment;
            $data['list'][$key] = $goods;
        }

        $this->assign('empty',$data['empty']);// 赋值数据集
        $this->assign('list',$data['list']);// 赋值数据集
        $this->assign('page',$data['page']);// 赋值分页输出

        $this->display();
    }

    /**
    回收站商品重新上架
     **/
    public function goback()
    {
        $goods_id = I('get.id',0,'intval');
        $result = array('code' => 0);
        $goods_info = M('goods')->where( array('goods_id' => $goods_id, 'store_id' => SELLERUID) )->find();
        if(empty($goods_info))
        {
            $result['msg'] = '非法操作';
            echo json_encode($result);
            die();
        }


        $up_data = array();
        $up_data['lock_type'] = 'normal';
        $up_data['status'] = 2;//下架

        M('goods')->where( array('goods_id' => $goods_id, 'store_id' => SELLERUID) )->save($up_data);

        $result['code'] = 1;
        echo json_encode($result);
        die();
    }

    public function get_weshare_image()
    {
        $goods_id = I('get.id',0,'intval');

        //400*400 fan_image
        //get_goods_price($goods_id)
        $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();

        $goods_img = ROOT_PATH.'Uploads/image/'.$goods_info['image'];
        if( !empty($goods_info['fan_image']) )
        {
            $goods_img = ROOT_PATH.'Uploads/image/'.$goods_info['fan_image'];
        }
        $goods_model = D('Home/Goods');
        $goods_price = $goods_model->get_goods_price($goods_id);
        $goods_price['market_price'] = $goods_info['price'];
        //price
        $goods_title = $goods_info['name'];


        $need_img = $goods_model->_get_compare_zan_img($goods_img,$goods_title,$goods_price);

        //贴上二维码图
        //$rocede_path = $goods_model->_get_goods_user_wxqrcode($goods_id,$member_id);
        //$res = $goods_model->_get_compare_qrcode_bgimg($need_img['need_path'], $rocede_path);

        M('goods_description')->where( array('goods_id' =>$goods_id) )->save( array('wepro_qrcode_image' =>$need_img['need_path']) );

        echo json_encode(array('code' =>1));
        die();
    }

    /**
    加入回车站
     **/
    public function backhuiche()
    {
        $goods_id = I('get.id',0,'intval');
        $result = array('code' => 0);
        $goods_info = M('goods')->where( array('goods_id' => $goods_id, 'store_id' => SELLERUID) )->find();
        if(empty($goods_info))
        {
            $result['msg'] = '非法操作';
            echo json_encode($result);
            die();
        }
        $lock_type = $goods_info['lock_type'];

        switch($lock_type)
        {
            case 'lottery':
                M('lottery_goods')->where( array('goods_id' => $goods_id) )->delete();
                break;
            case 'super_spike':
                M('super_spike_goods')->where( array('goods_id' => $goods_id) )->delete();
                break;
            case 'spike':
                M('spike_goods')->where( array('goods_id' => $goods_id) )->delete();
                break;
            case 'subject':
            case 'free_trial':
            case 'niyuan':
            case 'oneyuan':
            case 'haitao':
                M('subject_goods')->where( array('goods_id' => $goods_id) )->delete();
                break;
        }

        $up_data = array();
        $up_data['type'] = 'normal';
        $up_data['lock_type'] = 'normal';
        $up_data['status'] = 4;//下架

        M('goods')->where( array('goods_id' => $goods_id, 'store_id' => SELLERUID) )->save($up_data);

        $result['code'] = 1;
        echo json_encode($result);
        die();
    }


    /**
    撤回活动申请
     **/
    public function backshenqing()
    {
        $goods_id = I('get.id',0,'intval');
        $result = array('code' => 0);
        $goods_info = M('goods')->where( array('goods_id' => $goods_id, 'store_id' => SELLERUID) )->find();
        if(empty($goods_info))
        {
            $result['msg'] = '非法操作';
            echo json_encode($result);
            die();
        }
        $lock_type = $goods_info['lock_type'];

        switch($lock_type)
        {
            case 'lottery':
                M('lottery_goods')->where( array('goods_id' => $goods_id) )->delete();
                break;
            case 'super_spike':
                M('super_spike_goods')->where( array('goods_id' => $goods_id) )->delete();
                break;
            case 'spike':
                M('spike_goods')->where( array('goods_id' => $goods_id) )->delete();
                break;
            case 'subject':
            case 'free_trial':
            case 'niyuan':
            case 'oneyuan':
            case 'haitao':
                M('subject_goods')->where( array('goods_id' => $goods_id) )->delete();
                break;
        }

        $up_data = array();
        $up_data['lock_type'] = 'normal';
        $up_data['status'] = 0;//下架

        M('goods')->where( array('goods_id' => $goods_id, 'store_id' => SELLERUID) )->save($up_data);

        $result['code'] = 1;
        echo json_encode($result);
        die();
    }

    ///Goods/delcomment/id/1
    /**
     * 删除评论
     */
    public function delcomment()
    {
        $id = I('get.id');
        $goods_id = I('get.goods_id');
        M('order_comment')->where( array('comment_id' => $id) )->delete();
        //echo
        $result = array(
            'status'=>'success',
            'message'=>'删除成功',
            'jump'=>U('Goods/comment_info', array('id' =>  $goods_id))
        );
        $this->osc_alert($result);
    }
    /**
     * 审核评论
     */
    public function toggle_comment_state()
    {
        $comment_id = I('post.comment_id');
        $order_comment = M('order_comment')->where( array('comment_id' => $comment_id) )->find();
        //state
        $state = $order_comment['state'] == 1 ? 0: 1;
        M('order_comment')->where( array('comment_id' => $comment_id) )->save( array('state' => $state) );
        echo json_encode( array('code' => 1) );
        die();
    }
    /**
     * 商品评论信息
     */
    public function comment_info()
    {
        $goods_id = I('get.id');
        $model=new GoodsModel();
        $search = array();
        $search['goods_id'] = $goods_id;
        $data=$model->show_comment_page($search);


        $this->assign('empty',$data['empty']);// 赋值数据集
        $this->assign('list',$data['list']);// 赋值数据集
        $this->assign('page',$data['page']);// 赋值分页输出
        $this->display();
    }

    public function ajax_batchheads()
    {

        $goodsids = I('request.goodsids');
        $head_id_arr = I('request.head_id_arr');


        $is_clear_old = I('request.is_clear_old');



        if( $is_clear_old == 1 )
        {
            foreach($goodsids as $goods_id)
            {
                M('lionfish_community_head_goods')->where( array('goods_id' => $goods_id) )->delete();
            }
        }


        foreach($head_id_arr as $head_id)
        {
            foreach($goodsids as $goods_id)
            {
                D('Seller/Communityhead')->insert_head_goods($goods_id, $head_id);
            }
        }
        show_json(1);
    }


    public function ajax_batchcates_headgroup()
    {
        $_GPC = I('request.');

        $goodsids = $_GPC['goodsids'];
        $groupid = $_GPC['groupid'];

        if( $groupid == 'default')
        {
            $groupid = 0;
        }


        $head_list = M('lionfish_community_head')->field('id')->where( array("groupid" => $groupid, 'state' => 1 ) )->select();

        $is_clear_old = $_GPC['is_clear_old'];



        if( $is_clear_old == 1 )
        {
            foreach($goodsids as $goods_id)
            {

                M('lionfish_community_head_goods')->where( array('goods_id' => $goods_id ) )->delete();
            }
        }



        if( !empty($head_list) )
        {
            foreach($head_list as $val)
            {
                foreach($goodsids as $goods_id)
                {
                    D('Seller/Communityhead')->insert_head_goods($goods_id, $val['id']);
                }
            }
        }

        show_json(1);
    }


    public function ajax_batchcates()
    {

        $iscover =  I('request.iscover');
        $goodsids = I('request.goodsids');
        $cates = I('request.cates');

        if( !is_array($cates) )
        {
            $cates = array($cates);
        }

        foreach ($goodsids as $goods_id ) {

            if( $iscover == 1)
            {
                //覆盖，即删除原有的分类

                M('lionfish_comshop_goods_to_category')->where( array('goods_id' => $goods_id) )->delete();

                foreach($cates as $cate_id)
                {
                    $post_data_cate = array();
                    $post_data_cate['cate_id'] = $cate_id;
                    $post_data_cate['goods_id'] = $goods_id;
                    M('lionfish_comshop_goods_to_category')->add($post_data_cate);
                }
            }else{
                foreach($cates as $cate_id)
                {
                    //仅更新

                    $item = M('lionfish_comshop_goods_to_category')->where( array('goods_id' => $goods_id,'cate_id' => $cate_id) )->find();

                    if(empty($item))
                    {
                        $post_data_cate = array();
                        $post_data_cate['cate_id'] = $cate_id;
                        $post_data_cate['goods_id'] = $goods_id;
                        M('lionfish_comshop_goods_to_category')->add($post_data_cate);
                    }
                }

            }
        }
        show_json(1);
    }

    public function lotteryinfo()
    {
        $goods_id = I('get.id',0);
        $lottery_goods = M('lottery_goods')->where( array('goods_id' =>$goods_id) )->find();

        if(empty($lottery_goods)){
            die('非法操作');
        }//store_id
        $page = I('get.page',1);
        $per_page = 4;
        $offset = ($page - 1) * $per_page;

        $sql = "select m.uname,m.avatar,p.pin_id,p.lottery_state,o.lottery_win,o.order_id,o.pay_time from ".C('DB_PREFIX')."pin as p,".C('DB_PREFIX')."pin_order as po,
	           ".C('DB_PREFIX')."order as o,".C('DB_PREFIX')."order_goods as og,".C('DB_PREFIX')."member as m 
	               where p.state = 1 and p.pin_id = po.pin_id and po.order_id = o.order_id 
	                and o.order_id = og.order_id and og.goods_id and o.member_id = m.member_id and og.store_id =".SELLERUID." and og.goods_id = {$goods_id}  
	                    and o.date_added >= ".$lottery_goods['begin_time']."   order by p.pin_id asc limit {$offset},{$per_page}";

        //begin_time date_added

        $list=M()->query($sql);
        $this->list = $list;
        $this->goods_id = $goods_id;
        $this->lottery_goods = $lottery_goods;

        if($page>1){
            $result = array();
            $result['code'] = 0;
            if(!empty($list)) {
                $content = $this->fetch('Goods:lottery_info_fetch');
                $result['code'] = 1;
                $result['html'] = $content;
            }
            echo json_encode($result);
            die();
        }

        $this->display();
    }

    public function openlottery()
    {
        $goods_id = I('get.id',0);
        $oids = I('post.oids');
        $order_model = D('Home/Order');

        $order_model->open_goods_lottery_order($goods_id,$oids,false);

        //$order_model->open_goods_lottery_order($goods_id,'',true);
        //$map['id'] = array('in','1,3,8')

        echo json_encode( array('code' => 1) );
        die();
    }

    public function lottery_shenqing()
    {
        $result = array('code' => 0);
        $goods_id = I('post.goods_id',0);
        if($goods_id == 0){
            $result['msg'] = '商品不存在';
            echo json_encode($result);
            die();
        }
        $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
        if($goods_info['type'] == 'normal' && !empty($goods_info)) {
            $spike_data = array();
            $spike_data['goods_id'] = $goods_id;
            $spike_data['state'] = 0;
            $spike_data['quantity'] = $goods_info['quantity'];
            $spike_data['begin_time'] = 0;
            $spike_data['end_time'] = 0;
            $spike_data['addtime'] = time();
            $rs = M('lottery_goods')->add($spike_data);
            if($rs) {
                M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'lottery') );
            }
            $result['code'] = 1;
            echo json_encode($result);
            die();
        } else{
            $result['msg'] = '已存在其他活动中';
            echo json_encode($result);
            die();
        }
    }

    public function xianshimiaosha_shenqing()
    {
        $result = array('code' => 0);
        $goods_id = I('post.goods_id',0);
        if($goods_id == 0){
            $result['msg'] = '商品不存在';
            echo json_encode($result);
            die();
        }
        $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
        if($goods_info['type'] == 'normal' && !empty($goods_info)) {
            $spike_data = array();
            $spike_data['goods_id'] = $goods_id;
            $spike_data['state'] = 0;
            $spike_data['quantity'] = $goods_info['quantity'];
            $spike_data['begin_time'] = 0;
            $spike_data['end_time'] = 0;
            $spike_data['addtime'] = time();
            $rs = M('spike_goods')->add($spike_data);
            if($rs) {
                M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'spike') );
            }
            $result['code'] = 1;
            echo json_encode($result);
            die();
        } else{
            $result['msg'] = '已存在其他活动中';
            echo json_encode($result);
            die();
        }

    }

    public function spike_sub()
    {
        $spike_id = I('post.spike',0);
        $goods_id = I('post.goods_id',0);

        if($goods_id == 0){
            $result['msg'] = '商品不存在';
            echo json_encode($result);
            die();
        }

        $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
        $spike_info = M('spike')->where( array('id' => $spike_id) )->find();

        if($goods_info['type'] == 'normal' && !empty($goods_info)) {
            $super_data  = array();
            $super_data['spike_id'] = $spike_id;
            $super_data['goods_id'] = $goods_id;
            $super_data['state'] = 0;
            $super_data['begin_time'] = $spike_info['begin_time'];
            $super_data['end_time'] = $spike_info['end_time'];
            $super_data['addtime'] = time();

            $rs = M('spike_goods')->add($super_data);

            if($rs) {
                M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'spike') );
            }

            $result['code'] = 1;
            echo json_encode($result);
            die();
        } else {
            $result['msg'] = '已存在其他活动中';
            echo json_encode($result);
            die();
        }
    }

    public function chaozhidapai_sub()
    {
        $super_spike_id = I('post.super_spike',0);
        $goods_id = I('post.goods_id',0);

        if($goods_id == 0){
            $result['msg'] = '商品不存在';
            echo json_encode($result);
            die();
        }

        $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();

        if($goods_info['type'] == 'normal' && !empty($goods_info)) {
            $super_data  = array();
            $super_data['super_spike_id'] = $super_spike_id;
            $super_data['goods_id'] = $goods_id;
            $super_data['state'] = 0;
            $super_data['begin_time'] = 0;
            $super_data['end_time'] = 0;
            $super_data['addtime'] = time();

            $rs = M('super_spike_goods')->add($super_data);

            if($rs) {
                M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'super_spike') );
            }

            $result['code'] = 1;
            echo json_encode($result);
            die();
        } else {
            $result['msg'] = '已存在其他活动中';
            echo json_encode($result);
            die();
        }
    }
    public function oneyuansubject_sub()
    {
        $subject_id = I('post.subject',0);
        $goods_id = I('post.goods_id',0);

        if($goods_id == 0){
            $result['msg'] = '商品不存在';
            echo json_encode($result);
            die();
        }

        $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();

        if($goods_info['type'] == 'normal' && !empty($goods_info)) {

            $super_data  = array();
            $super_data['subject_id'] = $subject_id;
            $super_data['goods_id'] = $goods_id;
            $super_data['state'] = 0;

            $super_data['addtime'] = time();

            $rs = M('subject_goods')->add($super_data);

            if($rs) {
                M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'oneyuan') );
            }

            $result['code'] = 1;
            echo json_encode($result);
            die();
        } else {
            $result['msg'] = '已存在其他活动中';
            echo json_encode($result);
            die();
        }
    }
    public function niyuansubject_sub()
    {
        $subject_id = I('post.subject',0);
        $goods_id = I('post.goods_id',0);

        if($goods_id == 0){
            $result['msg'] = '商品不存在';
            echo json_encode($result);
            die();
        }

        $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();

        if($goods_info['type'] == 'normal' && !empty($goods_info)) {

            $super_data  = array();
            $super_data['subject_id'] = $subject_id;
            $super_data['goods_id'] = $goods_id;
            $super_data['state'] = 0;

            $super_data['addtime'] = time();

            $rs = M('subject_goods')->add($super_data);

            if($rs) {
                M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'niyuan') );
            }

            $result['code'] = 1;
            echo json_encode($result);
            die();
        } else {
            $result['msg'] = '已存在其他活动中';
            echo json_encode($result);
            die();
        }
    }
    public function mianfei_sub()
    {
        $subject_id = I('post.subject',0);
        $goods_id = I('post.goods_id',0);

        if($goods_id == 0){
            $result['msg'] = '商品不存在';
            echo json_encode($result);
            die();
        }

        $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();

        if($goods_info['type'] == 'normal' && !empty($goods_info)) {

            $super_data  = array();
            $super_data['subject_id'] = $subject_id;
            $super_data['goods_id'] = $goods_id;
            $super_data['state'] = 0;

            $super_data['addtime'] = time();

            $rs = M('subject_goods')->add($super_data);

            if($rs) {
                M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'zeyuan') );
            }

            $result['code'] = 1;
            echo json_encode($result);
            die();
        } else {
            $result['msg'] = '已存在其他活动中';
            echo json_encode($result);
            die();
        }
    }
    public function putongsubject_sub()
    {
        $subject_id = I('post.subject',0);
        $goods_id = I('post.goods_id',0);

        if($goods_id == 0){
            $result['msg'] = '商品不存在';
            echo json_encode($result);
            die();
        }

        $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();

        if($goods_info['type'] == 'normal' && !empty($goods_info)) {

            $super_data  = array();
            $super_data['subject_id'] = $subject_id;
            $super_data['goods_id'] = $goods_id;
            $super_data['state'] = 0;
            $super_data['addtime'] = time();

            $rs = M('subject_goods')->add($super_data);

            if($rs) {
                M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'subject') );
            }

            $result['code'] = 1;
            echo json_encode($result);
            die();
        } else {
            $result['msg'] = '已存在其他活动中';
            echo json_encode($result);
            die();
        }
    }

    public function haitaosubject_sub()
    {
        $subject_id = I('post.subject',0);
        $goods_id = I('post.goods_id',0);

        if($goods_id == 0){
            $result['msg'] = '商品不存在';
            echo json_encode($result);
            die();
        }

        $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();

        if($goods_info['type'] == 'normal' && !empty($goods_info)) {

            $super_data  = array();
            $super_data['subject_id'] = $subject_id;
            $super_data['goods_id'] = $goods_id;
            $super_data['state'] = 0;
            $super_data['addtime'] = time();

            $rs = M('subject_goods')->add($super_data);

            if($rs) {
                M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'haitao') );
            }

            $result['code'] = 1;
            echo json_encode($result);
            die();
        } else {
            $result['msg'] = '已存在其他活动中';
            echo json_encode($result);
            die();
        }
    }
    public function yiyuan_form()
    {
        $result = array('code' => 0);
        $goods_id = I('post.goods_id',0);
        if($goods_id == 0){
            $result['msg'] = '商品不存在';
            echo json_encode($result);
            die();
        }

        $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
        if($goods_info['type'] == 'normal' && !empty($goods_info)) {

            $subject = M('subject')->where('can_shenqing=1 and type="oneyuan"')->select();
            $this->subject = $subject;
            $this->goods_id = $goods_id;

            $content = $this->fetch('Goods:goods_oneyuansubject_fetch');
            $result['code'] = 1;
            $result['html'] = $content;
            echo json_encode($result);
            die();
        } else {
            $result['msg'] = '已存在其他活动中';
            echo json_encode($result);
            die();
        }
    }
    public function jiukuaijiu_form()
    {
        $result = array('code' => 0);
        $goods_id = I('post.goods_id',0);
        if($goods_id == 0){
            $result['msg'] = '商品不存在';
            echo json_encode($result);
            die();
        }

        $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
        if($goods_info['type'] == 'normal' && !empty($goods_info)) {

            $subject = M('subject')->where('can_shenqing=1 and type="niyuan"')->select();
            $this->subject = $subject;
            $this->goods_id = $goods_id;

            $content = $this->fetch('Goods:goods_niyuansubject_fetch');
            $result['code'] = 1;
            $result['html'] = $content;
            echo json_encode($result);
            die();
        } else {
            $result['msg'] = '已存在其他活动中';
            echo json_encode($result);
            die();
        }
    }

    public function lottery_form()
    {
        $result = array('code' => 0);
        $goods_id = I('post.goods_id',0);
        if($goods_id == 0){
            $result['msg'] = '商品不存在';
            echo json_encode($result);
            die();
        }

        $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
        if($goods_info['type'] == 'normal' && !empty($goods_info)) {

            $voucher_list = M('voucher')->where( "store_id=".SELLERUID." and begin_time>".time() )->select();
            $this->voucher_list = $voucher_list;
            $this->goods_id = $goods_id;

            $content = $this->fetch('Goods:goods_lottery_fetch');
            $result['code'] = 1;
            $result['html'] = $content;
            echo json_encode($result);
            die();
        } else {
            $result['msg'] = '已存在其他活动中';
            echo json_encode($result);
            die();
        }
    }

    public function lottery_sub()
    {
        $voucher_id = I('post.voucher_id',0);
        $goods_id = I('post.goods_id',0);
        $win_quantity = I('post.win_quantity',0);
        $is_auto_open = I('post.is_auto_open',0);
        $real_win_quantity = I('post.real_win_quantity',0);

        if($goods_id == 0){
            $result['msg'] = '商品不存在';
            echo json_encode($result);
            die();
        }

        if($voucher_id == 0){
            $result['msg'] = '请选择退款时赠送的优惠券';
            echo json_encode($result);
            die();
        }

        $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();

        if($goods_info['type'] == 'normal' && !empty($goods_info)) {

            $spike_data = array();
            $spike_data['goods_id'] = $goods_id;
            $spike_data['state'] = 0;
            $spike_data['is_open_lottery'] = 0;
            $spike_data['voucher_id'] = $voucher_id;
            $spike_data['win_quantity'] = $win_quantity;
            $spike_data['is_auto_open'] = $is_auto_open;
            $spike_data['real_win_quantity'] = $real_win_quantity;
            $spike_data['quantity'] = $goods_info['quantity'];
            $spike_data['begin_time'] = 0;
            $spike_data['end_time'] = 0;
            $spike_data['addtime'] = time();
            $rs = M('lottery_goods')->add($spike_data);
            if($rs) {
                M('goods')->where( array('goods_id' => $goods_id) )->save( array('lock_type' =>'lottery') );
            }
            $result['code'] = 1;
            echo json_encode($result);
            die();
            die();
        } else {
            $result['msg'] = '已存在其他活动中';
            echo json_encode($result);
            die();
        }

    }
    public function putongsubject_form()
    {
        $result = array('code' => 0);
        $goods_id = I('post.goods_id',0);
        if($goods_id == 0){
            $result['msg'] = '商品不存在';
            echo json_encode($result);
            die();
        }

        $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
        if($goods_info['type'] == 'normal' && !empty($goods_info)) {

            $subject = M('subject')->where('can_shenqing=1 and type="normal"')->select();
            $this->subject = $subject;
            $this->goods_id = $goods_id;

            $content = $this->fetch('Goods:goods_putongsubject_fetch');
            $result['code'] = 1;
            $result['html'] = $content;
            echo json_encode($result);
            die();
        } else {
            $result['msg'] = '已存在其他活动中';
            echo json_encode($result);
            die();
        }
    }
    public function haitaosubject_form()
    {
        $result = array('code' => 0);
        $goods_id = I('post.goods_id',0);
        if($goods_id == 0){
            $result['msg'] = '商品不存在';
            echo json_encode($result);
            die();
        }

        $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
        if($goods_info['type'] == 'normal' && !empty($goods_info)) {

            $subject = M('subject')->where('can_shenqing=1 and type="haitao"')->select();
            $this->subject = $subject;
            $this->goods_id = $goods_id;

            $content = $this->fetch('Goods:goods_haitaosubject_fetch');
            $result['code'] = 1;
            $result['html'] = $content;
            echo json_encode($result);
            die();
        } else {
            $result['msg'] = '已存在其他活动中';
            echo json_encode($result);
            die();
        }
    }
    public function mianfeishiyong_form()
    {
        $result = array('code' => 0);
        $goods_id = I('post.goods_id',0);
        if($goods_id == 0){
            $result['msg'] = '商品不存在';
            echo json_encode($result);
            die();
        }

        $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
        if($goods_info['type'] == 'normal' && !empty($goods_info)) {

            $subject = M('subject')->where('can_shenqing=1 and type="zeyuan"')->select();
            $this->subject = $subject;
            $this->goods_id = $goods_id;

            $content = $this->fetch('Goods:goods_mianfeishiyong_fetch');
            $result['code'] = 1;
            $result['html'] = $content;
            echo json_encode($result);
            die();
        } else {
            $result['msg'] = '已存在其他活动中';
            echo json_encode($result);
            die();
        }
    }
    public function chaozhidapai_form()
    {
        $result = array('code' => 0);
        $goods_id = I('post.goods_id',0);
        if($goods_id == 0){
            $result['msg'] = '商品不存在';
            echo json_encode($result);
            die();
        }

        $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
        if($goods_info['type'] == 'normal' && !empty($goods_info)) {
            $super_spike_list = M('super_spike')->where('begin_time>'.time())->select();
            $this->super_spike_list = $super_spike_list;
            $this->goods_id = $goods_id;

            $content = $this->fetch('Goods:goods_chaozhidapai_fetch');
            $result['code'] = 1;
            $result['html'] = $content;
            echo json_encode($result);
            die();
        } else {
            $result['msg'] = '已存在其他活动中';
            echo json_encode($result);
            die();
        }

    }
    public function spike_form()
    {
        $result = array('code' => 0);
        $goods_id = I('post.goods_id',0);
        if($goods_id == 0){
            $result['msg'] = '商品不存在';
            echo json_encode($result);
            die();
        }

        $goods_info = M('goods')->where( array('goods_id' => $goods_id) )->find();
        if($goods_info['type'] == 'normal' && !empty($goods_info)) {
            $spike_list = M('spike')->where()->select();
            //$spike_list = M('spike')->where('begin_time>'.time())->select();

            $this->spike_list = $spike_list;
            $this->goods_id = $goods_id;

            $content = $this->fetch('Goods:goods_spike_fetch');
            $result['code'] = 1;
            $result['html'] = $content;
            echo json_encode($result);
            die();
        } else {
            $result['msg'] = '已存在其他活动中';
            echo json_encode($result);
            die();
        }

    }

    public function get_json_category_tree($pid,$is_ajax=0)
    {
        // {pid:pid,is_ajax:1}
        $pid = empty($_GET['pid']) ? 0: intval($_GET['pid']);
        $is_ajax = empty($_GET['is_ajax']) ? 0:intval($_GET['is_ajax']);
        $goods_cate_model = D('Seller/GoodsCategory');
        //$list = $goods_cate_model->get_parent_cateory($pid,SELLERUID);

        $list = M('goods_category')->field('id,pid,name')->where( array('pid'=>$pid) )->order('sort_order asc')->select();

        if($pid > 0)
        {
            $list = M('goods_category')->field('id,pid,name')->where( array('pid'=>$pid) )->order('sort_order asc')->select();
        }


        $result = array();
        if($is_ajax ==0)
        {
            return $list;
        } else {
            if(empty($list)){
                $result['code'] = 0;
            } else {
                $result['code'] = 1;
                $result['list'] = $list;
            }
            echo json_encode($result);
            die();
        }

    }
    function add(){


        $model=new GoodsModel();
        if(IS_POST){

            $data=I('post.');
            $data['goods_description']['tag'] = str_replace('，', ',', $data['goods_description']['tag']);

            $data['store_id']=SELLERUID;

            if($this->goods_is_shenhe()) {
                $data['status'] = 2;
            }

            $return=$model->add_goods($data);
            $this->osc_alert($return);
        }


        $m=new \Admin\Model\OptionModel();
        //getOptions
        $options_list = $m->getOptions('',SELLERUID);

        $this->options_list = $options_list;
        $pick_list =  M('pick_up')->where( array('store_id' => SELLERUID) )->select();

        $this->pick_list = $pick_list;

        $member_model= D('Admin/Member');
        $level_list = $member_model->show_member_level();

        $member_default_levelname_info = D('Home/Front')->get_config_by_name('member_default_levelname');

        $member_defualt_discount_info = D('Home/Front')->get_config_by_name('member_defualt_discount');

        $default = array('id'=>'default', 'level' => 0,'levelname' => $member_default_levelname_info,'discount' => $member_defualt_discount_info);

        array_unshift($level_list['list'], $default );

        $need_level_list = $level_list['list'];

        $set = D('Seller/Config')->get_all_config();
        $this->set = $set;

        /***
        是否开启 分享等级佣金 begin
         **/

        $index_sort_method = D('Home/Front')->get_config_by_name('index_sort_method');

        if( empty($index_sort_method) || $index_sort_method == 0 )
        {
            $index_sort_method = 0;
        }
        $this->index_sort_method = $index_sort_method;

        $show_fissionsharing_level = 1;

        $is_open_sharing = D('Home/Front')->get_config_by_name('is_open_fissionsharing');
        $show_fissionsharing_level =  D('Home/Front')->get_config_by_name('show_fissionsharing_level');

        $this->show_fissionsharing_level = $show_fissionsharing_level;
        $this->is_open_sharing = $is_open_sharing;
        /***
        是否开启 分享等级佣金 end
         **/

        $this->member_level_is_open_info = D('Home/Front')->get_config_by_name('member_level_is_open');
        $this->need_level_list = $need_level_list;

        $this->cate_data = $this->get_json_category_tree(0);
        $this->action=U('Goods/add');
        $this->crumbs='新增';
        $this->display('edit');
    }

    /**
    商品是否需要审核
     **/
    function goods_is_shenhe()
    {
        $shenhegoods = M('config')->where( array('name' => 'shenhegoods') )->find();

        $is_need_shen = 0;

        if(!empty($shenhegoods)) {
            $is_need_shen = $shenhegoods['value'];
        }
        return $is_need_shen;
    }

    public function change()
    {

        $id = I('request.id',0);



        //ids
        if (empty($id)) {
            $ids = I('request.ids');

            $id = ((is_array($ids) ? implode(',', $ids) : 0));
        }



        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }


        $type = I('request.type');
        $value = I('request.value');

        //type/grounding/

        $is_can_do =  D('Seller/Supply')->checksupply_pri( $type );

        if( !$is_can_do )
        {
            show_json(0, array('message' => '无此操作权限','url' => $_SERVER['HTTP_REFERER'] ));
        }
        M('lionfish_comshop_coin')->where( array('id' => $id) )->delete();
        if (!(in_array($type, array('goodsname', 'price','index_sort','is_index_show', 'total','grounding', 'goodssn', 'productsn', 'displayorder')))) {
            show_json(0, array('message' => '参数错误'));
        }



        show_json(1, array('url' => $_SERVER['HTTP_REFERER']));
    }

    public function delete()
    {

        $id = I('get.id');

        //ids
        if (empty($id)) {
            $ids = I('post.ids');

            $id = ((is_array($ids) ? implode(',', $ids) : 0));
        }

        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }


        $items = M('lionfish_comshop_coin')->field('id,name')->where( array('id' => array('in', $id)) )->select();

        foreach ($items as $item ) {
            //pdo_update('lionfish_comshop_goods', array($type => $value), array('id' => $item['id'])); //ims_lionfish_comshop_goods

            M('lionfish_comshop_coin')->where( array('id' => $item['id']) )->delete();
        }
        sellerLog('删除了['.$items['goodsname'].']商品', 3);

        show_json(1);
    }

    function edit(){


        $id =  I('get.id');

        if (IS_POST) {
            $_GPC = I('post.');
            $http_refer = S('HTTP_REFERER');

            $http_refer = empty($http_refer) ? $_SERVER['HTTP_REFERER'] : $http_refer;
            $_GPC = I('post.');
            if( !isset($_GPC['name']) || empty($_GPC['name']) )
            {
                show_json(0,  array('message' => '兑换券名称不能为空' ,'url' => $http_refer) );
                die();
            }
            if( !isset($_GPC['gid']) || empty($_GPC['gid']) )
            {
                show_json(0,  array('message' => '套餐商品必须选择' ,'url' => $http_refer) );
                die();
            }
            if( !isset($_GPC['card_id']) || empty($_GPC['card_id']) )
            {
                show_json(0,  array('message' => '卡券名称必须选择' ,'url' => $http_refer) );
                die();
            }
            
            $data = [
                'name'=>$_GPC['name'],
                'gid'=>$_GPC['gid'],
                'card_id'=>$_GPC['card_id'],
                'num'=>$_GPC['num'],
                'status'=>$_GPC['status'],
                'remake'=>$_GPC['remake'],
                'coinunit'=>empty($_GPC['coinunit']) ? '张' : $_GPC['coinunit']
            ];
            $goods = M('lionfish_comshop_goods')->field('id,goodsname')->where(array('id'=>$_GPC['gid']))->find();
            $goodsImg = M('lionfish_comshop_goods_images')->where(array('goods_id'=>$_GPC['gid']))->find();
            $data['goods_img'] = $goodsImg['image'];
            $data['goods_name'] = $goods['goodsname'];
            $list = M('lionfish_comshop_coin')->where('id = "'.I('get.id').'"')->save($data);
            //D('Seller/Goods')->modify_goods();

            

            show_json(1, array('message'=>'修改商品成功！','url' => $http_refer ));
        }
        //sss
        S('HTTP_REFERER', $_SERVER['HTTP_REFERER']);
        $this->id = $id;
        $item = M('lionfish_comshop_coin')->where('id = "'.I('get.id').'"')->find();

        $card = M('lionfish_comshop_package_goods')->field('id,goodsname')->select();
        $goods = M('lionfish_comshop_goods')->field('id,goodsname')->where('is_package = 1')->select();
        $this->card = $card;
        $this->goods = $goods;
        $this->item = $item;
        $this->display('Coin/addgoods');
    }

    public function labelfile()
    {
        $_GPC = I('request.');
        $id = intval($_GPC['id']);

        if (empty($id)) {
            show_json(0, array() );
            die();
        }

        $condition = '  id = '.$id.' and state = 1 ';

        $labels = M('lionfish_comshop_goods_tags')->field('id,tagname,type,tagcontent')->where($condition)->find();

        if (empty($labels)) {
            $labels = array();
            show_json(0, array('msg' => '您查找的标签不存在或已删除！') );
            die();
        }

        show_json(1, array('label' => $labels['tagname'], 'id' => $labels['id']));
    }

    public function goodstag()
    {
        $_GPC = I('request.');

        $this->gpc = $_GPC;

        $condition = ' 1 and tag_type="normal" ';
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;

        if ($_GPC['enabled'] != '') {
            $condition .= ' and state=' . intval($_GPC['enabled']);
        }

        if (!empty($_GPC['keyword'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' and tagname like "%'.$_GPC['keyword'].'%" ';
        }

        $label = M('lionfish_comshop_goods_tags')->where( $condition )->order(' id asc ')->limit( (($pindex - 1) * $psize) . ',' . $psize )->select();

        $total = M('lionfish_comshop_goods_tags')->where( $condition )->count();

        $pager = pagination2($total, $pindex, $psize);

        $this->label = $label;
        $this->pager = $pager;

        $this->display();
    }

    function copy_goods(){
        $id =I('id');
        $model=new GoodsModel();
        if($id){
            foreach ($id as $k => $v) {
                $model->copy_goods($v);
            }
            $data['redirect']=U('Goods/index');
            $this->ajaxReturn($data);
            die;
        }
    }

    function del(){
        $model=new GoodsModel();
        $return=$model->del_goods(I('get.id'));
        $this->osc_alert($return);
    }

    /**
     * 置顶
     * @return [json] 0 失败 1 成功
     */
    public function settop()
    {

        $id =  I('request.id');

        //ids
        if (empty($id)) {
            $ids = I('request.ids');

            $id = ((is_array($ids) ? implode(',', $ids) : 0));
        }

        if (empty($id)) {
            show_json(0, array('message' => '参数错误'));
        }

        $type = I('request.type');
        $value = I('request.value');

        if ($type != 'istop') {
            show_json(0, array('message' => '参数错误'));
        }

        $items = M('lionfish_comshop_goods')->field('id')->where( 'id in( ' . $id . ' )' )->select();

        foreach ($items as $item ) {
            $settoptime = $value ? time() : '';

            M('lionfish_comshop_goods')->where( array('id' => $item['id'])  )->save( array($type => $value, 'settoptime' => $settoptime) );
        }


        show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
    }

    public function industrial()
    {
        $_GPC = I('request.');

        if ( IS_POST ) {
            $data = ((is_array($_GPC['parameter']) ? $_GPC['parameter'] : array()));
            $data['goods_industrial'] = serialize($data['goods_industrial']);


            D('Seller/Config')->update($data);

            show_json(1, array('url' => $_SERVER['HTTP_REFERER'] ));
        }

        $data = D('Seller/Config')->get_all_config();
        $data['goods_industrial'] = unserialize($data['goods_industrial']);
        $piclist = array();
        if( !empty($data['goods_industrial']) )
        {
            foreach($data['goods_industrial'] as $val)
            {
                $piclist[] = array('image' =>$val, 'thumb' => tomedia($val) ); //$val['image'];
            }
        }

        $this->piclist = $piclist;
        $this->data = $data;
        $this->display();
    }

    /**
     * excel商品导入编辑
     * @author liu 2020-03-03
     * */
    public function excel_goodslist_edit()
    {
        $columns = array(
            array(
                'title' => '商品ID(禁止修改)',
                'field' => 'id',
                'width' => 24
            ) ,
            array(
                'title' => '商品名称',
                'field' => 'goodsname',
                'width' => 24
            ) ,
            array(
                'title' => '一级分类ID',
                'field' => 'cate1_id',
                'width' => 24
            ) ,
            array(
                'title' => '一级分类名称',
                'field' => 'cate1_name',
                'width' => 24
            ) ,
            array(
                'title' => '二级分类ID',
                'field' => 'cate2_id',
                'width' => 24
            ) ,
            array(
                'title' => '二级分类名称',
                'field' => 'cate2_name',
                'width' => 24
            ) ,

            array(
                'title' => '商品价格',
                'field' => 'price',
                'width' => 24
            ) ,
            array(
                'title' => '商品成本价',
                'field' => 'costprice',
                'width' => 24
            ) ,
            array(
                'title' => '会员卡价格',
                'field' => 'card_price',
                'width' => 24
            ) ,
            array(
                'title' => '商品原价',
                'field' => 'productprice',
                'width' => 24
            ) ,
            array(
                'title' => '商品库存',
                'field' => 'total',
                'width' => 24
            ) ,
            array(
                'title' => '1上架/0下架',
                'field' => 'grounding',
                'width' => 24
            ) ,
            array(
                'title' => '首页推荐(0:取消/1:是)',
                'field' => 'is_index_show',
                'width' => 24
            ) ,
            array(
                'title' => '限时秒杀(0:取消/1:是)',
                'field' => 'is_spike_buy',
                'width' => 24
            ) ,
            array(
                'title' => '所有团长',
                'field' => 'is_all_sale_str',
                'width' => 24
            ) ,
            array(
                'title' => '新人专享',
                'field' => 'is_new_buy',
                'width' => 24
            ) ,

            array(
                'title' => '商品排序(数字)',
                'field' => 'index_sort',
                'width' => 24
            ) ,
            array(
                'title' => '每天限购',
                'field' => 'oneday_limit_count',
                'width' => 24
            ) ,
            array(
                'title' => '单次限购',
                'field' => 'one_limit_count',
                'width' => 24
            ) ,
            array(
                'title' => '历史限购',
                'field' => 'total_limit_count',
                'width' => 24
            ) ,
            array(
                'title' => '开始时间',
                'field' => 'pin_begin_time',
                'width' => 24
            ) ,
            array(
                'title' => '结束时间',
                'field' => 'pin_end_time',
                'width' => 24
            ) ,
            array(
                'title' => '商品重量(单位:g)',
                'field' => 'weight',
                'width' => 24
            ) ,
            array(
                'title' => '规格(1:开启/0:关闭)',
                'field' => 'hasoption',
                'width' => 24
            ) ,
            array(
                'title' => '规格id(禁止修改)',
                'field' => 'option_id',
                'width' => 24
            ) ,
            array(
                'title' => '规格名称(禁止修改)',
                'field' => 'option_title',
                'width' => 24
            ) ,
            array(
                'title' => '规格库存',
                'field' => 'option_stock',
                'width' => 24
            ) ,
            array(
                'title' => '规格现价',
                'field' => 'option_marketprice',
                'width' => 24
            ) ,
            array(
                'title' => '规格原价',
                'field' => 'option_productprice',
                'width' => 24
            ) ,
            array(
                'title' => '规格会员价',
                'field' => 'option_card_price',
                'width' => 24
            ) ,
            array(
                'title' => '规格成本价',
                'field' => 'option_costprice',
                'width' => 24
            ) ,
            array(
                'title' => '规格编码',
                'field' => 'option_goodssn',
                'width' => 24
            ) ,
            array(
                'title' => '规格重量(单位:g)',
                'field' => 'option_weight',
                'width' => 24
            )
        );
        sellerLog('导入商品excel编辑', 3);
        $rows = D('Seller/Excel')->import('excel');
        $row_count = count($rows);
        $field_arr = [];
        if($row_count <= 1){
            $this->error('失败','goods/index');

            die;
        }
        foreach($rows[0] as $key => $value) {
            foreach($columns as $k => $val) {
                if($val['title'] == $value){
                    $field_arr[$key]= $val['field'];
                }
            }
        }
        unset($rows[0]);
        foreach($rows as $key => $value){
            $this->excelGoodsUpdate($value, $field_arr);
        }
        $this->success('成功','goods/index');

    }
    public function excelGoodsUpdate($data, $field_arr)
    {
        $optionId = 0;
        $goodsId  = 0;
        $goodsData = [];
        $optionData = [];
        foreach($data as $key => $value){
            $field = $field_arr[$key];

            if(strstr($field, 'option_') > -1 && $value){// 规格
                if($field == 'option_id'){// 规格id
                    $optionId = $value;
                    $goodsData = [];
                }
                $optionData[str_replace('option_','',$field)] = $value;
                $goodsId  = 0;
            }else{ // 普通商品数据
                if(strstr($field, 'option_') > -1 ) {// 规格
                    continue;
                }

                if($field == 'id'){// 规格id
                    $goodsId = $value;
                    $optionData = [];
                }
                $optionId  = 0;
                $goodsData[$field] = $value;

            }
        }
        if($optionId > 0 && count($optionData) > 0){
            unset($optionData['title']);
            unset($optionData['id']);
            M('lionfish_comshop_goods_option_item_value')->where('id = "'.$optionId.'"')->save($optionData);

            unset($optionData);
        }else if($goodsId > 0 && count($goodsData) > 0){
            unset($goodsData['is_all_sale_str']);
            unset($goodsData['cate1_id']);
            unset($goodsData['cate1_name']);
            unset($goodsData['cate2_id']);
            unset($goodsData['cate2_name']);
            unset($goodsData['id']);

            $goodsCommon = M('lionfish_comshop_good_common')->where('goods_id = "'.$goodsId.'"')->field('id')->find();
            if($goodsCommon['id']){
                M('lionfish_comshop_good_common')
                    ->where('id = "'.$goodsCommon['id'].'"')
                    ->save([
                        'one_limit_count' => $goodsData['one_limit_count'],
                        'total_limit_count' => $goodsData['total_limit_count'],
                        'is_spike_buy' => $goodsData['is_spike_buy'],
                        'is_new_buy' => $goodsData['is_new_buy'],
                    ]);
                unset($goodsData['one_limit_count']);
                unset($goodsData['total_limit_count']);
                unset($goodsData['is_spike_buy']);
                unset($goodsData['is_new_buy']);
            }

            $goodsPin = M('lionfish_comshop_good_pin')->where('goods_id = "'.$goodsId.'"')->field('id')->find();
            if($goodsPin['id']){
                M('lionfish_comshop_good_pin')
                    ->where('id = "'.$goodsPin['id'].'"')
                    ->save([
                        'begin_time' => $goodsData['begin_time'],
                        'end_time' => $goodsData['end_time'],
                    ]);
                unset($goodsData['begin_time']);
                unset($goodsData['end_time']);
            }
            M('lionfish_comshop_goods')
                ->where('id = "'.$goodsId.'"')
                ->save($goodsData);
            unset($goodsData);
        }
    }


}
?>