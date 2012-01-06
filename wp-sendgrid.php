<?php

/*
 * Plugin Name: WP SendGrid
 * Description: SendGrid integration for WordPress
 * Version: 1.0.1
 * Plugin URI: http://www.itsananderson.com/plugins/wp-sendgrid/
 * Author: Will Anderson
 * Author URI: http://www.itsananderson.com/
 * License: GPLv2
 */

class WP_SendGrid {

	const OPTIONS_KEY = 'wp_sendgrid_options';
	const API_REST = 'rest';
	const API_SMTP = 'smtp';

	public static $defined_mail;

	public static function start() {
		$options = self::get_options();
		if ( self::API_REST == $options['api'] && !function_exists( 'wp_mail' ) ) {
				include( plugin_dir_path( __FILE__ ) . 'includes/wp-mail.php' );
		} else {
			add_action( 'phpmailer_init', array( __CLASS__, 'configure_smtp' ) );
		}
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'plugin_action_links', array( __CLASS__, 'plugin_action_links' ), 10, 2 );
	}

	function plugin_action_links( $links, $file ) {
		if ( $file != plugin_basename( __FILE__ ))
			return $links;

		$settings_link = '<a href="options-general.php?page=' . plugin_basename( __FILE__ ) . '">' . __( 'Settings', 'wp_mail_smtp' ) . '</a>';

		array_unshift( $links, $settings_link );

		return $links;
	}

	public static function configure_smtp( &$phpmailer ) {
		$options = self::get_options();

		$phpmailer->Mailer = 'smtp';
		$phpmailer->SMTPSecure = $options['secure'] ? 'ssl' : 'none';
		$phpmailer->Host = 'smtp.sendgrid.net';
		$phpmailer->Port = $options['secure'] ? 465 : 587;
		$phpmailer->SMTPAuth = true;
		$phpmailer->Username = $options['username'];
		$phpmailer->Password = $options['password'];
	}

	public static function get_options() {
		return get_option( self::OPTIONS_KEY, array(
			'username'	=> '',
			'password'	=> '',
			'api'		=> self::API_REST,
			'secure'	=> false
		) );
	}

	public static function add_menu() {
		add_options_page( __( 'SendGrid Settings', 'wp-sendgrid' ), __( 'SendGrid Settings', 'wp-sendgrid' ), 'manage_options', __FILE__, array( __CLASS__, 'show_settings_page' ) );
	}

	public static function show_settings_page() {
		$messages = array();
		if ( isset( $_POST['submit'] ) ) {
			$nonce = $_POST['_wpnonce'];
			if ( !wp_verify_nonce( $nonce, 'update-sendgrid-settings' ) ) {
				return;
			}
			$username = $_POST['username'];
			$password = $_POST['password'];
			$api = self::API_REST == $_POST['api'] ? self::API_REST : self::API_SMTP;
			$secure = isset( $_POST['secure'] );

			$options = array(
				'username'	=> $username,
				'password'	=> $password,
				'api'		=> $api,
				'secure'	=> $secure
			);

			update_option( self::OPTIONS_KEY, $options );
			$messages[] = __( 'SendGrid options updated ', 'wp-sendgrid' ) . '<input type="submit" name="test" class="button" value="' . __( 'Send Test Email', 'wp-sendgrid' ) . '" />';
		}

		if ( isset( $_POST['test'] ) ) {
			$nonce = $_POST['_wpnonce'];
			if ( !wp_verify_nonce( $nonce, 'update-sendgrid-settings' ) ) {
				return;
			}
			$user_id = get_current_user_id();
			$user = get_userdata( $user_id );
			if ( wp_mail( $user->user_email, __( 'SendGrid Test', 'wp-sendgrid' ), __( 'If you\'re reading this, it looks like your SendGrid settings are correct', 'wp-sendgrid' ) ) ) {
				$messages[] = __( 'Test email sent', 'wp-sendgrid' );
			} else {
				$messages[] = __( 'There was a problem sending the test email. Check your settings.', 'wp-sendgrid' );
			}

		}

		$options = self::get_options();

		extract( $options );

		include plugin_dir_path( __FILE__ ) . 'views/settings.php';
	}
}

WP_SendGrid::start();
