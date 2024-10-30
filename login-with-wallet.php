<?php

/**
 * Plugin Name:        Login With Wallet
 * Plugin URI:				 https://kb.roveteam.com/docs/login-with-wallet/
 * Description:     	 Simply login with your Ethereum wallet!
 * Version:         	 1.0.0
 * Requires at least:  5.0
 * Requires PHP:			 7.0
 * Author:          	 Rove Team
 * Author URI:     	   https://roveteam.com
 * Text Domain:     	 login-with-wallet
 * Domain Path:				 /languages
 * License:            GPL-2.0+
 * License URI:        http://www.gnu.org/licenses/gpl-2.0.txt
 */

if(!defined('LOGIN_WITH_WALLET_PLUGIN_VERSION'))
	define('LOGIN_WITH_WALLET_PLUGIN_VERSION', '1.0.0');

if(!defined('LOGIN_WITH_WALLET_FILE'))
	define('LOGIN_WITH_WALLET_FILE', __FILE__);

if(!defined('LOGIN_WITH_WALLET_URL'))
	define('LOGIN_WITH_WALLET_URL', plugin_dir_url(__FILE__));

if(!defined('LOGIN_WITH_WALLET_PATH'))
	define('LOGIN_WITH_WALLET_PATH', plugin_dir_path(__FILE__));

if(!defined('LOGIN_WITH_WALLET_OPTION_NAME'))
	define('LOGIN_WITH_WALLET_OPTION_NAME', 'login_with_wallet_options');

// add a widget to WP widgets
require_once(LOGIN_WITH_WALLET_PATH.'/inc/class-widget.php');

// main plugin class
require_once(LOGIN_WITH_WALLET_PATH.'/inc/class-login-with-wallet.php');

// add everything ajax related. Validation and functions
require_once(LOGIN_WITH_WALLET_PATH.'/inc/class-ajax.php');

// do on activating and uninstalling the plugin
register_activation_hook(__FILE__, 'login_with_wallet_do_on_activation');
register_uninstall_hook(__FILE__, 'login_with_wallet_do_on_uninstallation');

/**
 * Set plugin options on activation
 *
 * Set plugin version and default global options
 * @since 	1.0.0
 * @access: public
 */
function login_with_wallet_do_on_activation()
{
	update_option('_login_with_wallet_version',LOGIN_WITH_WALLET_PLUGIN_VERSION);
	$options =  get_option(LOGIN_WITH_WALLET_OPTION_NAME,[]);
	if(empty($options)) {
		$options = [
			'auth_type'                 => 'login_and_register',
			'css_selector'              => '',
			'ens_type'                  => 'subgraph',
			'force_user'                => 'no',
			'if_wallet_is_disconnected' => 'log_out',
			'infura_project_id'         => '',
			'layout_direction'          => 'vertical',
			'modal_intro'               => '',
			'optional_email'            => 'yes',
			'terms_link'                => '',
			'wallet_types'              => ['injected'],
			'whitelisted_accounts'      => '',
			'woocommerce_forms'         => ''
		];
		update_option(LOGIN_WITH_WALLET_OPTION_NAME,$options);
	}
}

/**
 * Delete plugin options on uninstallation
 *
 * Delete plugin version and general option
 * @since		1.0.0
 * @access: public
 */
function login_with_wallet_do_on_uninstallation()
{
	delete_option('_login_with_wallet_version');
	delete_option(LOGIN_WITH_WALLET_OPTION_NAME);
}

/******************************************************************************/

/**
* Initialize the plugin after all plugins loaded
*
* @since	 1.0.0
*/

$login_with_wallet_obj = new LoginWithWallet();
add_action('plugins_loaded',  [$login_with_wallet_obj , 'init']);
