<?php
/*
Plugin Name: HaxhitsPlayer
Plugin URI: https://wordpress.org/plugins/haxhitsplayer/
Description: Plugin to get direct link google drive, mp4upload, xvideos, rapidvideo to haxhits player
Version: 1.2.3
Author: Haxhits.com
Author URI: https://haxhits.com
License: GPL v2 or later
*/
add_action( 'admin_menu', 'haxhitsplayer_menu' );
function haxhitsplayer_menu() {
	add_menu_page( 
		'Haxhits Player Page',
		'Haxhits Player',
		'manage_options',
		'haxhitsplayer.php',
		'haxhitsplayer_init'
	);
}

if ( ! defined( 'ABSPATH' ) ) exit;

function haxhitsplayer_init(){

if(!current_user_can('manage_options')){
	die('you dont have authorization to view this page');
}	

if(sanitize_text_field($_POST["secretkey"]) != "" && sanitize_text_field($_POST['form_key'] == 'settings')){
	if(check_admin_referer('my_nonce_action', 'my_nonce_field')){
		update_option('hxh_secretkey', sanitize_text_field($_POST["secretkey"]));
		update_option('hxh_tag', sanitize_text_field($_POST["tag"]));
		update_option('hxh_link', str_replace('http://','', esc_url_raw($_POST["link"])));
		update_option('hxh_poster', str_replace('http://','', esc_url_raw($_POST["poster"])));
		update_option('hxh_subtitle', str_replace('http://','', esc_url_raw($_POST["subtitle"])));
	}else{
		die('<div class="notice notice-info"><p><strong>Notice : </strong>Invalid nonce</p></div><meta http-equiv="refresh" content="1; url='.str_replace( '%7E', '~', $_SERVER['REQUEST_URI']).'" /><br/>');
	}
	die('<div class="notice notice-info"><p><strong>Notice : </strong>Saved Settings</p></div><meta http-equiv="refresh" content="1; url='.str_replace( '%7E', '~', $_SERVER['REQUEST_URI']).'" /><br/>');
}
?>
<br/>
<form method="post" action="">
<input type="hidden" name="form_key" value="settings">

<table class="form-table">
<tbody>
<tr>
	<th scope="row"><label for="ap_key">Secret Key [<a href="https://hxload.io/?p=account">Click Here</a>]</label></th>
	<td><input name="secretkey" type="text" value="<?php echo get_option("hxh_secretkey"); ?>" placeholder="Insert Your Secret Key" class="regular-text"></td>
</tr>
</tbody>
</table>
<table class="form-table">
	<tbody>
	<tr>
	<th scope="row"><label for="ap_key">TAG</label></th>
	<td><input name="tag" type="text" value="<?php if(get_option("hxh_tag")==""){echo "gdu"; } else {echo get_option("hxh_tag"); } ?>" placeholder="Insert Tag" class="regular-text"></td>
	</tr>
	<tr>
	<th scope="row"><label for="ap_key">Link/ID</label></th>
	<td><input name="link" type="text" value="<?php if(get_option("hxh_link")==""){echo "link"; } else {echo get_option("hxh_link"); } ?>" class="regular-text"></td>
	</tr>
	<tr>
	<th scope="row"><label for="ap_key">Poster</label></th>
	<td><input name="poster" type="text" value="<?php if(get_option("hxh_poster")==""){echo "poster"; } else {echo get_option("hxh_poster"); } ?>" class="regular-text"></td>
	</tr>
	<tr>
	<th scope="row"><label for="ap_key">Subtitle</label></th>
	<td><input name="subtitle" type="text" value="<?php if(get_option("hxh_subtitle")==""){echo "subtitle"; } else {echo get_option("hxh_subtitle"); } ?>" class="regular-text"></td>
	</tr>
	</tbody>
</table>
<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save"></p>
<?php wp_nonce_field('my_nonce_action','my_nonce_field');?>
</form>
Example Shortcode Tag: [gdu link=""] or [gdu link="" subtitle=""] or [gdu link="" poster="" subtitle=""]<br/>
Example Multi Subtitle Tag: [gdu link="" subtitle="http://myweb/sub1.srt=English|http://myweb/sub2.srt=Indonesia"] (Last parameter (http://myweb/sub2.srt=Indonesia) is default subtitle)
<br/><br/>
* Example Link/ID:<br/>
-GDrive Link (mp4 format only) : https://drive.google.com/file/d/xxxxxxx/preview<br/>
-XVideos Link : https://www.xvideos.com/embedframe/xxxxxxx<br/>
-MP4Upload Link : https://www.mp4upload.com/embed-xxxxxxx.html<br/>
-RapidVideo Link : https://www.rapidvideo.com/e/xxxxxxx
<?php }

function curl_hxh_player($url){
	$ch = @curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	$head[] = "Connection: keep-alive";
	$head[] = "Keep-Alive: 300";
	$head[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
	$head[] = "Accept-Language: en-us,en;q=0.5";
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.124 Safari/537.36');
	curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
	curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	$page = curl_exec($ch);
	curl_close($ch);
	return $page;
}

function getHxh_PID(){
	global $post;
	$post_id = $post->ID;
	
	if(!$post_id){
		$post_id = get_the_ID();
	}
	if(!$post_id){
		$post_id = intval($_POST['post_id']);
	}	
	return $post_id;
}

function haxhitsplayerTag_load($data){
	$post_id = getHxh_PID();
	$CurDomain = 'http'.(empty($_SERVER['HTTPS'])?'':'s').'://'.$_SERVER['HTTP_HOST'].'/';
	
	$att_link = get_option("hxh_link");
	
	$att_poster = get_option("hxh_poster");
	if($att_poster && substr($att_poster, 0, 4) === "http" && substr($att_poster, 0, 5) === "https"){
		$att_poster = $CurDomain . $att_poster;
	}
	
	$att_subtitle = get_option("hxh_subtitle");
	if($att_subtitle && substr($att_subtitle, 0, 4) === "http" && substr($att_subtitle, 0, 5) === "https"){
		$att_subtitle = $CurDomain . $att_subtitle;
	}
	
	$data = array(
		title => get_the_title($post_id),
		link => urlencode($data[$att_link]),
		poster => urlencode($data[$att_poster]),
		subtitle => urlencode($data[$att_subtitle])
	);
	
	return Hxh_UpdateData($data);
}

function Hxh_UpdateData($data,$tid=""){
	$post_id = getHxh_PID();
	$ctag = get_option("hxh_tag".$tid);
	$hcachetime = get_option("hxh_cachetime");	
	$restat = get_option("hxh_restat");					
	
	$data = json_encode($data);
	$data = str_replace('\/', '/', $data);     
	
	$lcache = get_post_meta($post_id, "hxh_".$ctag."_lcache", $single = true);
	if(!is_numeric($lcache)){
		$lcache = 0;
	}
	$lcache = $lcache + $hcachetime;	
	if(time() > $lcache || $restat == 1 || !get_post_meta($post_id, "hxh_".$ctag."_result", $single = true)){
		$dataResp = curl_hxh_player("https://hxload.io/api/getlink/?ptype=glwp&datajson=".urlencode($data)."&secretkey=".get_option("hxh_secretkey"));
		if(strpos($dataResp,'|') === false && get_post_meta($post_id, "hxh_".$ctag."_result", $single = true)){
			return get_post_meta($post_id, "hxh_".$ctag."_result", $single = true);
		}

		$dataSplit = explode('|',$dataResp);
		$dataResult = $dataSplit[0];
		$expcacheData = $dataSplit[1];
		$dataRes = $dataSplit[2];
		
		if(is_numeric($expcacheData)){
			if(!add_post_meta($post_id, 'hxh_'.$ctag.'_data', $dataResp, true)){ 
				update_post_meta($post_id, 'hxh_'.$ctag.'_data', $dataResp);
			}				
			if(!add_post_meta($post_id, 'hxh_'.$ctag.'_result', $dataResult, true)){
				update_post_meta($post_id, 'hxh_'.$ctag.'_result', $dataResult);
			}				
			if(!add_post_meta($post_id, 'hxh_'.$ctag.'_lcache', time(), true)){ 
				update_post_meta($post_id, 'hxh_'.$ctag.'_lcache', time());
			}
			if(is_numeric($dataRes)){
			update_option('hxh_restat', sanitize_text_field($dataRes));
			}
			if(!is_numeric($expcacheData)){ 
				$expcacheData = 10800;
			}
			update_option('hxh_cachetime', sanitize_text_field($expcacheData));
			$data = $dataResult;
		}else{
			return '<center><font color="red">Error, invalid data response </font></center>';
		}
	}else{
		$data = get_post_meta($post_id, "hxh_".$ctag."_result", $single = true);
	}
		
	return $data;
}

add_shortcode(get_option("hxh_tag"), 'haxhitsplayerTag_load');

?>