<?php
/*
Plugin Name: NeonCRM Sign-In
Plugin URI: https://wordpress.org/plugins/neoncrm-sign-in
Description: Allows users to sign in to WordPress using their NeonCRM constituent login account.
Author: Colin Pizarek
Version: 1.2.0
Author URI: https://profiles.wordpress.org/colinpizarek/
License: GPL2
*/

/**
 * Include Admin page
 */
require_once('neoncrm-sso-admin-page.php');

/**
 * Include Profile page
 */
require_once('neoncrm-sso-profile-page.php');

/**
 * Include Menu page
 */
require_once('neoncrm-sso-menu-page.php');

// Get plugin settings
$options = get_option( 'neonsso_settings' );

/**
 * Credentials and constants.
 *
 * @since 1.0.0
 * @var string NEONSSO_ORG_ID NeonCRM organizaiton ID.
 * @var string NEONSSO_API_KEY NeonCRM API Key.
 * @var string NEONSSO_CLIENT_ID NeonCRM OAuth Client ID.
 * @var string NEONSSO_CLIENT_SECRET NeonCRM OAuth Client Secret.
 * @var string NEONSSO_DEFAULT_ROLE Default WP role to be assigned to new users.
 * @var string NEONSSO_REDIRECT_URI OAuth redirect URI, WP login page by default.
 * @var boolean NEONSSO_LOADED Flag whether all required settings are in place for the plugin.
 * @var string NEONSSO_BUTTON_TEXT Text string to appear on login page button.
 * @var boolean NEONSSO_ENABLE_MEMBERSHIP Flag whether membership data should be included in plugin logic.
 * 
 * @since 1.0.2
 * @var string NEONSSO_LOGIN_URL The sign-in url.
 *
 */
if ( !empty( $options['neonsso_client_id'] ) && !empty( $options['neonsso_client_secret'] ) && !empty( $options['neonsso_org_id'] ) && !empty( $options['neonsso_api_key'] ) && !empty( $options['neonsso_default_role'] ) ) {
	define("NEONSSO_ORG_ID", 			$options['neonsso_org_id']);
	define("NEONSSO_API_KEY", 			$options['neonsso_api_key']);
	define("NEONSSO_CLIENT_ID", 		$options['neonsso_client_id']);
	define("NEONSSO_CLIENT_SECRET",		$options['neonsso_client_secret']);
	define("NEONSSO_DEFAULT_ROLE",		$options['neonsso_default_role']);
	define("NEONSSO_REDIRECT_URI", 		wp_login_url() );
	define("NEONSSO_LOADED",			true );
	define("NEONSSO_BUTTON_TEXT",		$options['neonsso_button_text']);
	define("NEONSSO_ENABLE_MEMBERSHIP", $options['neonsso_enable_membership'] );
	$the_url = 'https://' . NEONSSO_ORG_ID . '.z2systems.com/np/oauth/auth?response_type=code&client_id=' . urlencode( NEONSSO_CLIENT_ID ) . '&redirect_uri=' . rawurlencode( NEONSSO_REDIRECT_URI );
	define("NEONSSO_LOGIN_URL",         $the_url );
} else {
	define("NEONSSO_LOADED", 			false );
	define("NEONSSO_ENABLE_MEMBERSHIP", false );
}

/**
 * Modifies the login form to include our custom sign-in button 
 *
 * @since 1.0.0
 */
add_action( 'login_form', 'neonsso_add_login_button' );

/**
 * Adds login button to wp-login.php.
 *
 * @since 1.0.0
 *
 * @return HTML output
 */
function neonsso_add_login_button() {
	
	// Ensure all required credentials have been supplied by the user.
	if ( NEONSSO_LOADED != true ) {
		return null;
	}
    ?>

    <p class="neonsso-login-button">
        <a class="button" href="<?php echo esc_url( NEONSSO_LOGIN_URL ); ?>"><?php echo esc_html( NEONSSO_BUTTON_TEXT ); ?></a>
    </p>
	<br>

    <?php
}

/**
 * Login Link Shortcode
 *
 * @since 1.0.2
 */
function neonsso_login_link_shortcode( $atts, $content = null ) {
	if ( !$content ){ 
		$content = NEONSSO_BUTTON_TEXT; 
	}
	$a = shortcode_atts( array(
		'class' => 'button',
	), $atts );
	return '<a href="' . esc_url( NEONSSO_LOGIN_URL ) . '" class="' . esc_attr( $a['class'] ) . '">' . $content . '</a>';
}
add_shortcode( 'neon_sign_in_link', 'neonsso_login_link_shortcode' );

