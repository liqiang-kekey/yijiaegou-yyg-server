# 监控直播调用

## 使用方法
1. 引用js
```
<script src="js/getVideo.min.js" type="text/javascript" charset="utf-8"></script>
```
2. 需要调用视频的地方插入占位div标签，id可以自定义
```
<div class="video-box" id="yst-video-box"></div>
```
3. 执行js，通常放在body最后
```
<script type='text/javascript'>
  // @param {string} idName 占位div标签的id名
  // @param {object} options 播放参数对象, 可忽略, 即使用缺省设置
  // @param {object} account 请求监看的账号及设备
  // @example 
  //  $id(idName).config(options).getVideo(account);
  
  $id("yst-video-box").getVideo({
    user: 'demo',
  })
</script>
```
  * 播放参数对象 
```
options = {
    api: '//cdn88.cn/api/', //API接口地址
    multi: false, //是否开启多通道协议
    debug: false, //是否启用本地调试
    logs: false, //是否启用前台日志调试(手机)
    alert: true, //是否弹出消息窗口
    timeout: 600, //连续播放时间限制(秒),0表示不限制
    heartbeat: 300, //心跳连接服务器间隔时间(秒) 
    login: true, //是否允许用户登录,显示登录窗口
    
    // 播放列表
    list: true, //是否显示播放列表
    filter: true, //是否过滤不在线的设备
    colNum: 0, //播放列表每行显示几列,0为自适应根据colWidth计算
    colWidth: 210, //播放列表每列最小宽度
    playinline: 1, //当行内列数小于或等于此值时,原地播放
    
    // 播放器
    player: '', //默认播放器[hls|hls-plugin|flash],空值为自动判断
    controller: true, //是否开启控制按钮(云台控制,分辨率,高/宽比)
    ratio: 0, //视频高/宽比例,不指定值则铺满上级容器
    res: 1, //flash播放器分辨率:默认1=辅码流，0=主码流
    buffer: 2, //flash播放器缓冲时间,默认2秒，网络较差时设置3秒
    flsPort: 1671, //flash播放器默认数据端口1671|7013
    jsPath: 'js/' //项目js文件夹路径
}
```
  * 账号及设备参数
```
account = {
  //服务器ip地址, 可忽略
  ip: '', 
  //无账号时需登录
  user: 'demo', 
  //密码,无密码可忽略
  //password: '', 
  //摄像头SN,传入单个则直接播放,不传或传多个(英文逗号分隔)则可能显示播放列表
  //dev: 'f445ce834bd6117e' 
}
```

## 传参方式
1. 浏览器url传参
```
  http://cdn88.cn/?user=demo&password=&dev=ADS-2CD3T10D-I320151120AACH554444975
```
2. data属性传参
```
  <div id="yst-video-box" class="video-box" data-user="demo"></div>
```
3. js传参
```
  $id(idName).config({
    ...
  }).getVideo({
    ...
  });
```
4. 参数覆盖优先级: url>data>js
