<?php 

/**
 * Create the metabox for nav-menus.php
 *
 * @since 1.0.2
 */
if ( !class_exists('NeonSSO_Menu')) {
	class NeonSSO_Menu {
		
		public function add_nav_menu_meta_boxes() {
			add_meta_box(
				'neonsso_links',
				__('Neon Sign-In Link'),
				array( $this, 'neonsso_links'),
				'nav-menus',
				'side',
				'low'
			);
		}
				
		/** Standard Links */
		public function neonsso_links() {
			
			/* Each link option is read from this array: */
			$neonsso_pages = array(
				'login' => array(
					'menu_title'	=> 'Sign-In Link',
					'default_title' => 'Sign in with NeonCRM',
					'url' 			=> NEONSSO_LOGIN_URL,
				),
			);
						
			/* Iteration counter for foreach loop */
			$i = 0;
			?>
			
			<div id="posttype-neoncrm-link" class="posttypediv">
				<ul id="neoncrm-link-tabs" class="neoncrm-link-tabs add-menu-item-tabs">
					<li class="tabs">
						<a class="nav-tab-link" data-type="tabs-panel-neoncrm-links" href="<?php echo admin_url( 'nav-menus.php?category-tab=standard#tabs-panel-neoncrm-links' ); ?>">All</a>
					</li>
				</ul>
				<div id="tabs-panel-neoncrm-links" class="tabs-panel tabs-panel-active">
					<ul id ="neoncrm-links-checklist" class="categorychecklist form-no-clear">
					
						<?php foreach ($neonsso_pages as $link): // iterate through the array of links  ?>
						<?php $i++; ?>
						
						<li>
							<label class="menu-item-title">
								<input type="checkbox" class="menu-item-checkbox" name="menu-item[-<?php echo $i; ?>][menu-item-object-id]" value="-1"> <?php echo $link['menu_title']; ?>
							</label>
							<input type="hidden" class="menu-item-type" name="menu-item[-<?php echo $i; ?>][menu-item-type]" value="custom">
							<input type="hidden" class="menu-item-title" name="menu-item[-<?php echo $i; ?>][menu-item-title]" value="<?php echo $link['default_title']; ?>">
							<input type="hidden" class="menu-item-url" name="menu-item[-<?php echo $i; ?>][menu-item-url]" value="<?php echo $link['url']; ?>">
							<input type="hidden" class="menu-item-classes" name="menu-item[-<?php echo $i; ?>][menu-item-classes]" value="neoncrm-link">
						</li>
						
						<?php endforeach; ?>
						
					</ul>
				</div>
				<p class="button-controls">
					<span class="list-controls">
						<a href="/wordpress/wp-admin/nav-menus.php?page-tab=all&amp;selectall=1#posttype-neoncrm-link" class="select-all">Select All</a>
					</span>
					<span class="add-to-menu">
						<input type="submit" class="button-secondary submit-add-to-menu right" value="Add to Menu" name="add-post-type-menu-item" id="submit-posttype-neoncrm-link">
						<span class="spinner"></span>
					</span>
				</p>
			</div>

		<?php }

	}
}

$custom_nav = new NeonSSO_Menu;

add_action('admin_init', array($custom_nav, 'add_nav_menu_meta_boxes'));