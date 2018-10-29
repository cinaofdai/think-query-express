<?php
/**
 * Created by dh2y.
 * Date: 2018/10/15 16:20
 * for: 快递查询
 */

namespace dh2y\query\express;


use Composer\DependencyResolver\Request;
use think\Config;

class QueryExpress
{

    private $config = [
        'type_url' => 'http://www.kuaidi100.com/autonumber/autoComNum?text=',
        'query_url' => 'http://www.kuaidi100.com/query?'
    ];

    private $express = [
        'youzhengguonei' => '邮政快递包裹',
        'ems' => 'EMS',
        'shunfeng' => '顺丰快递',
        'shentong' => '申通快递',
        'yuantong' => '圆通快递',
        'zhongtong' => '中通快递',
        'huitongkuaidi' => '汇通快递',
        'yunda' => '韵达快递',
        'zhaijisong' => '宅急送',
        'tiantian' => '天天快递',
        'debangwuliu' => '德邦快递',
        'guotongkuaidi' => '国通快递',
        'jd' => '京东物流',
        'annengwuliu' => '安能物流',
        'youshuwuliu' => '优速快递',
        'quanfengkuaidi' => '全峰快递',
        'baishiwuliu' => '百世物流'
    ];


    private static $instance = null;  //创建静态单列对象变量

    private $error = '';

    /**
     * QueryExpress constructor.
     * @param array $express
     */
    private function __construct($express = array())
    {
        if (empty($express) && $C = Config::get('express')) {
            $express = $C;
        }
        /* 获取配置 */
        $this->express = array_merge($this->express, $express);
    }

    /**
     * 单列模式
     * @param array $config
     * @return QueryExpress|null
     */
    public static function getInstance($config = array())
    {
        if (empty(self::$instance)) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * 克隆函数私有化，防止外部克隆对象
     * @throws \Exception
     */
    private function __clone()
    {
        throw new \Exception('禁止克隆');
    }

    /**
     * 返还错误信息
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 设置错误信息
     * @param $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    public function __set($name, $value)
    {
        $this->config[$name] = $value;
    }

    public function __get($name)
    {
        return $this->config[$name];
    }


    /**
     * 获取快递公司
     * @param  string $num 快递单号
     * @return array|bool
     */
    public function getType($num)
    {
        $request = $this->type_url . $num;
        $result = CurlRequest::get($request);
        $result = json_decode($result, JSON_OBJECT_AS_ARRAY);

        $return = [];
        if (isset($result['auto'][0])) {
            $return['type'] = $result['auto'][0]['comCode'];
            $return['num'] = $num;
            $return['name'] = isset($this->express[ $return['type']])?$this->express[ $return['type']]:$return['type'];
        }
        return count($return) > 0 ? $return : false;
    }

    /**
     * 获取快递公司代码
     * @param  string $num 快递单号
     * @return string
     */
    public function getComCode($num){
        $request = $this->type_url . $num;
        $result = CurlRequest::get($request);
        $result = json_decode($result, JSON_OBJECT_AS_ARRAY);

        $comCode = '';
        if (isset($result['auto'][0])) {
            $comCode =  $result['auto'][0]['comCode'];
        }
        return $comCode;
    }

    /**
     * 快递详情
     * @param string $num 快递单号
     * @return array|bool   state 0：在途中,1：已发货，2：疑难件，3： 已签收 ，4：已退货。
     */
    public function details($num){
        $type = $this->getComCode($num);
        $request = $this->query_url ."type=$type&postid=$num";
        $result = CurlRequest::get($request);
        $result = json_decode($result, JSON_OBJECT_AS_ARRAY);

        $detail = [];
        if($result['status']==200){
            $detail['data'] =  $result['data'];
            $detail['type'] =  $result['com'];
            $detail['name'] =  isset($this->express[ $result['com']])?$this->express[ $result['com']]:$result['com'];

            $detail['num'] = $result['nu'];

            $detail['state'] =  $result['state'];
            switch ($result['state']){
                case 0: $detail['ret'] = '在途中'; break;
                case 1: $detail['ret'] = '已发货'; break;
                case 2: $detail['ret'] = '疑难件'; break;
                case 3: $detail['ret'] = '已签收'; break;
                case 4: $detail['ret'] = '已退货'; break;
                default: $detail['ret'] = '未知状态'; break;
            }
        }else{
            $this->setError($result['message']);
        }

        return count($detail)>0?$detail:false;
    }


    /**
     * 快递状态
     * @param string $num 快递单号
     * @return array state 0：在途中,1：已发货，2：疑难件，3： 已签收 ，4：已退货。
     */
    public function getState($num){
        $type = $this->getComCode($num);
        $request = $this->query_url ."type=$type&postid=$num";
        $result = CurlRequest::get($request);
        $result = json_decode($result, JSON_OBJECT_AS_ARRAY);

        $status =['state'=>null,'ret'=>''];
        if($result['status']==200){
            switch ($result['state']){
                case 0: $status['ret'] = '在途中'; break;
                case 1: $status['ret'] = '已发货'; break;
                case 2: $status['ret'] = '疑难件'; break;
                case 3: $status['ret'] = '已签收'; break;
                case 4: $status['ret'] = '已退货'; break;
                default: $status['ret'] = '未知状态'; break;
            }
            $status['state'] = $result['state'];
        }

        return $status;
    }


    /**
     * 查询快递单号是否合法
     * @param string $num 快递单
     * @param bool $check_code 是否只检测快递公司即可
     * @return bool
     */
    public function checkNum($num,$check_code=false){
        $type = $this->getComCode($num);

        //如果只检测快递公司并且检测出来就过
        if ($check_code&&$type!=''){
            return true;
        }

        $request = $this->query_url ."type=$type&postid=$num";
        $result = CurlRequest::get($request);
        $result = json_decode($result, JSON_OBJECT_AS_ARRAY);

        if($result['status']==200){
           return true;
        }else{

            //韵达快递检查和发过
            if($type=='yunda'){
                return true;
            }

            $this->setError($result['message']);
            return false;
        }
    }

}