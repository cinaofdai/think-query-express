# think-qrcode
The ThinkPHP5 query-express
快递查询 SDK
## 安装

### 一、执行命令安装
```
composer require dh2y/think-query-express
```

或者

### 二、require安装
```
"require": {
        "dh2y/think-query-express":"*"
},
```

或者
###  三、autoload psr-4标准安装
```
   a) 进入vendor/dh2y目录 (没有dh2y目录 mkdir dh2y)
   b) git clone 
   c) 修改 git clone下来的项目名称为think-qrcode
   d) 添加下面配置
   "autoload": {
        "psr-4": {
            "dh2y\\query\\express\\": "vendor/dh2y/think-query-express/src"
        }
    },
    e) php composer.phar update
```


## 使用
#### 添加配置文件（非必须）
```
 将config/express.php 复制到配置目录里面即可
 
 1、如果获取快递公司是编码，请在配置里面添加编码对应的快递贵公司
 
```

#### 使用方法

   ###### 1-1、获取快递公司信息
   
   ```
    $num = 'XXXXXXXX';
    $Query = QueryExpress::getInstance();
    
    $express = $Query->getType($num);
    
    
   ```
   ###### 1-2、获取快递公司信息返回信息
    
   ```
    array(3) {
      ["type"] => string(8) "shentong"
      ["num"] => int(221401186231)
      ["name"] => string(12) "申通快递"
    }
   ```
    
   ###### 2-1、获取快递信息详情
      
   ```
        $num = 'XXXXXXXX';
        $Query = QueryExpress::getInstance();
        
        $express = $Query->details($num);
        
     
   ```
   ###### 2-2、获取快递信息返回信息
   >state 0：在途中,1：已发货，2：疑难件，3： 已签收 ，4：已退货。
        
   ```
       array(6) {
         ["data"] => array(16) {
           [0] => array(4) {
             ["time"] => string(19) "2018-09-27 07:52:40"
             ["ftime"] => string(19) "2018-09-27 07:52:40"
             ["context"] => string(50) "陕西镇坪县公司-已发往-陕西安康公司"
             ["location"] => string(0) ""
           }
           [1] => array(4) {
             ["time"] => string(19) "2018-09-26 20:19:12"
             ["ftime"] => string(19) "2018-09-26 20:19:12"
             ["context"] => string(79) "陕西镇坪县公司-陕西镇坪县公司(15591577188,0915-8287888)-已收件"
             ["location"] => string(0) ""
           }
         }
         ["type"] => string(8) "shentong"
         ["name"] => string(12) "申通快递"
         ["num"] => string(12) "221401186231"
         ["state"] => string(1) "3"
         ["ret"] => string(9) "已签收"
       }
   ```
 ###### 3-1、获取快递状态
   
   ```
    $num = 'XXXXXXXX';
    $Query = QueryExpress::getInstance();
    
    $express = $Query->getState($num);
    
    
   ```
   ###### 3-2、获取快递状态返回信息
    
   ```
    array(2) {
      ["state"] => string(1) "3"
      ["ret"] => string(9) "已签收"
    }
   ```
    
    

     


