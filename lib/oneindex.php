<?php
class oneindex {
    static $dir = array();
    static $file = array();
    static $thumb = array();
    /*检查刷新时间控制的配置是否生成*/
    static function check_refresh_config() {
        $path = dirname(__FILE__);
        if (!is_dir($path . '/../config')) {
            mkdir($path . '/../config');
        }
        if (!file_exists($path . '/../config/refreshfix.php')) {
            $r = array();
            $r['refreshinterval'] = 1200;
            $r['nextrefresh'] = 0;
            $r['retrytime'] = 0;
            $r['maxretrytime'] = 2;
            file_put_contents($path . '/../config/refreshfix.php', '<?php $rconfig=' . var_export($r, true) . ';?>');
        }
    }
    //使用 $refresh_token，获取 $access_token
    static function get_token($refresh_token) {
    }
    /*中转refreshcache，原refreshcache写法有多层循环*/
    static function refresh_cache($path) {
        $lastrefresh = '';
        if (file_exists(dirname(__FILE__) . '/../lastupdate.txt')) {
            $lastrefresh = strtotime(file_get_contents(dirname(__FILE__) . '/../lastupdate.txt'));
        }
        if (!file_exists(dirname(__FILE__) . '/refresh.lock')) {
            require dirname(__FILE__) . '/../config/refreshfix.php';
            date_default_timezone_set("Asia/Shanghai");
            if (time() >= intval($rconfig['nextrefresh'])) {
                file_put_contents(dirname(__FILE__) . '/refresh.lock', 'Refreshing');
                $rconfig['nextrefresh'] = time() + intval($rconfig['refreshinterval']);
                file_put_contents(dirname(__FILE__) . '/../config/refreshfix.php', '<?php $rconfig=' . var_export($rconfig, true) . ';?>'); /*储存一遍时间*/
                $rt = self::real_refresh_cache($path);
                if ($rt !== 'failed' && $rt !== 'timefailed' && $rt !== 'refreshing') {
                    file_put_contents(dirname(__FILE__) . '/../lastupdate.txt', date('Y-m-d h:i:sa', time()));
                }
                unlink(dirname(__FILE__) . '/refresh.lock');
                return $rt;
            } else { /*未到刷新时间*/
                return 'timefailed';
            }
        } else if (!empty($lastrefresh) && (time() - $lastrefresh) >= 600) { /*防止refresh.lock没有成功删除*/
            unlink(dirname(__FILE__) . '/refresh.lock');
            self::refresh_cache($path);
        } else { /*刷新中*/
            return 'refreshing';
        }
    }
    // 真-刷新缓存
    static function real_refresh_cache($path) {
        set_time_limit(0);
        ignore_user_abort();
        if (php_sapi_name() == "cli") {
            echo $path . PHP_EOL;
        }
        $items = onedrive::dir($path);
        if ($items == 'error') { /*报错中止*/
            return 'failed';
        }
        if (is_array($items) && $items !== 'error') {
            cache::set('dir_' . $path, $items, config('cache_expire_time'));
        }
        foreach ((array)$items as $item) {
            if ($item['folder']) {
                self::real_refresh_cache($path . $item['name'] . '/');
            }
        }
    }
    // 列目录
    static function dir($path = '/') {
        $path = self::get_absolute_path($path);
        if (!empty(self::$dir[$path])) {
            return self::$dir[$path];
        }
        self::$dir[$path] = cache::get('dir_' . $path, function () use ($path) {
            return onedrive::dir($path);
        }, config('cache_expire_time'));
        return self::$dir[$path];
    }
    // 获取文件信息
    static function file($path) {
        $path = self::get_absolute_path($path);
        $path_parts = pathinfo($path);
        $items = self::dir($path_parts['dirname']);
        if (!empty($items) && !empty($items[$path_parts['basename']])) {
            return $items[$path_parts['basename']];
        }
    }
    // 文件是否存在
    static function file_exists($path) {
        if (!empty(self::file($path))) {
            return true;
        }
        return false;
    }
    //获取文件内容
    static function get_content($path) {
        $item = self::file($path);
        // 仅小于10M 获取内容
        if (empty($item) or $item['size'] > 10485760) {
            return false;
        }
        return cache::get('content_' . $item['path'], function () use ($item) {
            $resp = fetch::get($item['downloadUrl']);
            if ($resp->http_code == 200) {
                return $resp->content;
            }
        }, config('cache_expire_time'));
    }
    //缩略图
    static function thumb($path, $width = 800, $height = 800) {
        $path = self::get_absolute_path($path);
        if (empty(self::$thumb[$path])) {
            self::$thumb[$path] = cache::get('thumb_' . $path, function () use ($path) {
                $url = onedrive::thumbnail($path);
                list($url, $tmp) = explode('&width=', $url);
                return $url;
            }, config('cache_expire_time'));
        }
        self::$thumb[$path].= strpos(self::$thumb[$path], '?') ? '&' : '?';
        return self::$thumb[$path] . "width={$width}&height={$height}";
    }
    //获取下载链接
    static function download_url($path) {
        $item = self::file($path);
        if (!empty($item['downloadUrl'])) {
            return $item['downloadUrl'];
        }
        return false;
    }
    static function web_url($path) {
        $path = self::get_absolute_path($path);
        $path = rtrim($path, '/');
        if (!empty(config($path . '@weburl'))) {
            return config($path . '@weburl');
        } else {
            $share = onedrive::share($path);
            if (!empty($share['link']['webUrl'])) {
                config($path . '@weburl', $share['link']['webUrl']);
                return $share['link']['webUrl'];
            }
        }
    }
    static function direct_link($path) {
        $web_url = self::web_url($path);
        if (!empty($web_url)) {
            $arr = explode('/', $web_url);
            if (strpos($arr[2], 'sharepoint.com') > 0) {
                $k = array_pop($arr);
                unset($arr[3]);
                unset($arr[4]);
                return join('/', $arr) . '/_layouts/15/download.aspx?share=' . $k;
            } elseif (strpos($arr[2], '1drv.ms') > 0) {
                # code...
                
            }
        }
    }
    //工具函数获取绝对路径
    static function get_absolute_path($path) {
        $path = str_replace(array('/', '\\', '//'), '/', $path);
        $parts = array_filter(explode('/', $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return str_replace('//', '/', '/' . implode('/', $absolutes) . '/');
    }
}
