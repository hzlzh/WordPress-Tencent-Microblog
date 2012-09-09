<?php
/*
Plugin Name: Wordpress Tencent Microblog
Plugin URI: http://zlz.im/wordpress-tencent-microblog/
Description: 显示腾讯微博发言的插件，OAuth认证授权，安全可靠。采用了缓存机制，自定义刷新时间，不占用站点加载速度。可以在[外观]--[小工具]中调用，或者在任意位置使用 <code>&lt;?php display_tencent('username=you-ID&number=5'); ?&gt;</code> 调用。
Version: 1.1.2
Author: hzlzh
Author URI: http://zlz.im/

*/

//如果有遇到问题，请到http://zlz.im/wordpress-tencent-microblog/ 得到技术支持！
if ( ! defined( 'WP_PLUGIN_URL' ) )
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );//获得plugins网页路径
if ( ! defined( 'WP_PLUGIN_DIR' ) )
	define( 'WP_PLUGIN_DIR', WP_CONTENT_URL. '/plugins' );//获得plugins直接路径

set_include_path( dirname( dirname( __FILE__ ) ) . '/wordpress-tencent-microblog/lib/' );
require_once 'OpenSDK/Tencent/Weibo.php';
include 'OpenSDK/Tencent/tencentappkey.php';

OpenSDK_Tencent_Weibo::init( $appkey, $appsecret );
//打开session
session_start();
$WTM_settings1 = get_option( 'WTM_settings' );
if ( $WTM_settings1 ) {
	OpenSDK_Tencent_Weibo::setParam ( OpenSDK_Tencent_Weibo::ACCESS_TOKEN, $WTM_settings1['access_token'] );
	OpenSDK_Tencent_Weibo::setParam ( OpenSDK_Tencent_Weibo::OAUTH_TOKEN_SECRET, $WTM_settings1['oauth_token_secret'] );
}else {
	//echo 'dnoe';
}

$exit = false;
if ( isset( $_GET['exit'] ) ) {
	delete_option( 'WTM_settings' );
	OpenSDK_Tencent_Weibo::setParam( OpenSDK_Tencent_Weibo::OAUTH_TOKEN, null );
	OpenSDK_Tencent_Weibo::setParam( OpenSDK_Tencent_Weibo::ACCESS_TOKEN, null );
	OpenSDK_Tencent_Weibo::setParam( OpenSDK_Tencent_Weibo::OAUTH_TOKEN_SECRET, null );
	//echo '<a href="?go_oauth">点击去授权</a>';
}else if ( OpenSDK_Tencent_Weibo::getParam ( OpenSDK_Tencent_Weibo::ACCESS_TOKEN ) &&
		OpenSDK_Tencent_Weibo::getParam ( OpenSDK_Tencent_Weibo::OAUTH_TOKEN_SECRET )
	) {
		$uinfo = OpenSDK_Tencent_Weibo::call( 'statuses/broadcast_timeline',
			array(
				'type' => '1',
				'contenttype' => '0',
			) );
	}
else if ( isset( $_GET['oauth_token'] ) && isset( $_GET['oauth_verifier'] ) ) {
		//从Callback返回时
		if ( OpenSDK_Tencent_Weibo::getAccessToken( $_GET['oauth_verifier'] ) ) {
			$uinfo = OpenSDK_Tencent_Weibo::call( 'user/info' );
			$WTM_settings = array( 'WTM_settings' );
			$WTM_settings['oauth_token'] = $_GET['oauth_token'];
			$WTM_settings['oauth_verifier'] = $_GET['oauth_verifier'];
			$WTM_settings['access_token'] = OpenSDK_Tencent_Weibo::getParam( OpenSDK_Tencent_Weibo::ACCESS_TOKEN );
			$WTM_settings['oauth_token_secret'] = OpenSDK_Tencent_Weibo::getParam( OpenSDK_Tencent_Weibo::OAUTH_TOKEN_SECRET );
			$WTM_settings['num'] = 5;
			update_option( 'WTM_settings', $WTM_settings );
			//var_dump($uinfo);
		}
		$exit = true;
	}
else if ( isset( $_GET['go_oauth'] ) ) {
		$callback = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
		$request_token = OpenSDK_Tencent_Weibo::getRequestToken( $callback );
		$url = OpenSDK_Tencent_Weibo::getAuthorizeURL( $request_token );
		header( 'Location: ' . $url );
	}else if ( $exit || isset( $_GET['exit'] ) ) {
		delete_option( 'WTM_settings' );
	}

