<?php

/**
 * create the option page and add the web app script
 *
 *@since 		1.0.0
 */
class LoginWithWallet
{
	// The security nonce
	private $nonce = 'login_with_wallet_nonce';

	// General option
	private $settings = [];

	/**
	 * Get and set genral settings as a property
	 *
	 * @since		1.0.0
	 */
	public function __construct() {
		$this->settings = get_option(LOGIN_WITH_WALLET_OPTION_NAME,[]);
	}

	/**
	 * Inits the main plugin actions for WordPress
	 *
	 * @since 		1.0.0
	 */
	public function init() {
		$settings =  $this->settings;

		/**
		 * Only hook on admin dashboard
		 *
		 * Add admin menu and scripts for settings page
		 * @since 		1.0.0
		 */
		if (is_admin()) {
			add_action('admin_menu',                [$this,'add_admin_menu']);
			add_action('admin_enqueue_scripts',     [$this,'add_admin_enqueues']);
		}

		// add front-end and wp-login.php scripts
		add_action('wp_enqueue_scripts',     [$this,'add_enqueues']);
		add_action('login_enqueue_scripts', [$this,'add_enqueues']);

		// add a shortcode
		add_shortcode('login-with-wallet', [$this,'shortcode']);

		// register the widget
		add_action('widgets_init', [$this,'widget']);

		// add and show user account number on user's dashboard profile page
		add_action('show_user_profile', [$this,'show_user_profile_field'],100, 1);
		add_action('edit_user_profile', [$this,'show_user_profile_field'],100, 1);


		// add settings action link to plugins page
		add_filter( 'plugin_action_links_'.plugin_basename(LOGIN_WITH_WALLET_FILE), [$this, "add_plugin_action_row_links"], 10, 2 );

		/**
		 * Check if only WooCommerce is installed and activated
		 *
		 * @since 		1.0.0
		 */
		if (!function_exists('is_woocommerce_activated')) {
			if (class_exists('woocommerce')) {
				$woocommerce_forms = empty($settings['woocommerce_forms']) ? 'disabled' : $settings['woocommerce_forms'];

				/**
				 * check if the option active then hook actions and filters
				 *
				 * @since 		1.0.0
				 */
				if ($woocommerce_forms == 'enabled') {
					add_action('woocommerce_login_form_end', [$this,'woocommerce_login_form']);
					add_action('woocommerce_register_form_end', [$this,'woocommerce_register_form']);
					add_action('woocommerce_after_lost_password_form', [$this,'woocommerce_lost_password_form']);

					if (isset($settings['force_user']) && $settings['force_user'] == 'yes') {
						add_filter( 'woocommerce_registration_errors',[$this,'filter_registration'], 1000, 3);
					}
				}
			}
		}

		/**
		 * If the option is enabled, blocks users on registration and login
		 *
		 * @since 		1.0.0
		 */
		if (isset($settings['force_user']) && $settings['force_user'] == 'yes') {
			add_filter('authenticate', [$this,'filter_authentication'],1000, 3);
			add_filter( 'registration_errors',[$this,'filter_registration'], 1000, 3);
			add_filter( 'login_errors',[$this,'filter_login'], 1000, 1);
		}

		/**
		 * If the option is enabled, emails will be optional
		 *
		 * change UI and functionality of the authentication
		 * and profile updating proccess
		 *
		 * @since 		1.0.0
		 */
		if (isset($settings['optional_email']) && $settings['optional_email'] == 'yes') {
			// Remove require email message and fix profile page ui
			add_action('user_profile_update_errors', [$this,'remove_required_email_message'],10, 3);
			add_action('user_new_form', [$this,'fix_profile_ui'], 10, 1);
			add_action('show_user_profile', [$this,'fix_profile_ui'], 10, 1);
			add_action('edit_user_profile', [$this,'fix_profile_ui'], 10, 1);

			// Remove require email message on woocommerce
			add_filter('woocommerce_save_account_details_required_fields',[$this,'remove_woocommerce_required_email_message'],10,1);
			add_action('woocommerce_edit_account_form_tag',[$this,'add_optional_email_attribute'],10,1);

			// fix optional email update when empty
			add_action( 'woocommerce_save_account_details', [$this, 'save_account_details'] );
		}

		// if user logged out of WordPress, set a cookie to disconnect it from JSON-RPC
		add_action('wp_logout', [$this,'set_logout_cookie'], 10,1);


		/**
		 * Initialize ajax calls
		 *
		 * @since 		1.0.0
		 */
		$login_with_wallet_ajax = new LoginWithWalletAjax();

		// saves settings options
		add_action('wp_ajax_login_with_wallet_store_admin_data', [$login_with_wallet_ajax,'store_admin_data']);

		// auth user: login or register or update logged in user account number
		add_action('wp_ajax_nopriv_login_with_wallet_auth_user', [$login_with_wallet_ajax, 'auth_user']);
		add_action('wp_ajax_login_with_wallet_auth_user', [$login_with_wallet_ajax, 'auth_user']);

		// set users ENS name
		add_action('wp_ajax_login_with_wallet_update_ensname', [$login_with_wallet_ajax, 'update_ensname']);
		// register user
		add_action('wp_ajax_nopriv_login_with_wallet_register_user', [$login_with_wallet_ajax, 'register_user']);
		// disconnect
		add_action('wp_ajax_login_with_wallet_disconnect', [$login_with_wallet_ajax, 'disconnect']);

		// log out user: register both priv and nopriv actions to prevent http 400
		add_action('wp_ajax_login_with_wallet_logout_user', [$login_with_wallet_ajax, 'logout_user']);
		add_action('wp_ajax_nopriv_login_with_wallet_logout_user', [$login_with_wallet_ajax, 'logout_user']);

		// gets ajax modal content
		add_action('wp_ajax_login_with_wallet_get_modal', [$login_with_wallet_ajax, 'get_modal']);
		add_action('wp_ajax_nopriv_login_with_wallet_get_modal', [$login_with_wallet_ajax, 'get_modal']);
	}