/**
 * Adds OAuth query to the Authenticate filter.
 *
 * @since 1.0.0
 */
add_filter ( 'authenticate', 'neonsso_auth', 10, 3 );

/**
 * Executes OAuth call and retrieves data through Neon API.
 *
 * Handles OAuth SSO. Also includes contact info sync and membership info sync.
 *
 * @since 1.0.0
 *
 * @param $user
 * @param $username
 * @param $password
 * @return $user WordPress User object
 */
function neonsso_auth( $user, $username, $password ) {
	
	// Ensure our constants are loaded
	if ( ! NEONSSO_LOADED ) {
		return null;
	}
	
	// Check for the OAuth Code from NeonCRM
	if ( isset( $_GET['code'] ) ) {
		
		$code = $_GET['code'];
		
		// Send a POST to Neon's OAuth to retrieve ID
		$neon_id = neonsso_oauth_post( $code );
		
		// Debugging
		neonsso_error_log( 'OAuth Neon ID: ', $neon_id );
		
		// Log the user in based on their Neon ID 
		if ( $neon_id ) {
			
			// Search for an existing WP user based on their NeonCRM Account ID
			$user_parameters = array(
				'meta_key'    => 'neon_id', 
				'meta_value'  => $neon_id, 
				'number'      => 1, 
				'count_total' => false,
				'fields'      => 'ID',
			);
			
			// Retrieve user from WP users
			$get_users = get_users( $user_parameters );

			// If user exists, reset role based on settings
			if ( isset( $get_users[0] ) && !empty( $get_users[0] ) ){
							
				// Get their WordPress user ID
				$user_id = $get_users[0];
				
				// Debugging
				neonsso_error_log( 'Existing WordPress User ID: ', $user_id );
				
				// Assume the default role
				$role = NEONSSO_DEFAULT_ROLE;
				
				if ( NEONSSO_ENABLE_MEMBERSHIP ) {
					
					// Check and retrieve the user's current membership			
					$membership_id = neonsso_membership_check( $neon_id );
					
					if ( $membership_id ) {					
						// Retrieve the correct role based on their membership
						$role = neonsso_match_role( $membership_id );	
					}
				}
				
				// Debugging
				neonsso_error_log( 'WordPress Role: ', $role );
				
				// Update the WP user's role
				wp_update_user( array( 'ID' => $user_id, 'role' => $role ) );
		
				// Get the WP user
				$user = get_user_by( 'id', $user_id );
				
				return $user;

			}
			// If no NeonCRM user is found, create one using data from the API
			else {
				// Log in to NeonCRM API
				$login = neonsso_login_neon_api();
				
				// If API login successful, retrieve data for this person
				if ( $login ){
					
					// Retrieve individual account
					$user_data = neonsso_get_individual( $neon_id, $login );

					if ( !isset( $user_data ) && ( 'error_message' != $user_data ) ) {
						// If error, attempt to retrieve organization account
						$user_data = neonsso_get_organization( $neon_id, $login );
					}					

					if ( isset( $user_data ) && ( $user_data != 'error_message' ) ) {

						// Insert the user into WP
						$user_id = wp_insert_user( $user_data );
						
						// Debugging
						neonsso_error_log( 'New WordPress User ID: ', $user_id );
						
						if ( ! is_wp_error( $user_id ) ) {
						
							// Append Neon account ID to WP user account
							update_user_meta( $user_id, 'neon_id', $neon_id );
							
							// Get the WordPress user
							$user = get_user_by( 'id', $user_id );	
							
							// Assume the default role
							$role = NEONSSO_DEFAULT_ROLE;
							
							if ( NEONSSO_ENABLE_MEMBERSHIP ) {	
								// Check and retrieve the user's current membership		
								$membership_id = neonsso_membership_check( $neon_id );
								
								if ( $membership_id ) {							
									// Retrieve the correct role based on their membership
									$role = neonsso_match_role( $membership_id );								
								}
							}
							
							// Debugging
							neonsso_error_log( 'WordPress Role: ', $role );

							// Update their role
							wp_update_user( array( 'ID' => $user_id, 'role' => $role ) );
							
							// Get the WP user
							$user = get_user_by( 'id', $user_id );
							
							return $user; 
						} else if ( is_wp_error( $user_id ) ) {
							// Show WP Error message
							add_filter('login_message', function() use ( $user_id ){
								$error_messages = $user_id->get_error_messages();
								$message_string = '';
								foreach( $error_messages as $key => $message ){
									neonsso_error_log( 'Login message: ', $message );
									$message_string .= '<p class="message">' . $message . '</p>';
								}
								return $message_string;
							} );			
						}
						
						
					}
				} else { 
					// Show the API broken error message
					add_filter( 'login_message', 'neonsso_api_is_broken' );
				}
			}
			
		}		
	}
}

