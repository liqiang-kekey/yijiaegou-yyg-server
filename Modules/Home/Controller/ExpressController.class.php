<?php

namespace Home\Controller;

use Common\library\Helper;

/**
 * 快递配送服务类
 * Class Delivery
 * @package app\common\service
 */
class ExpressController  extends CommonController
{
    private $cityId;     // 用户收货城市id
    private $goodsList;  // 订单商品列表
    private $orderType;  // 订单类型 (主商城、拼团)
    private $notInRuleGoodsId;  // 不在配送范围的商品ID

    // 运费模板数据集
    private $data = [];

    /**
     * 构造方法
     * Express constructor.
     * @param $cityId
     * @param $goodsList
     * @param int $orderType
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function __construct($cityId, $goodsList, $orderType = 'dan')
    {
        // 赋值传参
        $this->cityId = $cityId;
        $this->goodsList = $goodsList;
        $this->orderType = $orderType;
        // 整合运费模板
        $this->initDeliveryTemplate();
    }

    /**
     * 验证用户收货地址是否在配送范围
     * @return bool
     */
    public function isIntraRegion()
    {
        if (!$this->cityId) return false;
        $full_free_areas = D('Home/Front')->get_config_by_name('full_free_areas');
        if(!empty($full_free_areas)) {
            $areas = unserialize($full_free_areas);
            $notin_region = explode(';', $areas['citys_code']);
        }
        $pid = M('lionfish_comshop_area')->where(array('code' => $this->cityId ))->find();
        if($pid){
            $cityid = M('lionfish_comshop_area')->where(array('id' => $pid['pid'] ))->find();
            $cityIds = $cityid['code'];
        }else{
            $cityIds = $this->cityId;
        }
        foreach ($this->data as $item) {
//            $area = unserialize($item['delivery']['areas']);
//            foreach ($area as $ruleItem) {
//                $cityIds = array_merge($cityIds, explode(';',$ruleItem['citys_code']));
//            }
            if (in_array($cityIds, $notin_region)) {
                $this->notInRuleGoodsId = current($item['goodsList'])['goods_id'];
                return false;
            }
        }
        return true;
    }

    /**
     * 获取不在配送范围的商品名称
     * @return null
     */
    public function getNotInRuleGoodsName()
    {
        $item = helper::getArrayItemByColumn($this->goodsList, 'goods_id', $this->notInRuleGoodsId);
        return !empty($item) ? $item['name'] : null;
    }

    /**
     * 获取订单的配送费用
     * @return float|string
     */
    public function getDeliveryFee()
    {
        if (empty($this->cityId) || empty($this->goodsList) || $this->notInRuleGoodsId > 0) {
            return helper::number2(0.00);
        }
        // 处理商品包邮
        $this->freeshipping();
        // 计算配送金额
        foreach ($this->data as &$item) {
            // 计算当前配送模板的运费
            $item['delivery_fee'] = $this->calcDeliveryAmount($item);
        }
        // 根据运费组合策略获取最终运费金额
        return helper::number2($this->getFinalFreight());
    }

    /**
     * 根据运费组合策略 计算最终运费
     * @return double
     */
    private function getFinalFreight()
    {
        // 运费合集
        $expressPriceArr = helper::getArrayColumn($this->data, 'delivery_fee');
        // 最终运费金额
        $expressPrice = 0.00;
        // 判断运费组合策略
        $freight_rule = D('Home/Front')->get_config_by_name('freight_rule');
        switch ($freight_rule) {
            case '10':    // 策略1: 叠加
                $expressPrice = array_sum($expressPriceArr);
                break;
            case '20':    // 策略2: 以最低运费结算
                $expressPrice = min($expressPriceArr);
                break;
            case '30':    // 策略3: 以最高运费结算
                $expressPrice = max($expressPriceArr);
                break;
        }

        return $expressPrice;
    }

    /**
     * 处理商品包邮
     * @return bool
     */
    private function freeshipping()
    {
        // 订单商品总金额
        $orderTotalPrice = helper::getArrayColumnSum($this->goodsList, 'price');
        // 获取满额包邮设置
        $delivery_type_express = D('Home/Front')->get_config_by_name('delivery_type_express');

        $man_free_shipping = D('Home/Front')->get_config_by_name('man_free_shipping');//满50包邮
        $full_free_limit_goods_list = D('Home/Front')->get_config_by_name('full_free_limit_goods_list');//不包邮的商品
        foreach ($this->data as &$item) {
            $item['free_goods_list'] = [];
            foreach ($item['goodsList'] as $goodsItem) {
                if (
                    $this->orderType === 'dan'
                    && $delivery_type_express == 1
                    && $orderTotalPrice >= $man_free_shipping
                    && !in_array($goodsItem['goods_id'], explode(',',$full_free_limit_goods_list))
                ) {
                    $item['free_goods_list'][] = $goodsItem['goods_id'];
                }
            }
        }
        return true;
    }

