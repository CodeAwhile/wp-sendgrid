<?php

class WP_SendGrid_Settings {

	// Unique identifier for the settings page
	const SETTINGS_PAGE_SLUG = 'wp-sendgrid-settings';
	const SETTINGS_SECTION_ID = 'wp-sendgrid-account-settings';

	// Where WP SendGrid settings are stored
	const SETTINGS_OPTION_NAME = 'wp_sendgrid_options';

	// Constants to hold the API access options
	const API_REST = 'rest';
	const API_SMTP = 'smtp';

	private static $settings;

	public static function start() {
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'current_screen', array( __CLASS__, 'queue_resources' ) );
		add_action( 'wp_ajax_wp_sendgrid_check_settings', array( __CLASS__, 'ajax_check_settings' ) );
	}

	public static function register_settings() {
		// Register settings and sections
		register_setting( self::SETTINGS_PAGE_SLUG, self::SETTINGS_OPTION_NAME, array( __CLASS__, 'validate_settings' ) );
		add_settings_section( self::SETTINGS_SECTION_ID, __( 'Account Settings' ), array( __CLASS__, 'show_settings_section_description' ), self::SETTINGS_PAGE_SLUG );

		// Username/Password
		self::add_settings_field( 'username', __( 'Your SendGrid Username' ), 'text' );
		self::add_settings_field( 'password', __( 'Your SendGrid Password' ), 'password' );
		self::add_settings_field( 'api', __( 'Send Emails With' ), 'select', array(
			'description' => __( 'You shouldn\'t need to change this unless the default doesn\'t work on your server' ),
			'options' => array( self::API_REST => __( 'REST' ), self::API_SMTP => __( 'SMTP' ) )
		) );
		self::add_settings_field( 'secure', __( 'Secure Connection' ), 'checkbox', array(
			'description' => ' Make sure you have the SSL extension for PHP installed before enabling.',
			'label' => 'Use a secure connection (recommended).'
		) );
		//add_settings_field( self::SETTINGS_SECTION_ID . '-username', 'Your SendGrid Username', array( __CLASS__, 'show_settings_field' ), self::SETTINGS_PAGE_SLUG, self::SETTINGS_SECTION_ID );
	}

	public static function register_menu() {
		add_options_page( __( 'SendGrid Settings' ), __( 'SendGrid Settings' ),
			'manage_options', self::SETTINGS_PAGE_SLUG, array( __CLASS__, 'show_settings_page' ) );
	}

	public static function queue_resources($screen) {
		if ( 'settings_page_' . self::SETTINGS_PAGE_SLUG == $screen->base ) {
			wp_enqueue_script( 'wp-sendgrid', WP_SendGrid::plugin_url( 'resources/wp-sendgrid.js' ), array( 'jquery' ) );
			wp_enqueue_style( 'wp-sendgrid', WP_SendGrid::plugin_url( 'resources/wp-sendgrid.css' ) );
		}
	}

	public static function ajax_check_settings() {
		$user_id = get_current_user_id();
		$user = get_userdata( $user_id );
		if ( wp_mail( $user->user_email, __( 'SendGrid Test' ), __( 'If you\'re reading this, it looks like your SendGrid settings are correct' ) ) ) {
			wp_send_json( array( 'success' => 'Test email sent. If it doesn\'t show up, double check your settings.' ) );
		} else {
			wp_send_json( array( 'error' => 'There was a problem sending the test email. Double check your settings.' ) );
		}
	}

	public static function show_settings_page() {
		?>
		<div class="wrap">
			<h2><?php _e( 'WP SendGrid Settings' ); ?></h2>
			<form action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="POST">
				<?php do_action( 'wp_sendgrid_settings_form_begin' ); ?>
				<?php settings_fields( self::SETTINGS_PAGE_SLUG ); ?>
				<?php do_settings_sections( self::SETTINGS_PAGE_SLUG ); ?>
				<?php do_action( 'wp_sendgrid_settings_before_submit_button'); ?>
				<?php submit_button(); ?>
				<?php do_action( 'wp_sendgrid_settings_after_submit_button' ); ?>
				<?php do_action( 'wp_sendgrid_settings_form_end' ); ?>
			</form>
		</div>
		<?php
	}

	public static function show_settings_section_description() {
		WP_SendGrid::load_view( 'settings-section-description.php' );
	}

	public static function add_settings_field( $id, $label, $type, $args = array() ) {
		$default_args = array(
			'id' => $id,
			'description' => '',
			'type' => $type,
		);
		$args = array_merge( $default_args, $args );

		$callback = isset( $args['callback'] ) ? $args['callback'] : array( __CLASS__, "show_{$type}_field" );

		add_settings_field( self::SETTINGS_SECTION_ID . '-' . $id, $label, $callback, self::SETTINGS_PAGE_SLUG, self::SETTINGS_SECTION_ID, $args );
	}

	public static function load_field_view( $view, $args ) {
		$args['settings'] = self::get_settings();
		$args['value'] = $args['settings'][$args['id']];
		WP_SendGrid::load_view( $view, $args );
	}

	public static function show_input_field( $type, $args ) {
		$args['type'] = $type;
		self::load_field_view( 'input-field.php', $args );
	}

	public static function show_text_field( $args ) {
		self::show_input_field( 'text', $args );
	}

	public static function show_password_field( $args ) {
		self::show_input_field( 'password', $args );
	}

	public static function show_select_field( $args ) {
		self::load_field_view( 'select-field.php', $args );
	}

	public static function show_checkbox_field( $args ) {
		self::load_field_view( 'checkbox-field.php', $args );
	}

	public static function get_default_settings() {
		$defaults = array(
			'username'	=> '',
			'password'	=> '',
			'api'		=> self::API_REST,
			'secure'	=> false
		);
		return apply_filters( 'wp_sendgrid_default_settings', $defaults );
	}

	public static function validate_settings( $settings ) {
		add_settings_error( 'general', 'wp_sendgrid_settings_updated',
			__( 'SendGrid options updated' ) .
			' <input type="button" class="button" id="wp-sendgrid-test-settings" value="' .
			esc_attr( __( 'Send Test Email' ) ) . '" /><span class="spinner"></span>' .
			' <span id="wp-sendgrid-test-settings-response"></span>', 'updated' );
		$settings = apply_filters( 'wp_sendgrid_validate_settings', $settings );
		return $settings;
	}

	public static function get_settings() {
		if ( isset( self::$settings ) ) {
			return self::$settings;
		}
		$settings = get_option( self::SETTINGS_OPTION_NAME, self::get_default_settings() );
		self::$settings = apply_filters( 'wp_sendgrid_get_settings', $settings );
		return self::$settings;
	}
}

WP_SendGrid_Settings::start();
