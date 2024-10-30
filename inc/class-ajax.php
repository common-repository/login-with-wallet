<?php

/**
 * All ajax request and methods
 *
 * @since 		1.0.0
 * @global 		$wpdb
 */
class LoginWithWalletAjax
{
	// The security nonce
	private $nonce = 'login_with_wallet_nonce';

		/**
		 * Check security nonce and if the request is valid
		 *
		 * @since 	1.0.0
		 */
		public function check_nonce()
		{
			if (wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), $this->nonce ) === false) {
				wp_send_json_error([
															'message' => esc_html__('Invalid request! Reload the page.', 'login-with-wallet')
										 			], 403);
			}
		}

		/**
		 * Update wp_users table
		 *
		 * @since  1.0.0
		 * @param  integer $user_id               ID of the user
		 * @param  string $username               username to change to
		 * @return bool           								true on successfull update
		 */
		private function update_user($user_id,$username)
		{
			global $wpdb;
			$table = $wpdb->prefix.'users';
			$result = $wpdb->update($table,[
				'user_nicename' => $username,
				'display_name' => $username
			],['ID' => $user_id]);
			return intval($result) > 0 ? true : false;
		}

		/**
		 * Create random unique username
		 *
		 * @since  1.0.0
		 * @param  string $prefix               the prefix of username pattern
		 * @return string         							the random and unique username
		 */
		private function random_unique_username($prefix=''){
    	$user_exists = 1;

			/**
			 * Loop until the username doesn't exits
			 *
			 * @since 1.0.0
			 */
		   do {
	       $rnd_str = sprintf("%06d", mt_rand(1, 9999999));
	       $user_exists = username_exists($prefix . $rnd_str);
		   } while($user_exists > 0);

		   return $prefix . $rnd_str;
		}


		/**
		 * Create a random password to secure the account
		 *
		 * Supports php 5.3+ and 7.0+ differently
		 * @since  1.0.0
		 * @param  string  $base                 10 character of the wallet address
		 * @param  integer $length               random bytes default 16
		 * @return string          password
		 */
		private function create_password($base = '', $length = 16) {
	    if (function_exists('random_bytes')) {
	        $bytes = random_bytes($length / 2);
	    } else {
	        $bytes = openssl_random_pseudo_bytes($length / 2);
	    }
	    return md5(bin2hex($bytes).$base);
		}

		/**
		 * Save admin settings data
		 *
		 * @since 		1.0.0
		 */
		public function store_admin_data()
		{
				$this->check_nonce();
				$data = [];

				/**
				 * Loop through POST variables and separate fields with the prefix
				 * @since  1.0.0
				 */
				foreach ($_POST as $field => $value) {

					// skip the field if it doesn't have the prefix
			    if (substr($field, 0, 18) !== "login_with_wallet_") continue;

			    // remove the login_with_wallet_ prefix to clean things up
			    $field = substr($field, 18);

					// if the value isn't set remove the option
					if (empty($value)) {
						unset($data[$field]);
					} else {
						if (is_array($value)) {
							$data[$field] = [];
							foreach ($value as $key => $v) {
								$data[$field][] = sanitize_text_field($v);
							}
						} else {
							$data[$field] = sanitize_text_field($value);
						}
					}
				}

				update_option(LOGIN_WITH_WALLET_OPTION_NAME, $data);

				wp_send_json_success([
											'message' => esc_html__('Settings saved successfully!', 'login-with-wallet')
										], 200);
			}

		/**
		 * Get modal template
		 *
		 * @since  1.0.0
		 */
		public function get_modal()
		{
			$this->check_nonce();

			$type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
			$args = isset($_POST['args']) ? array_map( 'sanitize_text_field', $_POST['args'] ) : [];

			/**
			 * Check if the type is set
			 *
			 * @since 1.0.0
			 */
			if (!empty($type)) {
				ob_start();

				if ($type == 'choose_wallet') {
					require_once(LOGIN_WITH_WALLET_PATH.'/templates/modals/choose-wallet.php');
				}

				$html = ob_get_clean();
			} else {
				$html = esc_html__('Please select the modal type', 'login-with-wallet');
			}

			wp_send_json_success([
															'html' => $html
												   ], 200);
		}

		/**
		 * Authenticate user
		 *
		 * If the user is logged in just update the wallet
		 * If the user has an account log in the user
		 * If the user doesn't have an account just update the auth_status
		 *
		 * @since 1.0.0
		 */
		public function auth_user()
		{
			// Checks if the request is valid
			$this->check_nonce();

			$account = isset($_POST['account']) ? sanitize_text_field($_POST['account']) : '';
			$connect_type = isset($_POST['connect_type']) ? sanitize_text_field($_POST['connect_type']) : 'injected';

			/**
			 * Make sure the account is set
			 *
			 * @since 1.0.0
			 */
			if (empty($account)) {
				wp_send_json_error([
															'message' => esc_html__('Please connect your account', 'login-with-wallet')
													 ], 403);
			}

			/**
			 * if user is already logged in, it just update the account number for future logins
			 *
			 * @since 1.0.0
			 */
			$user_id = get_current_user_id();
			if ($user_id) {
				// checks for existance of this address
				$users = get_users(['meta_key' => '_login_with_wallet_account_number', 'meta_value' => $account]);

				if (!empty($users)) {
					$user = $users[0];
					if ($user->ID != $user_id) {
						wp_send_json_error([
																	'message' => sprintf(esc_html__('This address is already connected to an account! You can log out here and log in with your wallet. Or, First disconnect your wallet then connect this (%s ) to this (%s)', 'login-with-wallet'),$account,$user->user_login)
															 ], 403);
					}
				}

				update_user_meta($user_id,'_login_with_wallet_account_number', $account);
				update_user_meta($user_id,'_login_with_wallet_connect_type', $connect_type);

				wp_send_json_success([
															'message'=> esc_html__('Updated successfully!', 'login-with-wallet'),
															'auth_type' => 'update',
															'nonce' => wp_create_nonce($this->nonce)
														], 200);
			}

			$options =  get_option(LOGIN_WITH_WALLET_OPTION_NAME,[]);

			/**
			 * Check if user is already registered with this account then login instead of register
			 *
			 * @since 1.0.0
			 */
			$users = get_users(['meta_key' => '_login_with_wallet_account_number', 'meta_value' => $account]);

			if (!empty($users)) {
				$user = $users[0];
				update_user_meta($user->ID,'_login_with_wallet_connect_type',$connect_type);
				wp_set_current_user($user->ID, $user->user_login);
				wp_set_auth_cookie($user->ID,true);
				wp_send_json_success([
															'message'=> esc_html__('Logged in successfully', 'login-with-wallet'),
															'auth_type' => 'login'
														], 200);
			} else {
				// just update the auth_status to register so front-end will handle the registration
				wp_send_json_success([
															'message'=> esc_html__('Searching ENS. You will be registered soon.', 'login-with-wallet'),
															'auth_type' => 'register',
															'nonce' => wp_create_nonce($this->nonce)
														], 200);
			}
		}


		/**
		 * Regsiters the users
		 *
		 * @since  1.0.0
		 */
		public function register_user()
		{
			$this->check_nonce();

			/**
			 * $account  					Wallet address
			 * $name  						ENS name
			 * $connect_type   		Wallet type
			 * @since 						1.0.0
			 */
			$account = isset($_POST['account']) ? sanitize_text_field($_POST['account']) : '';
			$name = isset($_POST['name']) ? sanitize_user(sanitize_text_field($_POST['name'])) : '';
			$connect_type = isset($_POST['connect_type']) ? sanitize_text_field($_POST['connect_type']) : 'injected';

			$options =  get_option(LOGIN_WITH_WALLET_OPTION_NAME,[]);
			$whitelisted_accounts = $options['whitelisted_accounts'] ? explode(",",$options['whitelisted_accounts']) : [];
			$whitelisted_accounts = empty($whitelisted_accounts) ? [] : array_map('trim',$whitelisted_accounts);

			/**
			 * Check WordPress global option `Anyone can register` first
			 * then check plugin's whitelisted_accounts option
			 *
			 * @since 1.0.0
			 */
			$wp_user_can_register_option = get_option('users_can_register');

			if ( $wp_user_can_register_option == 1 ) {

				if (!empty($whitelisted_accounts) && !in_array($account,$whitelisted_accounts)) {
					wp_send_json_error([
																'message' => esc_html__('You are not allowed to register! Contact the administrator.', 'login-with-wallet')
														 ], 403);
				}

				/**
				 * Check if this address is already connected to an account
				 *
				 * @since 1.0.0
				 */
				$users = get_users(['meta_key' => '_login_with_wallet_account_number', 'meta_value' => $account]);
				if (!empty($users)) {
						wp_send_json_error([
																	'message' => sprintf(esc_html__('This address is already connected to an account! Please try to log in (%s)', 'login-with-wallet'),esc_html($account))
															 ], 403);
				}

				$login = empty($name) ? $this->random_unique_username('user') : $name;
				$login = mb_strtolower(str_replace(" ","",$login));

				$random_password = $this->create_password(substr($account,0,10));
				$user_data = [
					'user_login' => $login,
					'user_pass' => $random_password,
					'nickname' => $name,
					'meta_input' => [
						'_login_with_wallet_account_number' => $account,
						'_login_with_wallet_connect_type' => $connect_type,
					]
				];

				if (!empty($name)) {
					$user_data['meta_input']['_login_with_wallet_account_ens'] = $name;
				}

				/**
				 * Use wp_insert_user instead of wp_create_user to pass the username
				 * and change it on registration. Unless wp_set_auth_cookie won't work properly
				 *
				 * @since  1.0.0
				 */
				$user_id = wp_insert_user($user_data);

				/**
				 * Check if the registration is successfull then log in the user
				 *
				 * @since 1.0.0
				 */
				if (is_wp_error($user_id)) {
					wp_send_json_error([
															'message' => $user_id->get_error_message()
													 ], 403);
				} else {
					$user = get_user_by('id', $user_id);
					wp_set_current_user($user->ID, $user->user_login);
					wp_set_auth_cookie($user->ID,true);
					wp_send_json_success([
															'message'=> esc_html__('Registration completed sucessfully!', 'login-with-wallet'),
															'auth_type' => 'register',
															'nonce' => wp_create_nonce( $this->nonce )
														], 200);
				}
			} else {
				wp_send_json_error(
					[
						'message' => esc_html__('Registration is disabled or not allowed via wallet!', 'login-with-wallet')
					]
				);
			}
		}

		/**
		 * Update user's meta and nicename if ENS is available
		 *
		 * @since  1.0.0
		 */
		public function update_ensname()
		{

			$this->check_nonce();

			$account = isset($_POST['account']) ? sanitize_text_field($_POST['account']) : '';
			$name = isset($_POST['name']) ? sanitize_user(sanitize_text_field($_POST['name'])) : '';

			$user_id = get_current_user_id();
			update_user_meta($user_id,'_login_with_wallet_account_ens',$name);
			update_user_meta($user_id,'nickname',$name);
			wp_send_json_success([
														'message'=> esc_html__('Updated successfully!'.esc_html($user_id), 'login-with-wallet')
													], 200);

		}

		/**
		 * Disconnect the WP user from the wallet by deleting user metas
		 *
		 * @since 		1.0.0
		 */
		public function disconnect()
		{
			$user_id = get_current_user_id();
			delete_user_meta($user_id, '_login_with_wallet_account_ens');
			delete_user_meta($user_id, '_login_with_wallet_account_number');
			delete_user_meta($user_id, '_login_with_wallet_connect_type');
		}

		/**
		 * Log out user and remove related cookies
		 * @since 		1.0.0
		 */
		public function logout_user()
		{
			wp_logout();
			unset($_COOKIE['login_with_wallet_logged_out']);
			setcookie('login_with_wallet_logged_out',null,-1,"/");
		}
}