/**
 * Sends POST request to Neon's OAuth.
 *
 * @since 1.0.0
 *
 * @param string $code Neon OAuth code
 * @return integer Neon Account ID
 */
function neonsso_oauth_post( $code ){
	
	// Build array for HTTP request POST parameters 
	$parameters = array();
	$parameters['client_id']     = NEONSSO_CLIENT_ID;
	$parameters['client_secret'] = NEONSSO_CLIENT_SECRET;
	$parameters['redirect_uri']  = NEONSSO_REDIRECT_URI;
	$parameters['code']          = $code; // Get code from URL Parameter
	$parameters['grant_type']    = 'authorization_code'; // required, fixed value
 
	// Convert the parameters array to URLEncoded string
	$parameters = http_build_query($parameters);
 
	$url = 'https://www.z2systems.com/np/oauth/token'; // Always use this URL
	
	// HTTP POST request to NeonCRM's OAuth
	$response = wp_remote_post( $url, array(
		'method'  => 'POST',
		'headers' => array("Content-Type: application/x-www-form-urlencoded"),
		'body'    => $parameters,				
		)
	);
	
	// Decode the JSON response from OAuth
	$token = json_decode( $response['body'], true );
	
	if ( !empty ( $token['access_token'] ) ) {		
		// Save the NeonCRM Account ID
		$neon_id = intval( $token['access_token'] );
		
		return $neon_id;
		
	} else {
		return null;
	}
	
}
/**
 * Logs in to NeonCRM API.
 *
 * @since 1.0.0
 *
 * @return string Neon API Session ID
 */
function neonsso_login_neon_api(){
	
	// Log in to NeonCRM API
	$login_parameters = array();
	$login_parameters['login.apiKey'] = NEONSSO_API_KEY;
	$login_parameters['login.orgid']  = NEONSSO_ORG_ID;

	$login_url = 'https://api.neoncrm.com/neonws/services/api/common/login';
	
	$login_response = wp_remote_post( $login_url, array(
		'method' => 'POST',
		'body'   => $login_parameters,
	));
	
	$login = json_decode( $login_response['body'], true );
	
	// Check for successful login
	if ( 'SUCCESS' == $login['loginResponse']['operationResult'] ){
		return $login['loginResponse']['userSessionId'];
	} else {
		neonsso_error_log( 'Could not log in to NeonCRM API' );
		return null;
	}
}

/**
 * Returns a NeonCRM Individual Account from API.
 *
 * @since 1.0.0
 *
 * @param integer $neon_id Neon Account ID
 * @param string $session_id Neon API Session ID
 * @return array $account_details WP user account details
 */
function neonsso_get_individual( $neon_id, $session_id ){
	
	$account_parameters = array();
	$account_parameters['accountId']     = $neon_id;
	$account_parameters['userSessionId'] = $session_id;
	
	$account_url = 'https://api.neoncrm.com/neonws/services/api/account/retrieveIndividualAccount';
	
	$account_response = wp_remote_post( $account_url, array(
		'method' => 'POST',
		'body'   => $account_parameters,
		)
	);
	
	$account = json_decode($account_response['body'], true);

	if ( 'SUCCESS' == $account['retrieveIndividualAccountResponse']['operationResult'] ){
		
		if ( isset( $account['retrieveIndividualAccountResponse']['individualAccount']['primaryContact']['email1'] ) && is_email( $account['retrieveIndividualAccountResponse']['individualAccount']['primaryContact']['email1'] ) ) {
			
			$account_details = array(
				'first_name' => sanitize_text_field( $account['retrieveIndividualAccountResponse']['individualAccount']['primaryContact']['firstName'] ),
				'last_name'	 => sanitize_text_field( $account['retrieveIndividualAccountResponse']['individualAccount']['primaryContact']['lastName'] ),
				'user_email' => sanitize_email( $account['retrieveIndividualAccountResponse']['individualAccount']['primaryContact']['email1'] ),
				'user_login' => sanitize_text_field( $account['retrieveIndividualAccountResponse']['individualAccount']['login']['username'] ),
				'user_pass'	 => wp_generate_password(),
				'role'       => sanitize_text_field( NEONSSO_DEFAULT_ROLE ),
			);
			
			return $account_details;
			
		} else {
			// Show error - email is required
			neonsso_error_log( 'Account in NeonCRM must have an email address.' );
			add_filter( 'login_message', 'neonsso_ind_email_is_required' );
			
			return 'error_message';
		}
	} else {
		neonsso_error_log( 'Could not retrieve individual account from NeonCRM' );
		return null;
	}
}

