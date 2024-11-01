<?php
/*
Plugin Name: Click to Edit
Description: Edit content in-place when users are logged in as authors
Author: Jared Novack + Upstatement
Version: 1.3
Author URI: http://upstatement.com
*/
global $wp_version;
global $clickToEdit;

$exit_msg = 'Click to Edit reqiures WordPress 3.0 or newer';
if (version_compare($wp_version, '3.0', '<')){
	exit ($exit_msg);
}

if (!class_exists('ClickToEdit')): 
class ClickToEdit {
	var $DB_option = 'ClickToEdit_options';
	var $plugin_url;
	
	function ClickToEdit(){
		$this->plugin_url = trailingslashit(WP_PLUGIN_URL.'/'.dirname(plugin_basename(__FILE__)));
		
		// add scripts and styles
		add_action('init', array(&$this, 'scripts_action'));
		
	}
	
	function install(){
		$options = array('auto_edit' => 1, 'no_admin_button' => 0);
		update_option('upstatement_ce_options', $options);
	}
	
	function register_fr_settings() {
		register_setting('content-editable-settings', 'reference-post-type');
		register_setting('content-editable-settings', 'button-text');
	}
	
	// add scripts
	function scripts_action(){
		if (is_user_logged_in() && !is_admin()){
			wp_enqueue_script('jquery');
			$nonce = wp_create_nonce('content-editable-nonce');
			wp_enqueue_script('content-editable', $this->plugin_url.'content-editable.js', 'jquery');
			wp_localize_script('content-editable', 'ContentEditableSettings', array('content_editable_url' => $this->plugin_url, 'nonce' => $nonce));
			self::style_action();
		}
	}
	
	// add css
	function style_action() {
		wp_enqueue_style('content-editable-style', $this->plugin_url.'content-editable-style.css');
	}
	
}
endif;

if (class_exists('ClickToEdit')):
	$clickToEdit = new ClickToEdit();
	if (isset($clickToEdit)){
		register_activation_hook(__FILE__, array(&$clickToEdit, 'install'));
	}
endif;

function ce_get_editable_custom($key, $pid = 0){
	if ($pid == 0){
		$pid = get_the_ID();
	}
	if (ce_can_edit($pid)){
		$attrs = ' data-key="'.$key.'" ';
		$cust = get_post_custom_values($key, $pid);
		$ret = ce_wrap_content($cust[0], $pid, 'custom', $attrs);
		//$ret = '<div class="contenteditable custom" contenteditable="true" data-pid="'.$post->ID.'" >'.$post->$key.'</div>';
		return $ret;
	}
	return $post->$key;
}

function ce_wrap_content_field($content, $ID, $field){
	if ($field == 'title' || $field == 'post_title'){
		return ce_get_editable_title($ID);
	}
	if ($field == 'body' || $field == 'content' || $field == 'post_content'){
		return ce_get_editable_content($ID);
	} else {
		return ce_get_editable_custom($field, $ID);
	}
}

function ce_get_editable_content($pid){
	$post = get_post($pid);
	$content = $post->post_content;
	if (ce_can_edit($pid)){
		$ret = ce_wrap_content(($content), $post->ID, 'post_content');
		return $ret;
	} 
	return wpautop($content);
}

function ce_get_editable_title($pid){
	$post = get_post($pid);
	if (ce_can_edit($post->ID)){
		$ret = ce_wrap_content($post->post_title, $post->ID, 'title');
		return $ret;
	} 
	return $post->post_title;
}

function ce_wrap_content($content, $pid, $class = 'furniture', $attrs = ''){
	$c = '<span class="contenteditable '.$class.'" data-pid="'.$pid.'"  '.$attrs.'>';
	//$content .= '<span class="trigger-ce-edit ce-edit-button">Edit</span>';
	$c .= '<span class="saver">'.$content.'</span></span>';
	return $c;
}

function ce_can_edit($pid){
	$link = get_edit_post_link($pid);
	if ($link){
		return true;
	}
	return false;
}

