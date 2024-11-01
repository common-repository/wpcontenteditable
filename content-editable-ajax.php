<?php
	add_action('wp_ajax_ce_update_custom', 'ce_update_custom');
	add_action('wp_ajax_ce_update_title', 'ce_update_title');
	add_action('wp_ajax_ce_update_content', 'ce_update_content');
	
	function ce_update_custom(){
		$pid = $_POST['pid'];
		$content = $_POST['content'];
		$key = $_POST['key'];
		
		ce_security_check($pid);
		
		return update_post_meta($pid, $key, $content);
	}
	
	function ce_update_title(){
		$pid = $_POST['pid'];
		$title = $_POST['title'];
		
		ce_security_check($pid);
		
		$post = array();
		$post['ID'] = $pid;
		$post['post_title'] = $title;
		return wp_update_post($post);
	}
	
	function ce_update_content(){
		$pid = $_POST['pid'];
		$content = $_POST['content'];
		
		ce_security_check($pid);
		
		$post = array();
		$post['ID'] = $pid;
		$post['post_content'] = $content;
		return wp_update_post($post);
	}
	
	function ce_security_check($pid){
		$response = new WP_Ajax_Response;
		//echo wp_create_nonce("content-editable-nonce");
		check_ajax_referer('content-editable-nonce', 'security');
		if (!ce_can_edit($pid)){
			die('You do not have permission to edit this post');	
		}
		return $response;
	}

?>