/**
 * Returns a NeonCRM Organization Account from API.
 *
 * @since 1.0.0
 *
 * @param integer $neon_id Neon Account ID
 * @param string $session_id Neon API Session ID
 * @return array $account_details WP user account details
 */
function neonsso_get_organization( $neon_id, $session_id ){
	
	$account_parameters = array();
	$account_parameters['accountId']     = $neon_id;
	$account_parameters['userSessionId'] = $session_id;
	
	$org_account_url = 'https://api.neoncrm.com/neonws/services/api/account/retrieveOrganizationAccount';
	
	$account_response = wp_remote_post( $org_account_url, array(
		'method' => 'POST',
		'body'   => $account_parameters,
		)
	);
	
	$account = json_decode($account_response['body'], true);

	if ( 'SUCCESS' == $account['retrieveOrganizationAccountResponse']['operationResult'] ){
	
		if ( isset( $account['retrieveOrganizationAccountResponse']['organizationAccount']['primaryContact']['email1'] ) && is_email( $account['retrieveOrganizationAccountResponse']['organizationAccount']['primaryContact']['email1'] ) ) {
		
			$account_details = array(
				'first_name' => sanitize_text_field( $account['retrieveOrganizationAccountResponse']['organizationAccount']['primaryContact']['firstName'] ),
				'last_name'  => sanitize_text_field( $account['retrieveOrganizationAccountResponse']['organizationAccount']['primaryContact']['lastName'] ),
				'user_email' => sanitize_email( $account['retrieveOrganizationAccountResponse']['organizationAccount']['primaryContact']['email1'] ),
				'user_login' => sanitize_text_field( $account['retrieveOrganizationAccountResponse']['organizationAccount']['login']['username'] ),
				'user_pass'	 => wp_generate_password(),
				'role'       => sanitize_text_field( NEONSSO_DEFAULT_ROLE ),
			);
			
			return $account_details;
		} else {
				// Show error - email is required
				add_filter( 'login_message', 'neonsso_org_email_is_required' );
				
				return 'error_message';
		}
	} else {
		// Show error - API is broken
		neonsso_error_log( 'Could not retrieve organization account from NeonCRM.' );
		add_filter( 'login_message', 'neonsso_api_is_broken' );
		
		return null;
	}
}

/**
 * Retrieves membership history for a Neon account from API.
 *
 * @since 1.0.0
 *
 * @param integer $neon_id Neon Account ID
 * @param string $session_id Neon API Session ID
 * @return array $membership_history Neon membership history
 */
function neonsso_get_membership_history( $neon_id, $session_id ){
	$membership_history_parameters = array();
	$membership_history_parameters['accountId']     = $neon_id;
	$membership_history_parameters['userSessionId'] = $session_id;
	
	$membership_history_url = 'https://api.neoncrm.com/neonws/services/api/membership/listMembershipHistory';
	
	$membership_history_response = wp_remote_post( $membership_history_url, array(
		'method' => 'POST',
		'body'   => $membership_history_parameters,
		'timeout' => 20,
		)
	);

	if ( !is_wp_error( $membership_history_response ) ) {
		$membership_history = json_decode($membership_history_response['body'], true);
	}
	
	// Ensure at least one membership is returned
	if ( isset( $membership_history['listMembershipHistoryResponse']['membershipResults']['membershipResult'][0] ) ) {
		return $membership_history;
	} else {
		neonsso_error_log( 'No membership history' );
		return null;
	}
}

/**
 * Determines which membership is valid.
 *
 * Returns the Neon membership term ID of the valid membership.
 *
 * @since 1.0.0
 *
 * @param integer $neon_id Neon Account ID
 * @return integer $membership_term_id Neon membership term ID
 *
 */