    /**
     * 计算当前配送模板的运费
     * @param $item
     * @return float|mixed|string
     */
    private function calcDeliveryAmount($item)
    {
        // 获取运费模板下商品总数量or总重量
        if (!$totality = $this->getItemGoodsTotal($item)) {
            return 0.00;
        }
        // 当前收货城市配送规则
        $deliveryRule = $this->getCityDeliveryRule($item['delivery']);

        if($deliveryRule){
            $first = $deliveryRule['frist'];
            $first_price = $deliveryRule['frist_price'];
            $second = $deliveryRule['second'];
            $second_price = $deliveryRule['second_price'];
        }else{
            if($item['delivery']['type'] == 1){
                $first = $item['delivery']['firstweight'];
                $first_price = $item['delivery']['firstprice'];
                $second = $item['delivery']['secondweight'];
                $second_price = $item['delivery']['secondprice'];
            }else{
                $first = $item['delivery']['firstnum'];
                $first_price = $item['delivery']['firstnumprice'];
                $second = $item['delivery']['secondnum'];
                $second_price = $item['delivery']['secondnumprice'];
            }

        }

        if ($totality <= $first) {
            return $first_price;
        }
        // 续件or续重 数量
        $additional = $totality - $first;//1000

        if ($additional <= $second) {
            return helper::bcadd($first_price, $second_price);
        }
        // 计算续重/件金额
        if ($second < 1) {
            // 配送规则中续件为0
            $additionalFee = 0.00;
        } else {
            $additionalFee = helper::bcdiv($second_price, $second) * $additional;
        }
        return helper::bcadd($first_price, $additionalFee);
    }

    /**
     * 获取运费模板下商品总数量or总重量
     * @param $item
     * @return int|string
     */
    private function getItemGoodsTotal($item)
    {
        $totalWeight = 0;   // 总重量
        $totalNum = 0;      // 总数量

        foreach ($item['goodsList'] as $goodsItem) {
            // 如果商品为包邮，则不计算总量中
            if (!in_array($goodsItem['goods_id'], $item['free_goods_list'])) {
                $goodsWeight = helper::bcmul($goodsItem['weight'], $goodsItem['quantity']);
                $totalWeight = helper::bcadd($totalWeight, $goodsWeight);
                $totalNum = helper::bcadd($totalNum, $goodsItem['quantity']);
            }
        }
        return $item['delivery']['type'] == 2 ? $totalNum : $totalWeight;
    }

    /**
     * 根据城市id获取规则信息
     * @param
     * @return array|false
     */
    private function getCityDeliveryRule($delivery)
    {
        $areas = unserialize($delivery['areas']);
        //$region_data = explode(';',$areas['citys_code']);
        foreach ($areas as $item) {
            if (in_array($this->cityId, explode(';',$item['citys_code']))) {
                return $item;
            }
        }
        return false;
    }

    /**
     * 整合运费模板
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function initDeliveryTemplate()
    {
        // 运费模板ID集
        $deliveryIds = helper::getArrayColumn($this->goodsList, 'delivery_id');
        // 运费模板列表
        $deliveryIds = implode(',',$deliveryIds);
        $deliveryList = M('lionfish_comshop_shipping')->where( ' id in( ' . $deliveryIds . ' ) ' )->select();
        // 整理数据集
        foreach ($deliveryList as $item) {
            $this->data[$item['id']]['delivery'] = $item;
            $this->data[$item['id']]['goodsList'] = $this->getGoodsListByDeliveryId($item['id']);
        }
        return true;
    }

    /**
     * 根据运费模板id整理商品集
     * @param $deliveryId
     * @return array
     */
    private function getGoodsListByDeliveryId($deliveryId)
    {
        $data = [];
        foreach ($this->goodsList as $item) {
            $item['delivery_id'] == $deliveryId && $data[] = $item;
        }
        return $data;
    }


}