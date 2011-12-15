<div class="wrap">
	<h2><?php _e( 'WP SendGrid Settings', 'wp-sendgrid' ); ?></h2>
	<?php
	if ( !empty( $messages ) ) {


		echo '<div class="updated">';
		echo '<form method="post">';
		echo wp_nonce_field( 'update-sendgrid-settings' );
		foreach ( $messages as $message ) {
			echo "<p>$message</p>";
		}
		echo '</form>';
		echo '</div>';
	} ?>
	<form method="post" autocomplete="off">
		<?php echo wp_nonce_field( 'update-sendgrid-settings' ); ?>
		<p>
			<?php _e( 'You need a SendGrid account to send emails with the SendGrid API.', 'wp-sendgrid' ); ?>
		</p>
		<p>
			<?php printf( __( 'If you don\'t have an account, you can <a href="%s" target="_blank">sign up</a>.', 'wp-sendgrid' ), 'http://sendgrid.tellapal.com/a/clk/3MT72' ); ?>
		</p>
		<table class="form-table">
			<tr>
				<th><label for="username"><?php _e( 'Username', 'wp-sendgrid' ); ?></label></th>
				<td>
					<input type="text" name="username" id="username" value="<?php echo esc_attr( $username ); ?>" />
					<span class="description"><?php _e( 'Your SendGrid username', 'wp-sendgrid' ); ?></span>
				</td>

			</tr>
			<tr>
				<th><label for="password"><?php _e( 'API Key', 'wp-sendgrid' ); ?></label></th>
				<td>
					<input type="password" name="password" id="password" value="<?php echo esc_attr( $password ); ?>" />
					<span class="description"><?php _e( 'Your SendGrid password', 'wp-sendgrid' ); ?></span>
				</td>
			</tr>
			<tr>
				<th><label for="api">Send Emails With</label></th>
				<td>
					<select name="api" id="api">
						<option value="rest" <?php selected( 'rest', $api ); ?>><?php _e( 'REST API', 'wp-sendgrid' ); ?></option>
						<option value="smtp" <?php selected( 'smtp', $api ); ?>><?php _e( 'SMTP', 'wp-sendgrid' ); ?></option>
					</select>
					<span class="description"><?php _e( 'You shouldn\'t need to change this unless the default doesn\'t work on your server' , 'wp-sendgrid' ); ?></span>
				</td>
			</tr>
			<tr>
				<th><?php _e( 'Security', 'wp-sendgrid' ); ?></th>
				<td>
					<input type="checkbox" name="secure" id="secure" <?php checked( $secure ); ?> />
					<label for="secure"><?php _e( 'Use a secure connection (recommended).', 'wp-sendgrid' ); ?></label>
					<span class="description"><?php _e( 'Make sure you have the SSL extension for PHP installed before enabling.', 'wp-sendgrid' ); ?></span>
				</td>
			</tr>
		</table>
		<p>
			<input type="submit" name="submit" class="button-primary" value="Save Settings" />
		</p>
	</form>
</div>