	/**
	 * Add settings link to plugin page
	 *
	 * @since 1.0.0
	 * @param array $links_array        plugin actions link
	 * @param string $plugin_file_name  the plugin file
	 * @return array $actions 					updated links
	 */
	public function add_plugin_action_row_links( $links_array, $plugin_file_name ) {
		$settings_link = ['<a href="' . admin_url( 'options-general.php?page=login-with-wallet-settings' ) . '">'.esc_html__('Settings', 'login-with-wallet').'</a>'];
		$actions = array_merge( $settings_link, $links_array);
   	return $actions;
	}

	/**
	 * Add the plugin settings page to the WordPress Admin Sidebar Menu under settings
	 *
	 * @since 1.0.0
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'options-general.php',
			esc_html__('Login With Wallet', 'login-with-wallet'),
			esc_html__('Login With Wallet', 'login-with-wallet'),
			'manage_options',
			'login-with-wallet-settings',
			[$this, 'settings_layout']
		);
	}


	/**
	 * Hooks admin JS and CSS
	 *
	 * @since 1.0.0
	 */
	public function add_admin_enqueues() {
		// only register admin js and css files if user was on settings page
		$screen = get_current_screen();
		if (strpos($screen->id,'login-with-wallet') || $screen->id == 'toplevel_page_login_with_wallet') {

			if (!wp_style_is('login-with-wallet-settings-css', 'enqueued')) {
			  wp_enqueue_style('login-with-wallet-settings-css', LOGIN_WITH_WALLET_URL. 'assets/css/admin-settings.css', false, LOGIN_WITH_WALLET_PLUGIN_VERSION);
			}

			if (!wp_script_is('login-with-wallet-settings-js', 'enqueued')) {
				wp_enqueue_script('login-with-wallet-settings-js', LOGIN_WITH_WALLET_URL. 'assets/js/admin-settings.js', ['jquery'], LOGIN_WITH_WALLET_PLUGIN_VERSION);

				$admin_options = [
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce'   => wp_create_nonce($this->nonce)
					];

				wp_localize_script('login-with-wallet-settings-js', 'login_with_wallet', $admin_options);
			}
		}
	}


