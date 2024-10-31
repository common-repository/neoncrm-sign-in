<?php

/**
 * Adds the plugin to the settings menu
 *
 * @since 1.0.0
 */
add_action( 'admin_menu', 'neonsso_add_admin_menu' );


/**
 * Defines the plugin settings menu options
 *
 * @since 1.0.0
 */
function neonsso_add_admin_menu() { 
	add_options_page( 'NeonCRM Sign-In', 'NeonCRM Sign-In Settings', 'manage_options', 'neonsso', 'neonsso_options_page' );
}

/**
 * Adds the plugin options page
 *
 * @since 1.0.0
 */
add_action( 'admin_init', 'neonsso_settings_init' );

/**
 * Defines the plugin settings fields
 *
 * @since 1.0.0
 */
function neonsso_settings_init() { 

	register_setting( 'neon_sso_settings_group', 'neonsso_settings', 'neonsso_options_validate' );

	add_settings_section(
		'neonsso_neon_sso_settings_group_section', 
		__( '', 'wordpress' ), 
		'neonsso_settings_section_callback', 
		'neon_sso_settings_group'
	);

	add_settings_field( 
		'neonsso_org_id', 
		__( 'Organization ID', 'wordpress' ), 
		'neonsso_org_id_render', 
		'neon_sso_settings_group', 
		'neonsso_neon_sso_settings_group_section' 
	);

	add_settings_field( 
		'neonsso_api_key', 
		__( 'API Key', 'wordpress' ), 
		'neonsso_api_key_render', 
		'neon_sso_settings_group', 
		'neonsso_neon_sso_settings_group_section' 
	);

	add_settings_field( 
		'neonsso_client_id', 
		__( 'OAuth Client ID', 'wordpress' ), 
		'neonsso_client_id_render', 
		'neon_sso_settings_group', 
		'neonsso_neon_sso_settings_group_section' 
	);

	add_settings_field( 
		'neonsso_client_secret', 
		__( 'OAuth Client Secret', 'wordpress' ), 
		'neonsso_client_secret_render', 
		'neon_sso_settings_group', 
		'neonsso_neon_sso_settings_group_section' 
	);
	
	add_settings_field( 
		'neonsso_button_text', 
		__( 'What text should appear on the sign-in button?', 'wordpress' ), 
		'neonsso_button_text_render', 
		'neon_sso_settings_group', 
		'neonsso_neon_sso_settings_group_section' 
	);
	
	add_settings_field( 
		'neonsso_enable_double_logout', 
		__( 'When users log out of WordPress, automatically log them out of NeonCRM?', 'wordpress' ), 
		'neonsso_enable_double_logout_render', 
		'neon_sso_settings_group', 
		'neonsso_neon_sso_settings_group_section' 
	);
	
	add_settings_field( 
		'neonsso_default_role', 
		__( 'Which role should be granted to new users?', 'wordpress' ), 
		'neonsso_default_role_render', 
		'neon_sso_settings_group', 
		'neonsso_neon_sso_settings_group_section' 
	);
	
	add_settings_field( 
		'neonsso_enable_membership', 
		__( 'Should roles be assigned based on NeonCRM membership terms?', 'wordpress' ), 
		'neonsso_enable_membership_render', 
		'neon_sso_settings_group', 
		'neonsso_neon_sso_settings_group_section' 
	);
	
	
}

/**
 * Renders the Organization ID field
 *
 * @since 1.0.0
 */
function neonsso_org_id_render() { 
	$options = get_option( 'neonsso_settings' );
	?>
	<input type='text' class="regular-text code" name='neonsso_settings[neonsso_org_id]' value='<?php echo $options['neonsso_org_id']; ?>'>
	<?php
}

/**
 * Renders the API Key field
 *
 * @since 1.0.0
 */
function neonsso_api_key_render() { 
	$options = get_option( 'neonsso_settings' );
	?>
	<input type='text' class="regular-text code" name='neonsso_settings[neonsso_api_key]' value='<?php echo $options['neonsso_api_key']; ?>'>
	<?php
}

/**
 * Renders the OAuth Client ID field
 *
 * @since 1.0.0
 */
function neonsso_client_id_render() { 
	$options = get_option( 'neonsso_settings' );
	?>
	<input type='text' class="regular-text code" name='neonsso_settings[neonsso_client_id]' value='<?php echo $options['neonsso_client_id']; ?>'>
	<?php
}

/**
 * Renders the OAuth Client Secret field
 *
 * @since 1.0.0
 */
function neonsso_client_secret_render() { 
	$options = get_option( 'neonsso_settings' );
	?>
	<input type='text' class="regular-text code" name='neonsso_settings[neonsso_client_secret]' value='<?php echo $options['neonsso_client_secret']; ?>'>
	<?php
}

/**
 * Renders the Default Role field
 *
 * @since 1.0.0
 */
