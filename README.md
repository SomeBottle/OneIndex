# OneIndex
Oneindex Bottle Edition.   

(๑•̀ㅂ•́)و✧  Original Program by Donwa.  
<https://github.com/donwa/oneindex>  

## 缘由  
之前听网友介绍了入了one的大门，结果鼓捣oneindex时我的历程很不顺利，一会儿文件列表出不来，一会儿jwt token又过期了...   
于是我修改了一下，**缓解了**部分问题.稍后可能会加入更多功能.  

## 修改内容  
* 自动判断HTTP 429请求过多的错误，并自动限制刷新的时间间隔，自动调整刷新周期.(如果没有到周期会返回提示)↓
  
  ![](https://ww2.sinaimg.cn/large/ed039e1fgy1g1dncyfprgj20iw0acwee)  
  
  ![](https://ww2.sinaimg.cn/large/ed039e1fgy1g1dnd9mrelj20dq02bt8l)  
  
  详细配置可以自行去*/config/refreshfix.php*进行修改，refreshinterval是刷新允许周期，maxretrytime是自动调整周期前允许重试的次数.  
  
* 防止request失败导致的空文件目录.(（づ￣3￣）づ拒绝首页空白)   
* 增加**简单的**状态码&出错日志(在/lib目录下生成).(requestcode.txt & requestlog.php)  
* 在**nexmoe主题**增加了一次性缩略图的加载限制，最多预览五十张（防止请求过多被限制）  
* 增加缓存刷新结果，如果刷新失败，后台会显示**重建缓存失败**，CLI模式在one.php执行刷新时如果失败会返回**Failed**  
  ![Example](https://ww2.sinaimg.cn/large/ed039e1fgy1g15sddvme4j20bg0650sh)  

## 店长推荐（误  
 选项推荐：  
 * token:一小时  
 * cache:20分钟  
 * 缓存类型:filecache  
 * 缓存过期时间（秒）：86400  
 * 自动调整周期前允许重试的次数：2  
 
 Nginx伪静态规则： 
 ```
 if (!-f $request_filename){  
set $rule_0 1$rule_0;  
}  
if (!-d $request_filename){  
set $rule_0 2$rule_0;  
}  
if ($rule_0 = "21"){  
rewrite ^/(.*)$ /index.php?/$1 last;  
}  
 ```
 
 程序安装失败错误：
 * 访问<https://apps.dev.microsoft.com/#/appList>  
 * 删除原有的oneindex应用  
 * 重试安装  
 * 其余还有跳转问题： <https://github.com/donwa/oneindex/issues/118>  