	/**
	 * Load template for settings page
	 *
	 * @since 1.0.0
	 */
	public function settings_layout() {
		// pass $settings to the settings page
		$settings = $this->settings;
		require_once(LOGIN_WITH_WALLET_PATH.'/templates/settings.php');
	}

	/**
	 * Add a shortcode
	 *
	 * @since 1.0.0
	 * @param  array $atts  extracted attributes from the shortcode string
	 * @return string       rendered HTML output
	 */
	public function shortcode($atts) {
		$atts = shortcode_atts([
				'title' => esc_html__('Connect Wallet', 'login-with-wallet'),
		], $atts, 'login-with-wallet');

		$title = isset($atts['title']) && !empty($atts['title']) ? $atts['title'] : esc_html__('Connect Wallet', 'login-with-wallet');
		$out .='<button class="login-with-wallet-connect" type="button">'.esc_html($title).'</button>';

		return $out;
	}

	/**
	 * Add widget to WP widget dashboard
	 *
	 * @since 1.0.0
	 */
	public function widget() {
		register_widget('LoginWithWalletWidget');
	}

	/**
	 * Add text input to my profile page in dashboard
	 *
	 * @since 1.0.0
	 * @param  WP_User $user               User object
	 */
	public function show_user_profile_field($user) {
		?>
	    <h3><?php esc_html_e('Ethereum Account', 'login-with-wallet');?></h3>
	    <table class="form-table">
	        <tr>
	            <th><label for="contact"><?php esc_html_e('Account', 'login-with-wallet');?></label></th>
	            <td>
	                <input type="text" disabled="disabled" value="<?php echo esc_attr(get_user_meta($user->ID, '_login_with_wallet_account_number', true)); ?>" class="regular-text" /><br />
	                <p class="description"><?php esc_html_e('This is your Ethereum address', 'login-with-wallet');?></p>
	            </td>
	        </tr>
	    </table>
		<?php
	}

	/**
	 * Remove the error message to make email field optional
	 *
	 *
	 * @since  1.0.0
	 * @param  WP_Error $errors               generate error on update
	 * @param  array $update                  new updated values
	 * @param  WP_User $user                  the user being updated
	 * @return [type]         [description]
	 */
	public function remove_required_email_message($errors, $update, $user) {
		$errors->remove('empty_email');
	}

	/**
	 * Update the form ui
	 *
	 * remove (required) label and uncheck send email
	 * @since  1.0.0
	 * @param  string $form_type               type of form
	 */
	public function fix_profile_ui($form_type) {
	    ?>
	    <script type="text/javascript">
	        jQuery('#email').closest('tr').removeClass('form-required').find('.description').remove();
	        // Uncheck send new user email option by default
	        <?php if (isset($form_type) && $form_type === 'add-new-user') : ?>
	            jQuery('#send_user_notification').removeAttr('checked');
	        <?php endif; ?>
	    </script>
	    <?php
	}

	/**
	 * Allow empty email to save as user email
	 *
	 * @since  1.0.0
	 * @param  integer $user_id               ID of the current user
	 */
	public function save_account_details($user_id) {
		if (!isset($_POST['account_email']) || empty($_POST['account_email'])) {
			wp_update_user(['ID' => $user_id, 'user_email' => '']);
		}
	}

	/**
	 * Remove require field from the edit account details page
	 *
	 * @since  1.0.0
	 * @param  array $required_fields               array of fields
	 * @return array                   							edited array of fields
	 */
	public function remove_woocommerce_required_email_message($required_fields) {
		unset($required_fields['account_email']);
		return $required_fields;
	}

	/**
	 * Add an attribute to form to activate css class for hiding required field (*)
	 *
	 * @since  1.0.0
	 */
	public function add_optional_email_attribute() {
		echo 'optional_email="yes"';
	}