function neonsso_default_role_render() { 
	$options = get_option( 'neonsso_settings' );
	?>
	<select name='neonsso_settings[neonsso_default_role]'>
		<option value="none">No Access Granted</option>
		<?php wp_dropdown_roles( $options['neonsso_default_role'] ); ?>
	</select>
	<?php
}

/**
 * Renders the Sign-in button text field
 *
 * @since 1.0.0
 */
function neonsso_button_text_render() {
	$options = get_option( 'neonsso_settings' );
	?>
	<input type='text' class="regular-text" name='neonsso_settings[neonsso_button_text]' value='<?php echo $options['neonsso_button_text']; ?>'>
	<?php
}

/**
 * Renders the Enable Membership checkbox field
 *
 * @since 1.0.0
 */
function neonsso_enable_membership_render() { 

	$options = get_option( 'neonsso_settings' );
	?>
	<input type='checkbox' name='neonsso_settings[neonsso_enable_membership]' <?php checked( $options['neonsso_enable_membership'], 1 ); ?> value='1'>
	<?php
}

/**
 * Renders the Enable Double Logout checkbox field
 *
 * @since 1.1.5
 */
function neonsso_enable_double_logout_render() { 

	$options = get_option( 'neonsso_settings' );
	?>
	<input type='checkbox' name='neonsso_settings[neonsso_enable_double_logout]' <?php checked( $options['neonsso_enable_double_logout'], 1 ); ?> value='1'>
	<?php
}

/**
 * Renders the Enable Membership checkbox field
 *
 * @since 1.0.0
 */
function neonsso_settings_section_callback() { 
	echo __( 'Please provide the following credentials to enable the single sign-on.', 'wordpress' );
}

/**
 * Field validation for options page
 *
 * @since 1.0.0
 */
function neonsso_options_validate( $input ) {
	$options = get_option( 'neonsso_settings' );
	
	
	// Required fields
	if ( ! isset ( $input['neonsso_client_id'] ) || empty( $input['neonsso_client_id'] ) ) {
		add_settings_error( 'neonsso_client_id', 'client-id', 'You must provide an OAuth Client ID.', 'error' );
		$options['neonsso_client_id'] = null;
	} else {
		$options['neonsso_client_id'] = sanitize_text_field( $input['neonsso_client_id'] );
	}
	
	if ( ! isset ( $input['neonsso_client_secret'] ) || empty( $input['neonsso_client_secret'] ) ) {
		add_settings_error( 'neonsso_client_secret', 'client-secret', 'You must provide an OAuth Client Secret.', 'error' );
		$options['neonsso_client_secret'] = null;
	} else {
		$options['neonsso_client_secret'] = sanitize_text_field( $input['neonsso_client_secret'] );
	}
	
	if ( ! isset ( $input['neonsso_api_key'] ) || empty( $input['neonsso_api_key'] ) ) {
		add_settings_error( 'neonsso_api_key', 'api-key', 'You must provide an API Key.', 'error' );
		$options['neonsso_api_key'] = null;
	} else {
		$options['neonsso_api_key'] = sanitize_text_field( $input['neonsso_api_key'] );
	}
	
	if ( ! isset ( $input['neonsso_org_id'] ) || empty( $input['neonsso_org_id'] ) ) {
		add_settings_error( 'neonsso_org_id', 'client-id', 'You must provide an Organization ID.', 'error' );
		$options['neonsso_org_id'] = null;
	} else {
		$options['neonsso_org_id'] = sanitize_text_field( $input['neonsso_org_id'] );
	}
	
	if ( ! isset ( $input['neonsso_button_text'] ) || empty( $input['neonsso_button_text'] ) ) {
		$options['neonsso_button_text'] = 'Sign in with NeonCRM';
	} else {
		$options['neonsso_button_text'] = sanitize_text_field( $input['neonsso_button_text'] );
	}

	// Ensures enable_membership option has a value
	if ( ! isset( $input['neonsso_enable_membership'] ) || 1 != $input['neonsso_enable_membership'] ) {
		$options['neonsso_enable_membership'] = 0;
	} else {
		$options['neonsso_enable_membership'] = 1;
	}
	
	// Ensures enable_membership option has a value
	if ( ! isset( $input['neonsso_enable_double_logout'] ) || 1 != $input['neonsso_enable_double_logout'] ) {
		$options['neonsso_enable_double_logout'] = 0;
	} else {
		$options['neonsso_enable_double_logout'] = 1;
	}
	
	// Default role must be from the list of editable roles
	if ( ! isset( $input['neonsso_default_role'] ) || empty( $input['neonsso_default_role'] ) ){
		$options['neonsso_default_role'] = 'none';
	} else {
		$roles = array();
		$editable_roles = get_editable_roles();		
		foreach ( $editable_roles as $role_name => $data ) {
			$roles[] = $role_name;
		}		
		$valid_role = neonsso_is_valid_role( $roles, $input['neonsso_default_role'] );		
		if ( $valid_role ){
			$options['neonsso_default_role'] = $input['neonsso_default_role'];
		} else {
			$options['neonsso_default_role'] = 'none';
		}
	}
	
	// Validation for membership mapping
	if ( isset( $input['neonsso_membership_role'] ) && !empty( $input['neonsso_membership_role'] ) ) {
		$possible_roles = array();
		$editable_roles = get_editable_roles();		
		foreach ( $editable_roles as $role_name => $data ) {
			$possible_roles[] = $role_name;
		}
		foreach( $input['neonsso_membership_role'] as $id => $role ) {		
			$valid_role = neonsso_is_valid_role( $possible_roles, $role );		
			if ( $valid_role ){
				$validated_role = $role;
			} else {
				$validated_role = NEONSSO_DEFAULT_ROLE;
			}			
			$clean_id = intval( $id );
			$options['neonsso_membership_role'][ $clean_id ] = $validated_role;			
		}
	}
	return $options;
}

