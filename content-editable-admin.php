<?php

	require_once(ABSPATH . '/wp-admin/includes/plugin.php');
	require_once(ABSPATH . WPINC . '/pluggable.php');

	add_action('admin_menu', 'upstatement_ce_add_page');
	
	function upstatement_ce_add_page(){
		add_options_page('Click to Edit Options', 'Click to Edit', 'manage_options', 'upstatement_ce', 'upstatement_ce_options_page');
	}
	
	function upstatement_ce_options_page(){
		echo '<div class="wrap">';
		echo screen_icon();
		echo '<h2><strong>Click to Edit</strong> Options</h2>';
		echo '<form action="options-general.php?page=upstatement_ce" method="post">';
		settings_fields('upstatement_ce_options');
		do_settings_sections('upstatement_ce');
		echo '<input type="submit" name="Submit" value="Save Changes" class="button-primary"/>';
		echo '</form></div>';
	}
	
	add_action('admin_init', 'upstatement_ce_admin_init');
	
	function upstatement_ce_admin_init(){
		register_setting('WPContentEditable_options', 'upstatement_ce_options', 'upstatement_ce_validate_options');
	
		add_settings_section('upstatement_ce_main', '', 'upstatement_ce_section_text', 'upstatement_ce');
	
		add_settings_field('upstatement_ce_text_string', 'Auto Editable Mode', 'upstatement_ce_setting_input', 'upstatement_ce', 'upstatement_ce_main');


	
	}
	
	function upstatement_ce_options(){
		
	}
	
	function upstatement_ce_section_text(){
		
	}

	function upstatmeent_ce_setting_input_auto_edit($auto){
		$rt = '<fieldset>';
		$rt .=  '<input type="radio" name="upstatement_ce_options[auto_edit]" value="0" id="auto_edit_mode_off"';
		if (!$auto){
			$rt .= ' checked ';
		}
		$rt .= '><label for="auto_edit_mode_off"/>'."Don't mess with my templates! Let me put content editable where I manually place the calls".'</label>';
		$rt .= '<br>';
		$rt .= '<input type="radio" name="upstatement_ce_options[auto_edit]" value="1" id="auto_edit_mode_on"';
		if ($auto){
			$rt .= ' checked ';
		}
		$rt .= '><label for="auto_edit_mode_off"/>'."Make it easy for me! Adjust headline and body output so authors can automatically edit in place".'</label>';
		$rt .= '<p class="description"><strong>Auto Editable Mode</strong> will make the_title() and the_content() automatically editable to authors</p>';
		$rt .= '</fieldset>';
		return $rt;
	}

	function upstatement_ce_setting_input_admin_bar($no_admin_button){
		$rt = '<fieldset>';
		$rt .=  '<input type="radio" name="upstatement_ce_options[no_admin_button]" value="0" id="no_admin_button"';
		if (!$no_admin_button){
			$rt .= ' checked ';
		}
		$rt .= '><label for="no_admin_button"/>'."To use, users click in the Admin bar and select 'Edit Text'".'</label>';
		$rt .= '<br>';
		$rt .= '<input type="radio" name="upstatement_ce_options[no_admin_button]" value="1" id="auto_edit_mode_on"';
		if ($no_admin_button){
			$rt .= ' checked ';
		}
		$rt .= '><label for="no_admin_button"/>'."I dont want them to use the admin bar, WPContentEditable will be triggered when someone clicks into an editable field".'</label>';
		$rt .= '</fieldset>';
		return $rt;
	}
	
	function upstatement_ce_setting_input(){
		if ($_POST['upstatement_ce_options']){
			$options = get_option('upstatement_ce_options');
			$final = array_merge($options, $_POST['upstatement_ce_options']);
			update_option('upstatement_ce_options', $final);
		}
		$options = get_option('upstatement_ce_options');
		$auto = $options['auto_edit'];
		echo upstatmeent_ce_setting_input_auto_edit($auto);
		$no_admin_button = $options['no_admin_button'];
		echo upstatement_ce_setting_input_admin_bar($no_admin_button);
		
	}
	
	function upstatement_ce_validate_options($input){
		return $input;
	}

	function ce_add_admin_bar_control(){
		$options = get_option('upstatement_ce_options');
		$no_admin_button = $options['no_admin_button'];
		global $wp_admin_bar;
		if (!is_super_admin() || !is_admin_bar_showing() || is_admin()){
			return;
		}
		if ($no_admin_button){
			echo '<script type="text/javascript"> var ceAutoEditNoButton = true; </script>';
		} else {
			$wp_admin_bar->add_node(array(
				'id' => 'content_editable',
				'title' => 'Edit Text',
				'href' => '#trigger-content-editable',
				'meta' => array(
					'class' => 'trigger-content-editable'
				)
			));
		}
	}

	add_action('admin_bar_menu', 'ce_add_admin_bar_control', 999);