//展示函数
function display_tencent( $args = '' ) {
	$default = array(
		'username'=>'Weibo_ID',
		'number'=>'5',
		'time'=>'3600' );
	$r = wp_parse_args( $args, $default );
	extract( $r );

	$uinfo = OpenSDK_Tencent_Weibo::call( 'statuses/broadcast_timeline',
		array(
			'reqnum' => $number,
			'type' => '1',
			'contenttype' => '0',
		) );

	$decodedArray =$uinfo;
	echo '<ul style="list-style-type:none;">';
	foreach ( $decodedArray['data']['info'] as $value ) {
		echo '<li><div class="microblog"><a href="http://t.qq.com/'.$value['nick'].'" rel="external nofollow" title="来自 腾讯微博" target="_blank" style="padding-right:3px;"><img class="microblog-ico"  alt="腾讯微博" src="'.WP_PLUGIN_URL.'/wordpress-tencent-microblog/txwb.png" /></a><span class="microblog-content">'.str_replace( '&#160;', ' ', $value['origtext'] ).'</span>  <span class="microblog-from" style="font-size:smaller;">-'.date( "Y/m/d", $value['timestamp'] ).' 来自 '.$value['from'].'-</span></div></li>';
	}
	echo '</ul>';
}

//扩展类 WP_Widget
class TencentMicroblog extends WP_Widget
{
	//定义后台面板展示文字
	function TencentMicroblog() {
		$widget_des = array( 'classname'=>'wordpress-tencent-microblog', 'description'=>'在博客显示腾讯微博的发言' );
		$this->WP_Widget( false, '腾讯微博', $widget_des );
	}

	//定义widget后台选项
	function form( $instance ) {
		$decodedArray = OpenSDK_Tencent_Weibo::call( 'user/info' );
		$instance = wp_parse_args( (array)$instance, array(
				'title'=>'腾讯微博',
				'username'=>'Weibo_ID',
				'number'=>5,
				'time'=>'3600' ) );
		$title = htmlspecialchars( $instance['title'] );
		$username = htmlspecialchars( $instance['username'] );
		$number = htmlspecialchars( $instance['number'] );
		$time = htmlspecialchars( $instance['time'] );
		if ( isset( $_GET['exit'] ) ) {
			echo '<p><a class="button-primary widget-control-save" href="?go_oauth">点击OAuth授权</a></p>';}
		else if ( isset( $_GET['oauth_token'] ) && isset( $_GET['oauth_verifier'] ) ) {
			echo '<p><a style="cursor: default;" class="button-primary widget-control-save">已成功授权</a> <img src="'.$decodedArray['data']['head'].'/20" alt=""/> <span>@'.$decodedArray['data']['nick'].'</span></p>';}
		else if ( OpenSDK_Tencent_Weibo::getParam ( OpenSDK_Tencent_Weibo::ACCESS_TOKEN ) && OpenSDK_Tencent_Weibo::getParam ( OpenSDK_Tencent_Weibo::OAUTH_TOKEN_SECRET ) ) {
			echo '<p><a style="cursor: default;" class="button-primary widget-control-save">已成功授权</a> <img src="'.$decodedArray['data']['head'].'/20" alt=""/> <span>@'.$decodedArray['data']['nick'].'</span> <a href="?exit">注销</a></p>';}
		else{
			echo '<p><a class="button-primary widget-control-save" href="?go_oauth">点击OAuth授权</a></p>';}
		echo '<p style="color:#FF3333;">任何反馈@<a target="_blank" href="http://t.qq.com/hzlzh-com">hzlzh-com</a> 反馈</p><p><label for="'.$this->get_field_name( 'title' ).'">侧边栏标题:<input style="width:200px;" id="'.$this->get_field_id( 'title' ).'" name="'.$this->get_field_name( 'title' ).'" type="text" value="'.$title.'" /></label></p>
		<p><label for="'.$this->get_field_name( 'username' ).'">用户名:  <i>(字母+数字)</i><input style="width:200px;" id="'.$this->get_field_id( 'username' ).'" name="'.$this->get_field_name( 'username' ).'" type="text" value="'.$username.'" /></label></p>
		<p><label for="'.$this->get_field_name( 'number' ).'">显示数量: <i>(1-100条)</i><input style="width:200px" id="'.$this->get_field_id( 'number' ).'" name="'.$this->get_field_name( 'number' ).'" type="text" value="'.$number.'" /></label></p>
		<p><label for="'.$this->get_field_name( 'time' ).'">缓存时间:<input style="width:200px" id="'.$this->get_field_id( 'time' ).'" name="'.$this->get_field_name( 'time' ).'" type="text" value="'.$time.'" />秒</label></p>';
	}