function neonsso_membership_check( $neon_id ) {
	
	if ( NEONSSO_ENABLE_MEMBERSHIP == 1 ) {
		
		// Log in to Neon API
		$session_id = neonsso_login_neon_api();
		
		if ( $session_id ){
				
			// Return membership history for this account
			$membership_history = neonsso_get_membership_history( $neon_id, $session_id );
			
			if ( $membership_history ) {
			
				// Parse membership terms from server response
				$terms = $membership_history['listMembershipHistoryResponse']['membershipResults']['membershipResult'];
				
				$membership_term = array();
				
				// Collect current membership terms into an array
				foreach ( $terms as $term ) {
					
					// Handle lifetime memberships
					if ( $term['termDuration'] == 'LIFE' ){
						$term['termEndDate'] = '2999-01-01T06:00:00.000+0000';
					}
					
					// Parse timestamps and status from server response
					$start        = strtotime( $term['termStartDate'] );
					$end          = strtotime( $term['termEndDate'] );
					$status       = $term['status'];
					$current_time = current_time( 'timestamp' );
					
					// If membership is successful, already started, and not yet ended, add to the terms array					
					if ( $start <= $current_time && $end >= $current_time && 'SUCCEED' == $status ) {
						$membership_term[$end] = $term['membershipTerm']['termInfo']['id'];
					}
					
				}
				
				// Get the membership ID of the term with the highest end date
				if ( !empty( $membership_term ) ){
					$oldest_term = max( array_keys( $membership_term ) );
					
					$membership_term_id = null;
					
					// Set the return variable to the ID of the valid membership term
					$membership_term_id = $membership_term[ $oldest_term ];
					
					neonsso_error_log( 'NeonCRM Membership Term ID: ', $membership_term_id );
					
					return $membership_term_id;
				} else {
					neonsso_error_log( 'Could not find a valid membership term from the membership history' );
					return null;
				}
			} else {
				return null;
			}			
		} else {
			return null;
		}
	} else {
		return null;
	}
	
}

/**
 * Retrieves the WordPress role that corresponds to a Neon membership term.
 *
 * Retrieves the WordPress role that has been mapped to a Neon membership term in the plugin settings.
 *
 * @since 1.0.0
 *
 * @param integer $membership_id Neon membership term ID
 * @return string $role WordPress role
 */
function neonsso_match_role( $membership_id ) {
	
	// Retrieve plugin settings
	$options = get_option('neonsso_settings');
	
	// Check if a membership ID has been mapped to a role in plugin settings
	if ( array_key_exists( $membership_id, $options['neonsso_membership_role'] ) ) {
		
		// If match is found, return the mapped role
		$role = $options['neonsso_membership_role'][ $membership_id ];
		return $role;
		
	} else {
		neonsso_error_log('Could not match membership ID to a role.' );
		return null;
	}
}

/**
 * Retrieves all possible membership terms from Neon.
 *
 *
 * @since 1.0.0
 *
 * @return array $terms A list of membership terms from Neon API.
 */
function neonsso_get_membership_terms() {
	
	// Log in to Neon API
	$session_id = neonsso_login_neon_api();
	
	// Upon successful API login, we retrieve information about this person
	if ( $session_id ){
		$membership_terms_parameters = array();
		$membership_terms_parameters['userSessionId'] = $session_id;
		
		$membership_terms_url = 'https://api.neoncrm.com/neonws/services/api/membership/listMembershipTerms';
		
		$membership_terms_response = wp_remote_post( $membership_terms_url, array(
			'method' => 'POST',
			'body'   => $membership_terms_parameters,
			)
		);
		
		$membership_terms = json_decode($membership_terms_response['body'], true);
		
		if ( $membership_terms['listMembershipTermsResponse']['operationResult'] == 'SUCCESS' ) {
			$terms = $membership_terms['listMembershipTermsResponse']['membershipTerms']['membershipTerm'];
			return $terms;
		} else {
			neonsso_error_log( 'Could not retrieve a list of possible membership terms from NeonCRM' );
			return null;
		}
	} else {
		return null;
	}
}

/**
 * Returns an error message for Neon organization accounts without a valid email address.
 *
 * @since 1.0.0
 *
 * @return string $message HTML error message
 */