	/**
	 * Add front-end JS and CSS files
	 *
	 * @since 1.0.0
	 */
	public function add_enqueues() {
		// front-end css for modals
		if (!wp_style_is('login-with-wallet-style', 'enqueued')) {
			wp_enqueue_style('login-with-wallet-style', LOGIN_WITH_WALLET_URL. 'assets/css/login-with-wallet.css', false, LOGIN_WITH_WALLET_PLUGIN_VERSION);
		}

		// global variables to control loading
		if (!wp_script_is('login-with-wallet-global', 'enqueued')) {
			wp_enqueue_script('login-with-wallet-global', LOGIN_WITH_WALLET_URL. 'assets/js/global.js', [], LOGIN_WITH_WALLET_PLUGIN_VERSION,true);
		}

		// user status
		$user_id = get_current_user_id();
		$auth_status = $user_id ? true : false;

		if (!wp_script_is('login-with-wallet-script', 'enqueued')) {
			wp_enqueue_script('login-with-wallet-script', LOGIN_WITH_WALLET_URL. 'assets/js/login-with-wallet.js', ['jquery'], LOGIN_WITH_WALLET_PLUGIN_VERSION,true);
		}

		/**
		 * Get settings and set front-end settings variable
		 *
		 * @since 1.0.0
		 */

		$settings = $this->settings;
		$infura_id = isset($settings['infura_project_id']) ? $settings['infura_project_id'] : '';
		$if_wallet_is_disconnected = isset($settings['if_wallet_is_disconnected']) ? $settings['if_wallet_is_disconnected'] : 'log_out';
		$css_selector = isset($settings['css_selector']) ? $settings['css_selector'] : '';
		$css_selector = empty($css_selector) ? '.login-with-wallet-connect' : '.login-with-wallet-connect,'.$css_selector;
		$auth_type = isset($settings['auth_type']) ? $settings['auth_type'] : 'login_and_register';
		$ens_type = isset($settings['ens_type']) ? $settings['ens_type'] : 'subgraph';
		$ui_style = isset($settings['ui_style']) ? $settings['ui_style'] : 'v2';

		// check wp general option to hook on front-end
		$auth_type = get_option('users_can_register') != 1 ? 'only_login' : 'login_and_register';

		$options = [
			'ajax_url' => admin_url('admin-ajax.php'),
			'home_url' => get_home_url(),
			'template_url' => LOGIN_WITH_WALLET_URL,
			'nonce'   => wp_create_nonce($this->nonce),
			'auth_status' => $auth_status ? 'loggedin' : 'loggedout',
			'auth_type' => esc_js($auth_type),
			'ens_type' => esc_js($ens_type),
			'eth_account' => $auth_status ? esc_js(get_user_meta($user_id,'_login_with_wallet_account_number',true)) : '',
			'eth_account_ens' => $auth_status ? esc_js(get_user_meta($user_id,'_login_with_wallet_account_ens',true)) : '',
			'eth_connect_type' => $auth_status ? esc_js(get_user_meta($user_id,'_login_with_wallet_connect_type',true)) : '',
			'if_wallet_is_disconnected' => esc_js($if_wallet_is_disconnected),
			'css_selector' => esc_js($css_selector),
			'infura_id' => esc_js($infura_id),
			'ui_style' => esc_js($ui_style),

			// multiligual support on front-end
			'labels'   => [
				'loading' => esc_html__('Loading ...', 'login-with-wallet'),
				'connectWallet' => esc_html__('Connect Wallet', 'login-with-wallet'),
				'loginWithWallet' => esc_html__('Log in with Wallet', 'login-with-wallet'),
				'registerWithWallet' => esc_html__('Register With Wallet', 'login-with-wallet'),
				'disconnect' => esc_html__('Disconnect', 'login-with-wallet'),
				'logout' => esc_html__('Logout', 'login-with-wallet'),
				'initializing' => esc_html__('Initializing, please wait ...', 'login-with-wallet'),
				'authenticating' => esc_html__('Authenticating, please wait ...', 'login-with-wallet'),
				'connecting' => esc_html__('Connecting, please wait ...', 'login-with-wallet'),
				'searchingENSName' => esc_html__('Searching for ENS name, please wait ...', 'login-with-wallet'),
				'updating' => esc_html__('Updating profile, please wait ...', 'login-with-wallet'),
				'registering' => esc_html__('Registering, please wait ...', 'login-with-wallet'),
				'close' => esc_html__('Close', 'login-with-wallet'),
				'switchWallet' => esc_html__('Switch Wallet', 'login-with-wallet'),
				'youWillBeDisconnectedAlert' => esc_html__('Are you sure? Disconnecting your wallet will delete your wallet data. You will not be able to log in with this wallet again. To log out, simply try Log out', 'login-with-wallet'),
				'youWillBeLoggedoutAlert' => esc_html__('Are you sure? Disconnecting your wallet will delete your wallet data. You will not be able to log in with this wallet again. You will be logged out of your current account. To log out, simply try Log out', 'login-with-wallet'),
				'youWillBeDisconnectedAlertInjected' => esc_html__('Are you sure? Disconnecting your wallet will delete your wallet data. You will not be able to log in with this wallet again. To log out, simply try Log out. If you want to disconnect completely, you need to remove this domain from your wallet\'s connected sites list.', 'login-with-wallet'),
				'youWillBeLoggedoutAlertInjected' => esc_html__('Are you sure? Disconnecting your wallet will delete your wallet data. You will not be able to log in with this wallet again. You will be logged out of your current account. To log out, simply try Log out. If you want to disconnect completely, you need to remove this domain from your wallet\'s connected sites list.', 'login-with-wallet'),
				'youHaveDisconnectedYourWallet' => esc_html__('You have disconnected your wallet! We are disconnecting your wallet from your account.', 'login-with-wallet'),
				'youHaveDisconnectedYourWalletAndLogoutAccordingly' => esc_html__('You have disconnected your wallet! We are disconnecting your wallet from your account. Please be aware you need to log in first to connect your wallet again. Or register with a new account.', 'login-with-wallet'),
				'youHaveChangedYourWallet' => esc_html__('Your wallet was changed! You have connected this account from different connections! You can switch or connect with the previous connection.', 'login-with-wallet'),
				'unlockFirst' => esc_html__('Please unlock your account first and try again', 'login-with-wallet'),
				'errors' => [
					'cancelled' => wp_kses_post(__('<h3>You Cancelled! </h3><p>The operation is cancelled by you!You can retry by clicking on connect wallet again after refreshing the page.</p>','login-with-wallet')),
					'unauthorized' => wp_kses_post(__('<h3>Unauthorized! </h3><p>The operation is not authorized!You can retry by clicking on connect wallet again after refreshing the page.</p>','login-with-wallet')),
					'unsupported' => wp_kses_post(__('<h3>Unsupported Method! </h3><p>The operation is not supported!You can retry by clicking on connect wallet again after refreshing the page.</p>','login-with-wallet')),
					'disconnected' => wp_kses_post(__('<h3>Disconnected! </h3><p>The operation is disconnected!You can retry by clicking on connect wallet again after refreshing the page.</p>','login-with-wallet')),
					'chainDisconnected' => wp_kses_post(__('<h3>Chain Disconnected! </h3><p>The operation is chain disconnected!You can retry by clicking on connect wallet again after refreshing the page.</p>','login-with-wallet')),
					'pendingOrUnknown' => wp_kses_post(__('<h3>Pending Connect Request Or Unkown Error! </h3><p>You might have a pending request or an unknown error occured!Please check metamask or retry by clicking on connect wallet again after refreshing the page.</p>','login-with-wallet')),
				]
			]
		];

		wp_localize_script('login-with-wallet-script', 'login_with_wallet', $options);
	}