	//更新函数
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( stripslashes( $new_instance['title'] ) );
		$instance['username'] = strip_tags( stripslashes( $new_instance['username'] ) );
		$instance['number'] = strip_tags( stripslashes( $new_instance['number'] ) );
		$instance['time'] = strip_tags( stripslashes( $new_instance['time'] ) );
		return $instance;
	}

	//显示函数
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '&nbsp;' : $instance['title'] );
		$username = empty( $instance['username'] ) ? 'Weibo_ID' : $instance['username'];
		$number = empty( $instance['number'] ) ? 1 : $instance['number'];
		$time = empty( $instance['time'] ) ? 3600 : $instance['time'];

		echo $before_widget;
		echo $before_title . $title . $after_title;
		display_tencent( "username=$username&number=$number&time=$time" );
		echo $after_widget;
	}
}

//注册widget
function TencentMicroblogInit() {
	register_widget( 'TencentMicroblog' );
}

add_action( 'widgets_init', 'TencentMicroblogInit' );

function TencentMicroblogPage() {
	//add_options_page('TencentMicroblogInit Options', 'Wordpress Tencent Microblog', 10, 'wordpress-tencent-microblog/options.php');
	add_options_page('腾讯微博插件', '腾讯微博插件', 'manage_options','WTM-options', 'TencentMicroblog_options_page');
}
add_action('admin_menu', 'TencentMicroblogPage');

function TencentMicroblog_options_page() {
?>
<div class="wrap">
<?php screen_icon(); ?>
<h2>腾讯微博插件设置</h2>
<br />
<p>
<?php
$decodedArray = OpenSDK_Tencent_Weibo::call( 'user/info' );
if ( isset( $_GET['exit'] ) ) {
			echo '<p><a class="button-primary widget-control-save" href="?page=WTM-options&go_oauth">点击OAuth授权</a></p>';}
		else if ( isset( $_GET['oauth_token'] ) && isset( $_GET['oauth_verifier'] ) ) {
			echo '<p>[状态]： <a style="cursor: default;" class="button-primary widget-control-save">已成功授权</a></p>'.
			'<br /> <p>[已授权帐号]： <img src="'.$decodedArray['data']['head'].'/40" alt=""/> <span>@'.$decodedArray['data']['nick'].'</span></p>';}
		else if ( OpenSDK_Tencent_Weibo::getParam ( OpenSDK_Tencent_Weibo::ACCESS_TOKEN ) && OpenSDK_Tencent_Weibo::getParam ( OpenSDK_Tencent_Weibo::OAUTH_TOKEN_SECRET ) ) {
			echo '<p>[状态]： <a style="cursor: default;" class="button-primary widget-control-save">已成功授权</a></p>'.
			'<br /> <p>[已授权帐号]： <img src="'.$decodedArray['data']['head'].'/40" alt=""/> <span>@'.$decodedArray['data']['nick'].'</span> <a href="?page=WTM-options&exit">注销?</a></p>';}
		else{
			echo '<p><a class="button-primary widget-control-save" href="?go_oauth">点击OAuth授权</a></p>';}
		
?>
<div class="update-nag" id="donate">
<div style="text-align: center;">
<span style="font-size: 20px;margin: 5px 0;display: block;">使用说明</span>
<br />
授权完成之后，在[外观] -> <a href="/wp-admin/widgets.php">[小挂件]</a>中使用，也可以使用下面代码在WordPress任意页面的任意位置调用：
<br />
<code>&lt;?php display_tencent('number=5'); ?&gt;</code> 
<br />
任何反馈 -> @<a target="_blank" href="http://twitter.com/hzlzh">hzlzh</a>
</div>
</div>
</p>
</div>
<?php
}
?>