function neonsso_org_email_is_required() {
	$url = 'https://' . NEONSSO_ORG_ID . '.z2systems.com/np/constituent/companyEdit.do';
	$message = '<p class="message">Your Neon account must include an email address. <a href="' . esc_url( $url ) . '">Click here to update your profile.</a></p>';
	return $message;
}

/**
 * Returns an error message for Neon individual accounts without a valid email address.
 *
 * @since 1.0.0
 *
 * @return string $message HTML error message
 */
function neonsso_ind_email_is_required() {
	$url = 'https://' . NEONSSO_ORG_ID . '.z2systems.com/np/constituent/accountEdit.do';
	$message = '<p class="message">Your Neon account must include an email address. <a href="' . esc_url( $url ) . '">Click here to update your profile.</a></p>';
	return $message;
}

/**
 * Returns an error message for API connection errors.
 *
 * @since 1.0.0
 *
 * @return string $message HTML error message
 */
function neonsso_api_is_broken() {
	$message = '<p class="message">There was an unexpected connection error. Please use the login fields below instead or try again later.</p>';
	return $message;
}

/**
 * Adds a detection function to log in users from any page
 *
 * Users who follow the [neon_sign_in_link_return] shortcode are logged in using this function.
 *
 * @since 1.1.0
 * 
 * @return void
 */
function neonsso_detect_sso_login() {
	
	// Ensure our constants are loaded
	if ( ! NEONSSO_LOADED ) {
		return null;
	}
	
	// Check for the OAuth Code from NeonCRM
	if ( isset( $_GET['code'] ) ) {
		
		$code = $_GET['code'];
		
		// Send a POST to Neon's OAuth to retrieve ID
		$neon_id = neonsso_oauth_post( $code );
		
		// Debugging
		neonsso_error_log( 'OAuth Neon ID: ', $neon_id );
		
		// Log the user in based on their Neon ID 
		if ( $neon_id ) {
			
			// Search for an existing WP user based on their NeonCRM Account ID
			$user_parameters = array(
				'meta_key'    => 'neon_id', 
				'meta_value'  => $neon_id, 
				'number'      => 1, 
				'count_total' => false,
				'fields'      => 'ID',
			);
			
			// Retrieve user from WP users
			$get_users = get_users( $user_parameters );

			// If user exists, reset role based on settings
			if ( isset( $get_users[0] ) && !empty( $get_users[0] ) ){
							
				// Get their WordPress user ID
				$user_id = $get_users[0];
				
				// Debugging
				neonsso_error_log( 'Existing WordPress User ID: ', $user_id );
				
				// Assume the default role
				$role = NEONSSO_DEFAULT_ROLE;
				
				if ( NEONSSO_ENABLE_MEMBERSHIP ) {			
					// Check and retrieve the user's current membership			
					$membership_id = neonsso_membership_check( $neon_id );
					
					if ( $membership_id ) {					
						// Retrieve the correct role based on their membership
						$role = neonsso_match_role( $membership_id );	
					}
				}
				
				// Debugging
				neonsso_error_log( 'WordPress Role: ', $role );
				
				// Update the WP user's role
				wp_update_user( array( 'ID' => $user_id, 'role' => $role ) );
		
				// Get the WP user
				$user = get_user_by( 'id', $user_id );
				
				// Log the user in
				wp_set_current_user( $user_id, $user->user_login );
				wp_set_auth_cookie( $user_id );
				
				// Fixes compatibility issue with WooCommerce. 
				// If WooCommerce is active, we defer the sign-in action.
				if ( !class_exists( 'WooCommerce' ) ) {
				  do_action( 'wp_login', $user->user_login );
				}
				
				return $user;

			}
			// If no NeonCRM user is found, create one using data from the API
			else {
				// Log in to NeonCRM API
				$login = neonsso_login_neon_api();
				
				// If API login successful, retrieve data for this person
				if ( $login ){
					
					// Retrieve individual account
					$user_data = neonsso_get_individual( $neon_id, $login );

					if ( !isset( $user_data ) && ( 'error_message' != $user_data ) ) {
						// If error, attempt to retrieve organization account
						$user_data = neonsso_get_organization( $neon_id, $login );
					}					

					if ( isset( $user_data ) && ( $user_data != 'error_message' ) ) {

						// Insert the user into WP
						$user_id = wp_insert_user( $user_data );
						
						// Debugging
						neonsso_error_log( 'New WordPress User ID: ', $user_id );
						
						if ( ! is_wp_error( $user_id ) ) {
						
							// Append Neon account ID to WP user account
							update_user_meta( $user_id, 'neon_id', $neon_id );
							
							// Get the WordPress user
							$user = get_user_by( 'id', $user_id );	
							
							// Assume the default role
							$role = NEONSSO_DEFAULT_ROLE;
							
							if ( NEONSSO_ENABLE_MEMBERSHIP ) {	
								// Check and retrieve the user's current membership		
								$membership_id = neonsso_membership_check( $neon_id );
								
								if ( $membership_id ) {							
									// Retrieve the correct role based on their membership
									$role = neonsso_match_role( $membership_id );								
								}
							}
							
							// Debugging
							neonsso_error_log( 'WordPress Role: ', $role );

							// Update their role
							wp_update_user( array( 'ID' => $user_id, 'role' => $role ) );
							
							// Log the user in
							wp_set_current_user( $user_id, $user->user_login );
							wp_set_auth_cookie( $user_id );
							do_action( 'wp_login', $user->user_login );
						}
						
						
					}
				} else { 
					// Show the API broken error message
					add_filter( 'login_message', 'neonsso_api_is_broken' );
				}
			}
			
		}		
	}
}
// run it before the headers and cookies are sent
add_action( 'after_setup_theme', 'neonsso_detect_sso_login' );

