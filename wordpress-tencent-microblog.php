<?php
/*
Plugin Name: Wordpress Tencent Microblog
Plugin URI: http://zlz.im/wordpress-tencent-microblog/
Description: 显示腾讯微博发言的插件，无需密码，安全可靠。采用了缓存机制，自定义刷新时间，不占用站点加载速度。可以在[外观]--[小工具]中调用，或者在任意位置使用 <code>&lt;?php display_tencent('username=you-ID&number=5'); ?&gt;</code> 调用。
Version: 1.1.0
Author: hzlzh
Author URI: http://zlz.im

*/
//如果有遇到问题，请到http://zlz.im/wordpress-tencent-microblog/ 得到服务支持！
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );//获得plugins网页路径
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_URL. '/plugins' );//获得plugins直接路径

//展示函数
function display_tencent($args = ''){
	$default = array(
		'username'=>'Weibo_ID',
		'number'=>'1',
		'api'=>'4735433f9fbf9bb2983d9095595f5c5c36abdd0b',
		'time'=>'3600');
	$r = wp_parse_args($args,$default);
	extract($r);
	$url = 'http://v.t.qq.com/output/json.php?type=1&name='.$username.'&sign='.$api;
	if(function_exists("copy")){
	$e = WP_PLUGIN_DIR.'/wordpress-tencent-microblog/'.$username.'.json';
	if ( !is_file($e) || (time() - filemtime($e)) > $time|| filesize($e) < 1000){
	//当缓存不存在或超过 $time 时更新,或者得到文件大小小于1000
	copy($url, $e);}//拷贝到本地，一般主机都支持这个函数,需要目录的写入权限
	$jsonObject = substr(file_get_contents($e),10,-1);
}else{
	if(function_exists("file_get_contents")){
	$jsonObject = file_get_contents($url);
}else{
	if(function_exists( "file")){
	$f=file($url);
	for($i=0;$i<count($f);$i++){
		$jsonObject.=$f[$i];
	}
}else{
echo "<div>该主机不支持本插件，由于禁用了copy()或file_get_contents()等函数，请在http://zlz.im/wordpress-tencent-microblog/ 告知出错原因，便于我维护更新插件！</div>";
		}
	}
}
$decodedArray =json_decode($jsonObject, true);
if($number - count($decodedArray['data']) >0)
$number = count($decodedArray['data']);
		echo '<ul style="list-style-type:none;">';
		for($i = 0;$i< $number;$i++){
			echo '<li><div class="microblog"><a href="http://t.qq.com/'.$username.'" rel="external nofollow" title="来自 腾讯微博" target="_blank" style="padding-right:3px;"><img class="microblog-ico"  alt="腾讯微博" src="'.WP_PLUGIN_URL.'/wordpress-tencent-microblog/txwb.png" /></a><span class="microblog-content">'.str_replace('&#160;',' ',$decodedArray['data'][$i]['content']).'</span>  <span class="microblog-from" style="font-size:smaller;">-'.date("Y/m/d", $decodedArray ['data'][$i]['timestamp']).' 来自 '.$decodedArray ['data'][$i]['fromarea'].'-</span></div></li>';
		}
		echo '</ul>';
}

//扩展类 WP_Widget
class TencentMicroblog extends WP_Widget
{
	//定义后台面板展示文字
	function TencentMicroblog(){
		$widget_des = array('classname'=>'wordpress-tencent-microblog','description'=>'在博客显示腾讯微博的发言');
		$this->WP_Widget(false,'腾讯微博',$widget_des);
	}

	//定义widget后台选项
	function form($instance){
		$instance = wp_parse_args((array)$instance,array(
		'title'=>'腾讯微博',
		'username'=>'Weibo_ID',
		'api'=>'4735433f9fbf9bb2983d9095595f5c5c36abdd0b',
		'number'=>1,
		'time'=>'3600'));
		$title = htmlspecialchars($instance['title']);
		$username = htmlspecialchars($instance['username']);
		$api = htmlspecialchars($instance['api']);
		$number = htmlspecialchars($instance['number']);
		$time = htmlspecialchars($instance['time']);
		echo '<p><b>首次使用获取官方API--></b><a target="_blank" href="http://open.t.qq.com/resource.php?i=3,3">点此获取</a></p><p style="color:#FF3333;">任何问题请@我的微博<a target="_blank" href="http://t.qq.com/hzlzh-com">hzlzh-com</a> 反馈</p><p><label for="'.$this->get_field_name('title').'">侧边栏标题:<input style="width:200px;" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text" value="'.$title.'" /></label></p>
		<p><label for="'.$this->get_field_name('username').'">用户名:<input style="width:200px;" id="'.$this->get_field_id('username').'" name="'.$this->get_field_name('username').'" type="text" value="'.$username.'" /></label></p>
		<p><label for="'.$this->get_field_name('api').'">API地址(sign值): <a target="_blank" href="http://zlz.im/wordpress-tencent-microblog/">[?]</a><input style="width:200px;" id="'.$this->get_field_id('api').'" name="'.$this->get_field_name('api').'" type="text" value="'.$api.'" /></label></p>
		<p><label for="'.$this->get_field_name('number').'">显示数量:<input style="width:200px" id="'.$this->get_field_id('number').'" name="'.$this->get_field_name('number').'" type="text" value="'.$number.'" /></label></p>
		<p><label for="'.$this->get_field_name('time').'">缓存时间:<input style="width:200px" id="'.$this->get_field_id('time').'" name="'.$this->get_field_name('time').'" type="text" value="'.$time.'" />秒</label></p>';
	}
	
	//更新函数
	function update($new_instance,$old_instance){
		$instance = $old_instance;
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['username'] = strip_tags(stripslashes($new_instance['username']));
		$instance['api'] = strip_tags(stripslashes($new_instance['api']));
		$instance['number'] = strip_tags(stripslashes($new_instance['number']));
		$instance['time'] = strip_tags(stripslashes($new_instance['time']));
		return $instance;
	}

	//显示函数
	function widget($args,$instance){
		extract($args);
		$title = apply_filters('widget_title',empty($instance['title']) ? '&nbsp;' : $instance['title']);
		$username = empty($instance['username']) ? 'Weibo_ID' : $instance['username'];
		$number = empty($instance['number']) ? 1 : $instance['number'];
		$api = empty($instance['api']) ? '4735433f9fbf9bb2983d9095595f5c5c36abdd0b' : $instance['api'];
		$time = empty($instance['time']) ? 3600 : $instance['time'];
		
		echo $before_widget;
		echo $before_title . $title . $after_title;
		display_tencent("username=$username&number=$number&api=$api&time=$time");
		echo $after_widget;
	}
}

//注册widget
function TencentMicroblogInit(){
	register_widget('TencentMicroblog');
}

add_action('widgets_init','TencentMicroblogInit');
?>