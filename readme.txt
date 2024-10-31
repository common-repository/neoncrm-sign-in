=== NeonCRM Sign-In ===
Contributors: colinpizarek
Donate link: https://www.neoncrm.com/
Tags: neon, neoncrm, crm, nonprofit, sso, single sign-on, oauth
Requires at least: 4.0
Tested up to: 5.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sign in to WordPress using a NeonCRM constituent account.

== Description ==

> This plugin is no longer being publicly supported and will not be updated to be compatible with the newest version of WordPress. If you are interested in pursuing Single Sign On options for your NeonCRM database, please contact us at sales@neoncrm.com. 

Sign in to WordPress using a NeonCRM constituent login and password. Use this single sign-on to assign roles to WordPress users.

With this plugin, you can also assign roles to each NeonCRM constituent based on their active membership, retrieved from NeonCRM.

Requires a minimum of PHP 5.3.

== Installation ==

= Setup =

1. Obtain a NeonCRM account. [Learn about NeonCRM](https://www.neoncrm.com/ "NeonCRM by Z2 Systems, Inc.")
2. Ensure that OAuth is enabled for your NeonCRM system. This guide can help you determine whether the API is enabled for your system:
[OAuth Keys](http://help.neoncrm.com/api/oauth "OAuth Keys")
3. Generate an API key to be used with this plugin. This guide explains how to generate an API key: [API Keys](http://help.neoncrm.com/api/keys "API Keys")
4. Extract all files in the 'neon-events' plugin to the '/wp-content/plugins/' directory,
5. Activate the plugin through the 'Plugins' menu in WordPress.
6. Navigate to Settings -> NeonSSO Settings
7. Enter a valid API Key, Organization ID, OAuth Client ID, and OAuth Secret Key into the plugin settings.

= Use =

Once the plugin is properly configured (following the steps above), a new login button will appear on your login page.

1. Users with NeonCRM constituent accounts should click that button to log in.
2. They will be directed to the NeonCRM login screen.
3. After entering their credentials, they will be redirected BACK to WordPress and taken to their profile page. The sign-in process will automatically populate their WordPress profile with data retrieved from NeonCRM, including First Name, Last Name, Email, and Login name.

= Menus =
You can add the sign-in link to your navigation menus by going to **Appearance > Menus**. You may need to toggle this new section in the **Screen Options**.

= Standard Shortcode =

You can use a shortcode to place a login link anywhere on your website. This shortcode will direct users to the admin dashboard upon successful login:
`[neon_sign_in_link]`

You can also include your own CSS classes in this shortcode:
`[neon_sign_in_link class="btn btn-default"]`

And, you can override your default button text like this:
`[neon_sign_in_link]Member Login[/neon_sign_in_link]`

= Redirection Shortcode =

Place this shortcode within a page or post. Users who follow this link will be redirected back to the page or post:
`[neon_sign_in_link_return]`

You can also include your own CSS classes in this shortcode:
`[neon_sign_in_link_return class="btn btn-default"]`

You can also send users to a different page on your site:
`[neon_sign_in_link_return redirect_to="https://mysite.org/specific-page"]`

And, you can override your default button text like this:
`[neon_sign_in_link_return]Sign In and Return to this Page[/neon_sign_in_link_return]`

= Caveats =
* This plugin expects that your user accounts exist in NeonCRM and **do not** yet exist in WordPress. If you need to link an existing WordPress user to an existing NeonCRM user, you will need to enter their NeonCRM Account ID in the appropriate field on their WordPress profile page.
* WordPress requires each user to have a unique email address. NeonCRM does not. This means it's possible for multiple NeonCRM users to have the same email address. If a user tries to log in to WordPress using this plugin and their email address has already been associated with a WordPress user, the login will fail. The plugin does not yet gracefully deal with this issue, largely due to the structure of NeonCRM.


== Frequently Asked Questions ==

= What is NeonCRM? =

NeonCRM is a web-based fundraising and membership system that provides nonprofit organizations with all the tools
required to increase donations and memberships while automating common processes and streamlining staff's day-to-day tasks. Learn more at [www.neoncrm.com](https://www.neoncrm.com/ "neoncrm.com").

= Where do I get an API Key / Org ID? =

This guide explains how to generate an API key: [API Keys](https://developer.neoncrm.com/api/getting-started/api-keys/ "API Keys")

= Where do I get my OAuth Client ID / Secret? =
This guide explains where to find these: [OAuth Keys](https://developer.neoncrm.com/api/accounts/oauth-2/ "OAuth Keys")

= How can I use this plugin to create a restricted-access content area? =
I highly recommend using the [Members](https://wordpress.org/plugins/members/ "Members Plugin") plugin in conjunction with NeonCRM Sign-In. It lets you set up user access based on custom roles. Any roles you create using the Members plugin will be available for mapping and assignment in the NeonCRM Sign-In plugin.

= How can I use this plugin to create a restricted-access forum? =
I highly recommend using the [Members](https://wordpress.org/plugins/members/ "Members Plugin") plugin in conjunction with NeonCRM Sign-In and [bbPress](https://wordpress.org/plugins/bbPress/ "bbPress"). bbPress and Members work together to create restricted-access forums. Use the NeonCRM Sign-In plugin to authenticate your users based on membership and assign them the custom roles you create.

== Changelog ==

= 1.2.0 =
* Fixes a bug that prevents successful logout.
* Fixes another critical bug with error logging.

= 1.1.9 =
* Fixes a critical bug with error logging.

= 1.1.8 =
* Improved error logging to increase ability to troubleshoot problems.

= 1.1.7 =
* Fixes a bug that prevents members with a long history of membership from receiving the correct access level.

= 1.1.6 =
* Fixes a bug that prevents lifetime membership terms from working properly.

= 1.1.5 =
* Adds an option so that users can be logged out of WordPress and NeonCRM simultaneously.

= 1.1.4 =
* Fixes a bug that causes login failure when used with WooCommerce.

= 1.1.3 =
* Administrators can now edit the NeonCRM Account ID associated with a WordPress user.

= 1.1.2 =
* Catches WP_Errors generated at user creation and displays them to a user.
* Fixes [neon_sign_in_link_return] shortcode to support archive pages.

= 1.1.1 =
* Fixed a critical bug that causes authentication with NeonCRM to fail.

= 1.1.0 =
* Added a sign-in link shortcode that allows you to redirect users back to their original page (or any page) instead of the dashboard.
* Fixed a bug that caused users logging in for the first time to be given the default level of access.
* Changed the date format for membership dates on the user profile page.

= 1.0.2 =
* Fixed a bug that causes the settings page to conflict with other plugins.
* Added shortcode for the sign-in link.
* Added sign-in button as an option to nav-menus.php.
* Refactored the sign-in URL into a constant

= 1.0.1 =
* Fixed a bug that causes the default role to not save.

= 1.0.0 =
* Original release

== Upgrade Notice ==

= 1.2.0 =
* Fixes a bug that prevents successful logout.
* Fixes another critical bug with error logging.

= 1.1.9 =
* Fixes a critical bug with error logging.

= 1.1.8 =
* Improved error logging to increase ability to troubleshoot problems.

= 1.1.7 =
* Fixes a bug that prevents members with a long history of membership from receiving the correct access level.

= 1.1.6 =
* Fixes a bug that prevents lifetime membership terms from working properly.

= 1.1.5 =
* Adds an option so that users can be logged out of WordPress and NeonCRM simultaneously.

= 1.1.4 =
* Fixes a bug that causes login failure when used with WooCommerce.

= 1.1.3 =
* Administrators can now edit the NeonCRM Account ID associated with a WordPress user. This allows you to link existing WordPress users to existing NeonCRM users.

= 1.1.2 =
* Catches WP_Errors generated at user creation and displays them to a user.
* Fixes [neon_sign_in_link_return] shortcode to support archive pages.

= 1.1.1 =
* Fixes a critical bug that causes authentication with NeonCRM to fail.

= 1.1.0 =
* Added a sign-in link shortcode that allows you to redirect users back to their original page (or any page) instead of the dashboard.
* Fixed a bug that caused users logging in for the first time to be given the default level of access.

= 1.0.2 =
* Added a shortcode for the sign-in link so it can be placed anywhere on the site.
* Added a section to the Menus page so you can place the sign-in link in your menus.
* Minor bug fixes.

= 1.0.1 =
* Fixed a bug that causes the default role to not save.

= 1.0.0 =
* Original release
