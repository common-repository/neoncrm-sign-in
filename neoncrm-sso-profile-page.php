<?php
/**
 * Displays membership details on profile page.
 *
 * @since 1.0.0
 *
 * @param object $user WP User object
 * @return html
 */
function neonsso_show_current_membership( $user ) {
	
	if ( NEONSSO_ENABLE_MEMBERSHIP == 1 ) {
		// Retrieve user's neon account ID
		$neon_id = get_user_meta( $user->ID, 'neon_id', true);
		
		if ( $neon_id ) {
		
			// Retrieve current membership info
			$current_membership = neonsso_current_membership_details( $neon_id );
			
			if ( $current_membership ){
				$url = 'https://' . NEONSSO_ORG_ID . '.z2systems.com/np/constituent/membershipHome.do';
				$start_date = date( 'm/d/Y', strtotime( $current_membership['termStartDate'] ) );
				$end_date   = date( 'm/d/Y', strtotime( $current_membership['termEndDate'] ) );
				?>
				<h2>Current Membership</h2>
				<table class="form-table">
					<tbody>
						<tr>
							<th>Membership Term Name</th>
							<td><?php echo esc_html( $current_membership['membershipTerm']['termInfo']['name'] ); ?></td>
						</tr>
						<tr>
							<th>Membership Level</th>
							<td><?php echo esc_html( $current_membership['membershipName'] ); ?></td>
						</tr>
						<tr>
							<th>Start Date</th>
							<td><?php echo esc_html( $start_date ); ?></td>
						</tr>
						<tr>
							<th>End Date</th>
							<?php if ( $current_membership['termDuration'] == 'LIFE' ): ?>
								<td>Lifetime</td>
							<?php else: ?>
								<td><?php echo esc_html( $end_date ); ?></td>
							<?php endif; ?>
						</tr>
					</tbody>
				</table>
				<p><a href="<?php echo esc_url( $url ); ?>">Click here to manage your membership.</a></p>
			<?php 
			}
		}
	}
}

/**
 * Retrieves membership details and returns one valid membership.
 *
 * @since 1.0.0
 *
 * @param int $neon_id Neon account ID
 * @return array $current_membership Neon membership details.
 */
function neonsso_current_membership_details( $neon_id ) {
	
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
					$membership_term[$end] = $term;
				}
				
			}
			
			// Get the membership ID of the term with the highest end date
			if ( !empty( $membership_term ) ){
				$oldest_term = max( array_keys( $membership_term ) );
				
				$membership_term_id = null;
				
				// Set the return variable to the ID of the valid membership term
				$current_membership = $membership_term[ $oldest_term ];
				
				return $current_membership;
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
 * Adds membership details to the view profile page.
 *
 * @since 1.0.0
 */
add_action('show_user_profile', 'neonsso_show_current_membership');

/**
 * Adds membership details to the edit profile page.
 *
 * @since 1.0.0
 */
add_action('edit_user_profile', 'neonsso_show_current_membership');

/**
 * Displays NeonCRM Account ID field on profile page.
 *
 * @since 1.1.3
 *
 * @param object $user WP User object
 * @return html
 */
function neonsso_show_account_id_profile( $user ){ 
	?>
	<h2>NeonCRM Account ID</h2>
	<table class="form-table">
		<tbody>
			<tr>
				<th>Account ID</th>
				<td>
					<?php if ( current_user_can( 'edit_users' ) ): ?>
					<input type="text" name="neon_id" value="<?php echo get_user_meta( $user->ID, 'neon_id', true); ?>" class="regular-text" /><br>
					<span class="description"><?php _e("Use this field to attach existing WordPress users to existing NeonCRM accounts or to update NeonCRM account IDs that have changed."); ?></span>
					<?php else: ?>
					<?php echo get_user_meta( $user->ID, 'neon_id', true); ?>
					<?php endif; ?>
					
				</td>
			</tr>
		</tbody>
	</table>
	<?php
}
/**
 * Adds NeonCRM Account ID to the view profile page.
 *
 * @since 1.1.3
 */
add_action( 'show_user_profile', 'neonsso_show_account_id_profile' );

/**
 * Adds NeonCRM Account ID to the edit profile page.
 *
 * @since 1.1.3
 */
add_action( 'edit_user_profile', 'neonsso_show_account_id_profile' );

/**
 * Allows update of NeonCRM Account ID on the edit profile page.
 *
 * @since 1.1.3
 *
 * @param integer $user_id
 */
function neonsso_update_neon_account_id_profile( $user_id ) {
    if ( !current_user_can( 'edit_users', $user_id ) ) { 
        return false; 
    }
    update_user_meta( $user_id, 'neon_id', $_POST['neon_id'] );
}

/**
 * Allows update of NeonCRM Account ID on the edit profile page.
 *
 * @since 1.1.3
 */
add_action( 'personal_options_update', 'neonsso_update_neon_account_id_profile' );

/**
 * Allows update of NeonCRM Account ID on the edit profile page.
 *
 * @since 1.1.3
 */
add_action( 'edit_user_profile_update', 'neonsso_update_neon_account_id_profile' );