/**
 * Adds a shortcode to use dynamic redirect for SSO
 *
 * Adds a shortcode so that users who follow the login link are redirected back to the original page from which they began.
 *
 * @since 1.1.0
 *
 * @return HTML Link
 */
function neonsso_dynamic_redirect_shortcode( $atts, $content = null ){
	if ( !is_user_logged_in() ){
		if ( !$content ){ 
			$content = NEONSSO_BUTTON_TEXT;
		}
		// Determine page type, archive or single
		if ( is_archive() ){
			// custom post type archive
			if ( is_post_type_archive() ){ 
				$permalink = get_post_type_archive_link( get_query_var('post_type') );
			} else if ( is_tax() ){
				$permalink = get_term_link( get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
			} else if ( is_category() ){ 
				$permalink = get_category_link( get_query_var('cat') );
			} else if ( is_tag() ){ 
				$permalink = get_tag_link( get_query_var( 'tag_id' ) );
			} else {
				$permalink = get_permalink();
			}
		} else {
			// single post
			$permalink = get_permalink();
		}		
		$a = shortcode_atts( array(
			'class' => 'button',
			'redirect_to' => $permalink
		), $atts );
		$the_url = 'https://' . NEONSSO_ORG_ID . '.z2systems.com/np/oauth/auth?response_type=code&client_id=' . urlencode( NEONSSO_CLIENT_ID ) . '&redirect_uri=' . rawurlencode( $a['redirect_to'] );		
		return '<a href="' . esc_url( $the_url ) . '" class="' . esc_attr( $a['class'] ) . '">' . $content . '</a>';
	}
}
add_shortcode( 'neon_sign_in_link_return', 'neonsso_dynamic_redirect_shortcode' );


/**
 * Adds a redirect after logout to sign out of NeonCRM.
 *
 * @since 1.1.5
 */
function neonsso_auto_redirect_external_after_logout(){
	
	$user = wp_get_current_user();
	
	// Get user's Neon ID
	$neon_id = get_user_meta( $user->ID, 'neon_id', true);
	
	// Get configuration to check if this setting is enabled
	$options = get_option( 'neonsso_settings' );
		
	// If the user is a neon user and the setting is enabled, do the redirect
	if ( $neon_id && $options['neonsso_enable_double_logout'] === 1 ) {
		wp_redirect( 'https://' . NEONSSO_ORG_ID . '.z2systems.com/np/logout.do?targetUrl=' . home_url() );
		exit();
	}
	
}
add_action( 'wp_logout', 'neonsso_auto_redirect_external_after_logout');

/**
 * Adds a custom error logging function.
 *
 * @since 1.2.0
 */
function neonsso_error_log($message, $variable = null){
	
	// Only log errors if DEBUG mode is enabled
	if (defined('WP_DEBUG') && true === WP_DEBUG) {
		
		// json_encode variables if they are objects or arrays
		if ( is_array( $variable ) || is_object( $variable ) ){
			$variable = json_encode( $variable );
		}
		
		// Log the errors
		error_log( '[NeonSSO] ' . $message . ' ' . $variable );
	}
	
}