=== Wordpress Tencent Microblog ===
Contributors: hzlzh
Donate link: http://zlz.im/wordpress-tencent-microblog/
Tags: 腾讯微博,QQ,微博,腾讯,同步,Tencent,microblog,weibo
Requires at least: 2.7
Tested up to: 3.4.2
Stable tag: 1.1.2

显示腾讯微博发言的插件，精简，纯洁，快速，安全可靠，采取缓存机制，官方API接口，支持1-10条发言输出。

== Description ==

显示腾讯微博发言的插件，从官方处获得时间轴，安全可靠。采用了缓存机制，可自定义刷新时间，不占用站点加载速度。可以在[外观]--[小工具]中调用，也可以在任意位置使用`<?php display_tencent('username=Weibo_ID&number=5'); ?>` 调用。

== Installation ==

1. 上传 `wordpress-tencent-microblog`插件到 `/wp-content/plugins/` 目录
2. 在Wordpress后台控制面板"插件(Plugins)"菜单下激活"wordpress-tencent-microblog"
3. 在Wordpress后台控制面板"外观(Appearance)->小工具(Widgets)"下使用`腾讯微博`
4. 首次点击"OAuth认证授权"

== Frequently Asked Questions ==

= 我不想在侧边栏调用，而是在任意位置可以么？怎么做？ =

可以！需要激活插件后，在任意位置使用`<?php display_tencent('username=Weibo_ID&number=5'); ?>`调用即可，只需要修改`Weibo_ID` 为你的腾讯微博帐号，`5`是你想要展示的条数。



= 使用之前必须先OAuth认证登录吗？ =

是的，目前采用官方OAuth1.0

= 如果我的HOST不支持copy()函数怎么办？ =

可以去除缓存功能，直接抓取API即可，参见 <a href="http://zlz.im/wordpress-tencent-microblog/">使用说明</a>

== Screenshots ==

1. 使用侧边栏[小工具]调用的效果
2. 后台截图

== Changelog ==

= V 1.1.2 =
*使用OAuth授权获取数据
*添加后台设置页面

= V 1.1.1 =
*更新了使用方法和获取Sign值的教程图
*添加了插件页面banner图

= V 1.1.0 =
*使用腾讯微博官方提供的 RSS/JSON 接口获取数据，安全快速。

= V 1.0.9 =
*使用Oauth认证得到API来获取信息
*修改少许代码兼容新版API
*重新使用 http://q.hzlzh.com/为API

= V 1.0.7 =
*增加了对copy()，file_get_contents等函数的验证，在网站主机不支持饿情况下，会提示原因。
*新增加了一个判断更新的因素，当抓取文件为空时，从新抓取。
*增加了file()函数抓取方式
*去掉手动注释部分
*原API 地址 http://q.hzlzh.com/重新可用！

= V 1.0.5 =
*修改了几个重要的注释
*提供了WIN主机无法使用copy()函数的解决方案注释
*修改了一些细节

= V 1.0.3 =
*现在支持 1至10 条的发言调用
*修复了有实名认证的用户的显示错误BUG
*加入了 CSS 样式，预留自定义接口

= V 1.0.0  =
*在Wordpress中同步显示腾讯微博中的发言
*无需登录，采取民间API，安全可靠
*由于使用缓存，本插件目录需要有写入权限
*目前调取显示数量为1条，更多需要API的支持，尽在下一个版本

== Upgrade Notice ==
= V 1.0.9 =
使用全新腾讯官方API Oauth认证机制，安全可靠，高速！

= V 1.0.7 =
加入了新的缓存判断，可以有效避免空抓的现象出现

= V 1.0.6 =
为了减少API 被禁止的可能，使用新的API地址，http://q.hzlzh.com/wordpress/

= V 1.0.5 =
修改了几个重要的注释，提供了WIN主机无法使用copy()函数的解决方案注释

= 1.0.3 =
本版本已经完善了10条以内信息的完善，并且预留了CSS样式名

= 1.0.0 =
这个是第一个版本，由于API限制只能调用1发言
