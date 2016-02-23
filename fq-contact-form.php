<?php
/*
Plugin Name: FQ Contact Form
Plugin URI: http://www.figoliquinn.com/
Description: Easy way to add a contact form
Version: 0.9
Author: Figoli Quinn
Author URI: http://www.figoliquinn.com/
License: GPL
Copyright: Figoli Quinn
*/
defined( 'ABSPATH' ) or die( 'No access!' );










function fq_contact_form_activate() {
	
	add_option( 'fq_contact_form_activated', time() );
	fq_contact_form_init();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__ , 'fq_contact_form_activate' );




function fq_contact_form_deactivate() {

    delete_option( 'fq_contact_form_activated' );
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__ , 'fq_contact_form_deactivate' );





function fq_contact_form_init() {

	if ( class_exists( 'BFIGitHubPluginUpdater' ) ) {
		
		// Check for updates at GitHub
		// wp_die('oops!');
		$update = new BFIGitHubPluginUpdater( __FILE__ , 'figoliquinn', 'fq-contact-form' , '2d751809306a7e660989b331a3fa2a4d3d25631e' );
		#wp_die(print_r($update,true));
	}

	if( class_exists('FQ_Custom_Post_Type') ) {

		$contacts = new FQ_Custom_Post_Type('contact');
		$contacts->register();
	}


	if( is_admin() && class_exists('FQ_Settings') ) {

		$settings = new FQ_Settings();
		$settings->parent_slug	= 'edit.php?post_type=contact';
		$settings->menu_slug	= 'contact-form-settings';
		$settings->menu_title	= 'Contact Form Settings';
		$settings->page_title	= 'Contact Form Settings';
		$settings->page_intro	= 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin dapibus pulvinar lacus, id pharetra ipsum ultricies quis. Nullam a placerat dui. In turpis turpis, ultricies vel sodales pulvinar, rhoncus id sapien. Aenean egestas ante quis libero vestibulum porta. Sed faucibus id nibh ac molestie. Sed blandit urna a molestie ultricies. Duis scelerisque varius enim, a dapibus sem aliquet eu. Ut in turpis sed neque facilisis vulputate eu id ex. Nam gravida tempus lectus quis elementum.';
		$settings->settings	= array(
			array(
				'label' => 'Send To',
				'name' => 'contact-form-send-to',
				'type' => 'text', // select, radio, checkbox, textarea, upload, OR text
				'class' => 'regular-text', // large-text, regular-text
				'value' => '', // default value
				'description' => 'Enter a comma-seperated list of email addresses to send contact form submissions.',
				'options' => array("Small","Medium","Large"),
				'rows' => 5,
			),
			array(
				'label' => 'Reply To',
				'name' => 'contact-form-reply-to',
				'type' => 'text', // select, radio, checkbox, textarea, upload, OR text
				'class' => 'regular-text', // large-text, regular-text
				'value' => '', // default value
				'description' => 'Enter a single email address for contact form submissions to reply to.',
				'options' => array("Small","Medium","Large"),
				'rows' => 5,
			),
			array(
				'label' => 'Form Elements',
				'name' => 'contact-form-show-elements',
				'type' => 'checkbox', // select, radio, checkbox, textarea, upload, OR text
				'description' => 'This is a description',
				'options' => array(
					"your_name"=>"Name",
					"your_email"=>"Email",
					"your_phone"=>"Phone",
					"your_subject"=>"Subject",
					"your_message"=>"Message"
				),
				'value' => array(),// default value
				'rows' => 5,
			),
			array(
				'label' => 'Reply Subject',
				'name' => 'contact-form-reply-subject',
				'type' => 'text', // select, radio, checkbox, textarea, upload, OR text
				'value' => 'You have recieved an email from your website.', // default value
				'description' => 'This is the subject of the email you recive (unless you allow the user to enter their own subject.)',
			),
			array(
				'label' => 'Reply Message',
				'name' => 'contact-form-reply-message',
				'type' => 'textarea', // select, radio, checkbox, textarea, upload, OR text
				'value' => '', // default value
				'description' => '
				This is the email you will recieve after someone has submitted the form. 
				You can use the following variables that will be replaced with actual values.
				{name}, {email}, {phone}, {subject}, {message}.
				',
				'options' => array("Small","Medium","Large"),
				'rows' => 10,
			),
			array(
				'label' => 'Auto-Reply Subject',
				'name' => 'contact-form-auto-reply-subject',
				'type' => 'text', // select, radio, checkbox, textarea, upload, OR text
				'value' => 'Thanks for your message!', // default value
				'description' => 'This is the subject of the email the sender will receive.',
			),
			array(
				'label' => 'Auto-Reply Messsage',
				'name' => 'contact-form-auto-reply-message',
				#'group' => 'setting-group' ,
				'type' => 'textarea', // select, radio, checkbox, textarea, upload, OR text
				'value' => '', // default value
				'description' => '
				This is the email that will be sent to the sender after they have submitted the form. 
				You can use the following variables that will be replaced with actual values.
				{name}, {email}, {phone}, {subject}, {message}.
				',
				'options' => array("Small","Medium","Large"),
				'rows' => 10,
			),
		);

	}


}
add_action( 'init', 'fq_contact_form_init' );