	/**
	 * Set cookie to work on front-end after WP log out
	 *
	 * If user logged out of wordpress, set cookie to load dependencies
	 * for WalletConnect to disconnect from JSON-RPC after next load
	 * @since 		1.0.0
	 * @param integer $user_id  current user id
	 */
	public function set_logout_cookie($user_id) {
		if (!$_COOKIE['login_with_wallet_logged_out']) {
			$eth_connect_type = esc_html(get_user_meta($user_id,'_login_with_wallet_connect_type',true));
			setcookie('login_with_wallet_logged_out',$eth_connect_type,strtotime('+12 months'),"/");
		} else {
			unset($_COOKIE['login_with_wallet_logged_out']);
			setcookie('login_with_wallet_logged_out',null,-1,"/");
		}
	}

	/**
	 * Check user on logging in and prevent them if force_user option is enabled
	 *
	 * @since 	1.0.0
	 * @param  WP_User $user                   1.0.0
	 * @param  string $username               the username of the user
	 * @param  string $password               the password of the user
	 * @return WP_user OR WP_Error            WP_user if successfull, WP_Error if unsuccessful
	 */
	public function filter_authentication($user,$username,$password) {
		if (is_a($user,'WP_User')) {

			/**
			 * Check if the user is administrator then ignore force_user option
			 *
			 * @since 1.0.0
			 */
			if (in_array('administrator', $user->roles)) {
				return $user;
			}

			$user_account_number = get_user_meta($user->ID, '_login_with_wallet_account_number',true);
			if (empty($user_account_number)) {
				return new WP_Error('only_wallet_authentication', esc_html__('You can only authenticate with your wallet!', 'login-with-wallet'));
			}
		}
	}