function upstatement_ce_options_setter(){
	$options = get_option('upstatement_ce_options');
	if (!$options){
		update_option('upstatement_ce_options', array('auto_edit' => 1));	
	}
	$auto = $options['auto_edit'];
	if ($auto === false){
		$options['auto_edit'] = 1;
		update_option('upstatement_ce_options', $options);	
	}
	return $options;
}

$options = upstatement_ce_options_setter();

/*  =================

	Filters for the_content and the_title 
	
	================= */

if ($options['auto_edit']){
	add_filter('the_content', 'ce_the_content');
	add_filter('the_title', 'ce_the_title');
}
function ce_the_content($content){
	$pid = get_the_ID();
	if (ce_can_edit($pid) && !is_admin()){
		$content = ce_wrap_content($content, $pid);
	}
	return $content;
}

function ce_the_title($title){
	$pid = get_the_ID();
	if (ce_can_edit($pid) && !is_admin()){
		$title = ce_wrap_content($title, $pid, 'title');
	}
	return $title;
}


function ce_wpautop($pee, $br = true){
	if ( trim($pee) === '' ){
		return '';
	}
	$pee = $pee . "\n"; // just to make things a little easier, pad the end
	$pee = preg_replace('|<br />\s*<br />|', "\n\n", $pee);
	// Space things out a little
	$allblocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|option|form|map|area|blockquote|address|math|style|input|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';
	$pee = preg_replace('!(<' . $allblocks . '[^>]*>)!', "\n$1", $pee);
	$pee = preg_replace('!(</' . $allblocks . '>)!', "$1\n\n", $pee);
	$pee = str_replace(array("\r\n", "\r"), "\n", $pee); // cross-platform newlines
	if ( strpos($pee, '<object') !== false ) {
		$pee = preg_replace('|\s*<param([^>]*)>\s*|', "<param$1>", $pee); // no pee inside object/embed
		$pee = preg_replace('|\s*</embed>\s*|', '</embed>', $pee);
	}
	$pee = preg_replace("/\n\n+/", "\n\n", $pee); // take care of duplicates
	// make paragraphs, including one at the end
	$pees = preg_split('/\n\s*\n/', $pee, -1, PREG_SPLIT_NO_EMPTY);
	$pee = '';
	foreach ( $pees as $tinkle )
		$pee .= '<p class="auto-p">' . trim($tinkle, "\n") . "</p>\n";
	$pee = preg_replace('|<p class="auto-p">\s*</p>|', '', $pee); // under certain strange conditions it could create a P of entirely whitespace
	$pee = preg_replace('!<p class="auto-p">([^<]+)</(div|address|form)>!', "<p class='auto-p'>$1</p></$2>", $pee);
	$pee = preg_replace('!<p class="auto-p">\s*(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee); // don't pee all over a tag
	$pee = preg_replace("|<p class='auto-p'>(<li.+?)</p>|", "$1", $pee); // problem with nested lists
	$pee = preg_replace('|<p class="auto-p"><blockquote([^>]*)>|i', "<blockquote$1><p class='auto-p'>", $pee);
	$pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);
	$pee = preg_replace('!<p class="auto-p">\s*(</?' . $allblocks . '[^>]*>)!', "$1", $pee);
	$pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee);
	if ($br) {
		$pee = preg_replace_callback('/<(script|style).*?<\/\\1>/s', '_autop_newline_preservation_helper', $pee);
		$pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee); // optionally make line breaks
		$pee = str_replace('<WPPreserveNewline />', "\n", $pee);
	}
	$pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*<br />!', "$1", $pee);
	$pee = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $pee);
	if (strpos($pee, '<pre') !== false)
		$pee = preg_replace_callback('!(<pre[^>]*>)(.*?)</pre>!is', 'clean_pre', $pee );
	$pee = preg_replace( "|\n</p>$|", '</p>', $pee );
	return $pee;
}

include('content-editable-ajax.php');
include('content-editable-admin.php');
?>