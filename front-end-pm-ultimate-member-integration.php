<?php
/*
Plugin Name: Front End PM - Ultimate Member Integration
Plugin URI: https://www.shamimsplugins.com/contact-us/
Description: Front End PM extension to integrate with Ultimate Member
Version: 1.1
Author: Shamim
Author URI: https://www.shamimsplugins.com/contact-us/
Text Domain: front-end-pm-ultimate-member-integration
License: GPLv2 or later
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

register_activation_hook(__FILE__ , 'fep_um_plugin_activate' );

function fep_um_plugin_activate(){
		if( function_exists( 'um_get_option' )
		&& function_exists( 'fep_update_option' )
		&& um_get_option('core_user') ){
			fep_update_option( 'page_id', um_get_option('core_user') );
		}
}

class Front_End_Pm_UM_Integration {

	private static $instance;

	private function __construct() {
		if( ! function_exists( 'fep_get_option' ) || ! function_exists( 'um_profile_id' ) ) {
			// Display notices to admins
			add_action( 'admin_notices', array( $this, 'notices' ) );
			return;
		}
		//$this->constants();
		//$this->includes();
		$this->actions();
		$this->filters();

	}

	public static function init()
        {
            if(!self::$instance instanceof self) {
                self::$instance = new self;
            }
            return self::$instance;
        }

	private function constants()
    	{
    	}

	private function includes()
    	{
    	}

	private function actions()
    	{
				if( is_user_logged_in() ){
					add_action('um_profile_content_fep_um_message_tab_default', array( $this, 'content' ) );

					//Account page
					add_action('um_account_tab__fep_um_message_tab', array( $this, 'account_tab_hook' ) );
				}
    	}

	private function filters()
    	{
    		if( is_user_logged_in() ){
					add_filter( 'um_profile_tabs', array( $this, 'tab' ), 1000 );
					add_filter( 'fep_query_url_without_esc_filter', array( $this, 'url' ), 99, 2 );

					//Account page
					add_filter('um_account_page_default_tabs_hook', array( $this, 'account_tab' ), 100 );
					add_filter('um_account_content_hook_fep_um_message_tab', array( $this, 'account_content' ) );
				}
    	}

	function content( $args ) {
		if( get_current_user_id() == um_profile_id() ){
			?><style type="text/css">
			input#fep_mr_to, input#blocked_users{display: none !important;}
			</style>
			<?php
			echo do_shortcode('[front-end-pm]');
		} else {
			echo do_shortcode('[fep_shortcode_new_message_form to="{um-current-author}" subject="" heading=""]');
		}
	}

	function url( $url, $args ){
		$args['profiletab'] = 'fep_um_message_tab';
		return add_query_arg( $args, um_user_profile_url());
	}

	function tab( $tabs ) {

		$tabs['fep_um_message_tab'] = array(
			'name' => __('Message', 'front-end-pm-ultimate-member-integration'),
			'icon' => 'um-icon-email',
		);

		return $tabs;

	}

	//Account page
	function account_tab( $tabs ) {
			$tabs[800]['fep_um_message_tab']['icon'] = 'um-icon-email';
			$tabs[800]['fep_um_message_tab']['title'] = __('Message', 'front-end-pm-ultimate-member-integration');
			$tabs[800]['fep_um_message_tab']['custom'] = true;
			return $tabs;
	}

	function account_tab_hook( $info ) {
			global $ultimatemember;
			extract( $info );

			$output = $ultimatemember->account->get_tab_output('fep_um_message_tab');
			if ( $output ) {
				echo '<div class="um-account-heading uimob340-hide uimob500-hide"><i class="'. $icon .'"></i>' .$title.'</div>';
				echo $output;
			}
	}

	function account_content( $output ){

			$output = '<div class="um-field">';
			$output .= '<a class="um-button" href="' . esc_url( add_query_arg( 'profiletab', 'fep_um_message_tab', um_user_profile_url()) ) .'">' . __('Message', 'front-end-pm-ultimate-member-integration') . '</a>';
			$output .= '</div>';

			return $output;
	}

	public function notices() {
		echo '<div class="error"><p>'. __( 'Front End PM and Ultimate Member must be activated to use Front End PM UM Integration extension.', 'front-end-pm-ultimate-member-integration' ). '</p></div>';
	}

} //END Class

add_action( 'init', array( 'Front_End_Pm_UM_Integration', 'init' ) );