	/**
	 * Check user on regsistration and prevent them if force_user option is enabled
	 *
	 * @since 		1.0.0
	 * @param  array $errors                              WP_error created on registration
	 * @param  string $sanitized_user_login               submitted username of the user
	 * @param  string $user_email                         submitted email of the user
	 * @return WP_user OR WP_Error            WP_user if successfull, WP_Error if unsuccessful
	 */
	public function filter_registration($errors, $sanitized_user_login, $user_email) {
			$account = isset($_POST['account']) ? sanitize_text_field($_POST['account']) : '';
			if (empty($account)) {
				$errors->add('only_wallet_registration', esc_html__('You can only register with your wallet!', 'login-with-wallet'));
			}
			return $errors;
	}

	/**
	 * Check user on logging in and prevent them if force_user option is enabled
	 *
	 * @since 		1.0.0
	 * @param  array $errors                              WP_error created on registration
	 * @return String $erros             									Force user error
	 */
	public function filter_login($errors) {
			$errors = esc_html__('You can only login with your wallet!', 'login-with-wallet');
			return $errors;
	}

	/**
	 * Add login button to wp-login.php page
	 *
	 * @since 		1.0.0
	 */
	public function add_login_button_to_wp_login() {
		?>
		<button class="button button-hero login-with-wallet-connect wp-login"><?php esc_html_e('Login With Wallet', 'login-with-wallet');?></button>
		<?php
	}

	/**
	 * Add login button to WooCommerce login form
	 *
	 * @since 		1.0.0
	 */
	public function woocommerce_login_form() {
		?>
		<button class="woocommerce-Button button button-hero login-with-wallet-connect woocommerce"><?php esc_html_e('Login With Wallet', 'login-with-wallet');?></button>
		<?php
	}

	/**
	 * Add register button to WooCommerce register form
	 *
	 * @since 		1.0.0
	 */
	public function woocommerce_register_form() {
		?>
		<button class="woocommerce-Button button button-hero login-with-wallet-connect woocommerce"><?php esc_html_e('Register With Wallet', 'login-with-wallet');?></button>
		<?php
	}

	/**
	 * Add recover button to WooCommerce lostpassword form
	 *
	 * @since 		1.0.0
	 */
	public function woocommerce_lost_password_form() {
		?>
		<button class="woocommerce-Button button login-with-wallet-connect woocommerce"><?php esc_html_e('Recover With Wallet', 'login-with-wallet');?></button>
		<?php
	}

}