/**
 * Determines if a role is valid
 *
 * @since 1.0.0
 *
 * @param array $possible_roles An array of roles retrieved from WP
 * @param string $actual_role The role submitted by the user
 * @return boolean
 */
function neonsso_is_valid_role( $possible_roles, $role ) {
	if ( in_array( $role, $possible_roles ) || $role == 'none' ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Retrieves all possible membership terms from Neon.
 *
 * @since 1.0.0
 *
 * @see neonsso_get_membership_terms
 * @param array $terms A list of membership terms from Neon API
 * @return HTML The membership term and role mapping settings table
 */
function neonsso_render_membership_mapping( $terms ) {
	$options = get_option( 'neonsso_settings' );
?>
	<h2>Membership Terms</h2>
	<p>Assign WordPress roles to NeonCRM Members based on their Membership Term. The default role (specified above) will still be assigned to constituents without a membership.</p>
	<p><em>This feature does not work well if your organization assigns multiple concurrent memberships to the same person!</em></p>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th>Term Name</th>
				<th>Term ID</th>
				<th>Role</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($terms as $t): ?>
				<?php $name = $t['termInfo']['name']; ?>
				<?php $id = $t['termInfo']['id']; ?>
				<tr>
					<td><?php echo esc_html( $name ); ?></td>
					<td><?php echo esc_html( $id ); ?></td>
					<td>
						<select name="neonsso_settings[neonsso_membership_role][<?php echo esc_attr( $id ); ?>]">
							<option value="none">No Access Granted</option>
							<?php 
							// Check for existing mapping
							if ( isset( $options['neonsso_membership_role'][$id] ) ) {
								// If found, assign the mapping
								$role_value = $options['neonsso_membership_role'][$id]; 
							} else {
								// Else use default role
								$role_value = NEONSSO_DEFAULT_ROLE;
							}
							?>
							<?php wp_dropdown_roles( $role_value ); ?>
							
						</select>
					</td>
				</tr>
				<?php if ( isset( $t['childTerms'] ) ): ?>
					<?php foreach ($t['childTerms']['idNamePair'] as $child): ?>
					<?php $child_id = $child['id']; ?>
						<tr>
							<td style="padding-left: 30px;"><?php echo esc_html( $child['name'] ); ?> <em>(Child term of <?php echo esc_html( $name ); ?>)</em></td>
							<td><?php echo esc_html( $child['id'] ); ?></td>
							<td>
								<select name="neonsso_settings[neonsso_membership_role][<?php echo esc_attr( $child_id ); ?>]">
									<option value="none">No Access Granted</option>
									<?php 
									// Check for existing mapping
									if ( isset( $options['neonsso_membership_role'][ $child_id ] ) ) {
										// If found, assign the mapping
										$child_role_value = $options['neonsso_membership_role'][ $child_id ]; 
									} else {
										// Else use default role
										$child_role_value = NEONSSO_DEFAULT_ROLE;
									}
									?>
									<?php wp_dropdown_roles( $child_role_value ); ?>
								</select>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			<?php endforeach; ?>
		</tbody>
	</table>

<?php
}

/**
 * Renders options page
 *
 * @since 1.0.0
 */
function neonsso_options_page() { 

	$options = get_option( 'neonsso_settings' );
	?>
		
	<form action='options.php' method='post'>
		
		<h2>NeonCRM Single Sign-On</h2>
		
		<?php
		settings_fields( 'neon_sso_settings_group' );
		do_settings_sections( 'neon_sso_settings_group' );
		
		// Render membership term settings
		if ( NEONSSO_LOADED && NEONSSO_ENABLE_MEMBERSHIP ) {
			$terms = neonsso_get_membership_terms();
			if ( $terms ) {
				neonsso_render_membership_mapping( $terms );
			}
		}
		
		submit_button();
		?>
		
	</form>
	<?php

}

?>