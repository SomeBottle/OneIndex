
<h1 align="center"><a href="https://github.com/SomeBottle/OneIndex" target="_blank">OneIndex</a></h1>

> Oneindex Bottle Edition.<br>
> (๑•̀ㅂ•́)و✧  Original Program by [Donwa](https://github.com/donwa/oneindex). 

<p align="center">
<img alt="star" src="https://img.shields.io/github/stars/SomeBottle/OneIndex.svg"/>
<img alt="fork" src="https://img.shields.io/github/forks/SomeBottle/OneIndex.svg"/>
<img alt="GitHub last commit" src="https://img.shields.io/github/last-commit/SomeBottle/OneIndex.svg?label=commits">
<img alt="issues" src="https://img.shields.io/github/issues/SomeBottle/OneIndex.svg"/>
<img alt="Author" src="https://img.shields.io/badge/author-Bottle-red.svg"/>
<img alt="Download" src="https://img.shields.io/badge/download-85.2KB-brightgreen.svg"/>
</p>

## 停止更新
因为程序可维护性不高，目前不再对这个仓库进行维护。取而代之的是另一个项目：  
https://github.com/SomeBottle/OdIndex  

## 缘由  
之前听网友介绍了入了one的大门，结果鼓捣oneindex时我的历程很不顺利，一会儿文件列表出不来，一会儿jwt token又过期了...   
于是我修改了一下，**缓解了**部分问题.稍后可能会加入更多功能.  

## 修改内容  
1. 密码md5密文保存  
2. 自动判断HTTP 429请求过多的错误，并自动限制刷新的时间间隔，自动调整刷新周期.(如果没有到周期会返回提示)↓
  
  ![](https://ww2.sinaimg.cn/large/ed039e1fgy1g1dncyfprgj20iw0acwee)  
  
  ![](https://ww2.sinaimg.cn/large/ed039e1fgy1g1dnd9mrelj20dq02bt8l)  
  
  详细配置可以自行去 `/config/refreshfix.php` 进行修改，`refreshinterval` 是刷新允许周期，`maxretrytime` 是自动调整周期前允许重试的次数.  
  
3. 防止request失败导致的空文件目录.(（づ￣3￣）づ拒绝首页空白)   
4. 增加**简单的**状态码&出错日志(在 `/lib` 目录下生成).( `requestcode.txt` & `requestlog.php`)  
5. 在**nexmoe主题**增加了一次性缩略图的加载限制，最多预览五十张（防止请求过多被限制）  
6. 增加缓存刷新结果，如果刷新失败，后台会显示**重建缓存失败**，CLI模式在 `one.php` 执行刷新时如果失败会返回**Failed**  
  ![Example](https://ww2.sinaimg.cn/large/ed039e1fgy1g15sddvme4j20bg0650sh)  
7. 文件缓存过期**引用时**自动刷新   

## 店长推荐（误  
`crontab` 选项推荐[可选]，非必需:
1. token自动刷新: 两小时

```
0 */2 * * * * php /www/one.php token:refresh
```

2. cache自动刷新: 30分钟

```
*/30 * * * * php /www/one.php cache:refresh
```
`设置`选项推荐:
- `base.php` 中 `cache_refresh_time` 推荐为 `3600`(秒)
- 缓存类型推荐为 `filecache`
- 缓存过期时间推荐为 `86400` (秒)
- 自动调整周期前允许重试的次数(`/config/refreshfix.php`中的`maxretrytime`)推荐为  `8`  
 
## Nginx伪静态规则配置： 
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

## QA
1. 周期限制不起效？！
     请注意您的 `/config` 目录下的文件是否可读，php有时候会出现 `permission denied` 问题  

2. 账号绑定出错：  
 <https://github.com/donwa/oneindex/issues/511>   

3. 程序安装失败错误：
 * 访问<https://apps.dev.microsoft.com/#/appList>  
 * 删除原有的oneindex应用  
 * 重试安装  
 * 其余还有跳转问题： <https://github.com/donwa/oneindex/issues/118>  