/* This adds a link to the settings page from the plugins page */
function fq_contact_form_plugin_actions( $actions, $plugin_file, $plugin_data, $context ) {

	array_unshift($actions, "<a href=\"".admin_url('edit.php?post_type=contact&page=contact-form-settings')."\">".__("Settings")."</a>");
	return $actions;
}
add_filter("plugin_action_links_".plugin_basename(__FILE__), "fq_contact_form_plugin_actions", 10, 4);





function fq_contact_form_required_plugin_check() {

    if ( is_admin() && current_user_can( 'activate_plugins' ) && !class_exists( 'BFIGitHubPluginUpdater' ) ) {

        add_action( 'admin_notices', 'fq_cf_update_notice' );
        deactivate_plugins( plugin_basename( __FILE__ ) ); 
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
    if ( is_admin() && current_user_can( 'activate_plugins' ) && !class_exists( 'FQ_Custom_Post_Type' ) ) {

        add_action( 'admin_notices', 'fq_cf_cpt_notice' );
        deactivate_plugins( plugin_basename( __FILE__ ) ); 
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
    if ( is_admin() && current_user_can( 'activate_plugins' ) && !class_exists( 'FQ_Settings' ) ) {

        add_action( 'admin_notices', 'fq_cf_st_notice' );
        deactivate_plugins( plugin_basename( __FILE__ ) ); 
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
    if ( is_admin() && current_user_can( 'activate_plugins' ) && !class_exists( 'FQ_Form_Builder' ) ) {

        add_action( 'admin_notices', 'fq_cf_fb_notice' );
        deactivate_plugins( plugin_basename( __FILE__ ) ); 
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }

}
function fq_cf_cpt_notice(){

	echo '
	<div class="error">
		<p>
			Sorry, but FQ Contact Form requires the 
			<a target="_blank" href="https://github.com/figoliquinn/fq_custom_post_types">FQ Custom Post Types plugin</a> to be installed and active.
		</p>
	</div>
	';

}
function fq_cf_st_notice(){
	
	echo '
	<div class="error">
		<p>
			Sorry, but FQ Contact Form requires the 
			<a target="_blank" href="https://github.com/figoliquinn/fq_settings">FQ Settings plugin</a> to be installed and active.
		</p>
	</div>
	';
}
function fq_cf_fb_notice(){
	
	echo '
	<div class="error">
		<p>
			Sorry, but FQ Contact Form requires the 
			<a target="_blank" href="https://github.com/figoliquinn/fq_form_builder">FQ Form Builder plugin</a> to be installed and active.
		</p>
	</div>
	';
}
function fq_cf_update_notice(){

	echo '
	<div class="error">
		<p>
			Sorry, but FQ Contact Form requires the 
			<a target="_blank" href="https://github.com/figoliquinn/fq_updater_check">FQ Updater Check plugin</a> to be installed and active.
		</p>
	</div>
	';
}
add_action( 'admin_init', 'fq_contact_form_required_plugin_check' );





function fq_contact_custom_column_heads($defaults) {
	
	unset($defaults['author']);
	unset($defaults['comments']);
	unset($defaults['date']);
	$defaults['title'] = 'From';
	$defaults['message'] = 'Message';
	return $defaults;
}
function fq_contact_custom_content($column_name, $post_ID) {

    if ($column_name == 'message') {
		echo "Sent ".time2str(get_the_date('U',$post_ID))."<hr>";
		echo wpautop(get_the_content($post_ID));
    }
}
add_filter('manage_contact_posts_columns', 'fq_contact_custom_column_heads');
add_action('manage_contact_posts_custom_column', 'fq_contact_custom_content', 10, 2);





function fq_contact_form_shortcode( $atts = array() , $content = '' , $tag = '' ){


	extract(shortcode_atts( array(
		'send_to' => get_option('contact-form-send-to'),
		'reply_to' => get_option('contact-form-reply-to'),
		'auto_reply_message' => get_option('contact-form-auto-reply-message'),
		'reply_message' => get_option('contact-form-reply-message'),
		'show_elements' => get_option('contact-form-show-elements'),
	), $atts ));


	if( !class_exists('FQ_Form_Builder') ) return;
	
	$form = new FQ_Form_Builder();
	$form->form['action'] = get_permalink().'#contact';
	$form->send_to = $send_to;

	$form->elements = array();

	if(in_array('your_name',$show_elements)){
		$form->elements[] = array(
			'template' => 'text',
			'type' => 'text',
			'label' => 'Your Name',
			'name' => 'your_name',
			'id' => 'your_name',
			'class' => '',
			'value' => '',
			'placeholder' => 'Your Name',
			'required' => true,
		);
	}

	if(in_array('your_email',$show_elements)){
		$form->elements[] = array(
			'type' => 'text',
			'label' => 'Your Email',
			'name' => 'your_email',
			'id' => 'your_email',
			'placeholder' => 'Your Email',
			'required' => true,
		);
	}

	if(in_array('your_phone',$show_elements)){
		$form->elements[] = array(
			'type' => 'text',
			'label' => 'Your Phone',
			'name' => 'your_phone',
			'id' => 'your_phone',
			'placeholder' => 'Your Phone',
			'required' => false,
		);
	}

	if(in_array('your_subject',$show_elements)){
		$form->elements[] = array(
			'type' => 'text',
			'label' => 'Your Subject',
			'name' => 'your_subject',
			'id' => 'your_subject',
			'placeholder' => 'Your Subject',
			'required' => false,
		);
	}

	if(in_array('your_message',$show_elements)){
		$form->elements[] = array(
			'type' => 'textarea',
			'label' => 'Your Message',
			'name' => 'your_message',
			'id' => 'your_message',
			'placeholder' => 'Your Message',
			'rows' => '5',
			'required' => true,
		);
	}

	$form->elements[] = array(
		'type' => 'submit',
		'label' => 'Submit',
		'name' => 'submit_me',
		'id' => 'submit_me',
	);

	return $form->display();


}
add_shortcode( 'fq_contact_form' , 'fq_contact_form_shortcode' );





function time2str($ts) {

    if(!ctype_digit($ts))
        $ts = strtotime($ts);

    $diff = time() - $ts;
    if($diff == 0)
        return 'now';
    elseif($diff > 0)
    {
        $day_diff = floor($diff / 86400);
        if($day_diff == 0)
        {
            if($diff < 60) return 'just now';
            if($diff < 120) return '1 minute ago';
            if($diff < 3600) return floor($diff / 60) . ' minutes ago';
            if($diff < 7200) return '1 hour ago';
            if($diff < 86400) return floor($diff / 3600) . ' hours ago';
        }
        if($day_diff == 1) return 'Yesterday';
        if($day_diff < 7) return $day_diff . ' days ago';
        if($day_diff < 31) return ceil($day_diff / 7) . ' weeks ago';
        if($day_diff < 60) return 'last month';
        return date('F Y', $ts);
    }
    else
    {
        $diff = abs($diff);
        $day_diff = floor($diff / 86400);
        if($day_diff == 0)
        {
            if($diff < 120) return 'in a minute';
            if($diff < 3600) return 'in ' . floor($diff / 60) . ' minutes';
            if($diff < 7200) return 'in an hour';
            if($diff < 86400) return 'in ' . floor($diff / 3600) . ' hours';
        }
        if($day_diff == 1) return 'Tomorrow';
        if($day_diff < 4) return date('l', $ts);
        if($day_diff < 7 + (7 - date('w'))) return 'next week';
        if(ceil($day_diff / 7) < 4) return 'in ' . ceil($day_diff / 7) . ' weeks';
        if(date('n', $ts) == date('n') + 1) return 'next month';
        return date('F Y', $ts);
    }
}





