<?php

  /**
   * Get general options
   */
  $wallet_types = empty($settings['wallet_types']) ? [] : $settings['wallet_types']; ?>

<div class="wrap">
  <div id="login-with-wallet-settings">
    <form method="post" id="login-with-wallet-admin-form">
      <input type="hidden" name="action" id="login-with-wallet-action" value="login_with_wallet_store_admin_data" />
      <input type="hidden" name="_wpnonce" id="login-with-wallet-security" value="" />
      <input type="hidden" name="login_with_wallet_saved_by_user" id="login_with_wallet_saved_by_user" value="1" />
        <div class="inside login-with-wallet-settings">
            <div class="login-with-wallet-settings postbox">
              <img class="login-with-wallet-settings-logo" width="200" src="<?php echo LOGIN_WITH_WALLET_URL;?>assets/images/login-with-wallet.svg"/>
              <h1><?php esc_html_e('Settings', 'login-with-wallet');?></h1>
              <hr/>
              <h2><?php esc_html_e('Essential Setup', 'login-with-wallet');?></h2>
                <table class="form-table">
                  <tr valign="top">
                    <th scope="row"><?php esc_html_e('Trigger Login', 'login-with-wallet');?></th>
                    <td>
                      <input type="text" name="login_with_wallet_css_selector" value="<?php echo esc_attr($settings['css_selector']); ?>" placeholder="<?php esc_html_e('CSS Selector', 'login-with-wallet');?>"/>
                      <p>1. <?php esc_html_e('Trigger login by clicking the element(s) matching this selector. For Example:menu-item-login', 'login-with-wallet');?></p>
                      <p>2. <?php esc_html_e('You can use this shortcode <code>[login-with-wallet title="Connect Wallet"]</code>', 'login-with-wallet');?></p>
                      <p>3. <?php printf(esc_html_e('Refer to %s and use the widget', 'login-with-wallet') ,'<a href="'.admin_url('widgets.php').'" target="_blank">'.__('widgets area', 'login-with-wallet').'</a>');?></p>
                      <p>4. <?php esc_html_e('Set <code>class="login-with-wallet-connect"</code> to any element on the page.', 'login-with-wallet');?></p>
                    </td>
                  </tr>
                  <?php

                  /**
                   * Check if WooCommerce plugin is available and activated
                   * Then shows the related option
                   *
                   * @since     1.0.0
                   */
                  if (!function_exists('is_woocommerce_activated')) {
                    	if (class_exists('woocommerce')) {
                        ?>
                        <tr valign="top">
                          <th scope="row"><?php esc_html_e('WooCommerce Forms', 'login-with-wallet');?></th>
                          <td>
                              <label for="login_with_wallet_woocommerce_forms"><input type="checkbox" id="login_with_wallet_woocommerce_forms" name="login_with_wallet_woocommerce_forms" value="enabled"<?php echo $settings['woocommerce_forms'] == 'enabled' ? ' checked="checked"' : '';?>><?php esc_html_e('Enable on WooCommerce', 'login-with-wallet');?></label>
                              <p><?php esc_html_e('Adds a button to login/register/Forgot Password Forms', 'login-with-wallet');?></p>
                          </td>
                        <?php
                      }
                    }
                   ?>
                  <tr valign="top">
                    <th scope="row"><?php esc_html_e('Wallets', 'login-with-wallet');?></th>
                    <td>
                      <div class="form-group block">
                        <?php

                          /**
                           * Creates wallet type options
                           * if the type is WalletConnect it will show the related option
                           *
                           * @since     1.0.0
                           */
                          $all_wallet_types = [
                            'injected' => ['title' => esc_html__('Injected', 'login-with-wallet'), 'desc' => esc_html__('Auto detect injected wallets. MetaMask, Safe, NiftyWallet, Trust, Dapper, Coinbase, Cipher, imToken, Status, Tokenry, Opera, Frame, Liquality', 'login-with-wallet')],
                            'walletconnect' => ['title' => esc_html__('WalletConnect', 'login-with-wallet'), 'desc' => esc_html__('Allows users to log in with their WalletConnect account', 'login-with-wallet')]
                          ];
                          if (!empty($all_wallet_types)) {
                            foreach ($all_wallet_types as $wallet_key => $wallet_type) { ?>
                            <label for="login_with_wallet_wallet_types-<?php echo esc_attr($wallet_key);?>">
                              <input class="login_with_wallet_wallet_types" id="login_with_wallet_wallet_types-<?php echo esc_attr($wallet_key);?>" type="checkbox"<?php echo in_array($wallet_key,$wallet_types) ? ' checked="checked" ' : '';?> value="<?php echo esc_attr($wallet_key);?>" name="login_with_wallet_wallet_types[]" <?php echo $wallet_key != 'injected' && $wallet_key != 'walletconnect' ? 'disabled' : '';?>/>
                              <div class="label-meta">
                                <strong><?php echo esc_html($wallet_type['title']);?></strong>
                                <?php if (isset($wallet_type['desc'])) { ?>
                                  <small><?php echo esc_html($wallet_type['desc']);?></small>
                                <?php } ?>
                              </div>
                            </label>
                          <?php } ?>
                        <?php } ?>
                        <p><?php esc_html_e('Choose the wallet to support on log in', 'login-with-wallet');?></p>
                      </div>
                    </td>
                  </tr>
                  <tr valign="top" id="login_with_wallet_infura_project_id"<?php echo in_array('walletconnect',$wallet_types) ? ' class="show" ' : '';?>>
                    <th scope="row"><?php esc_html_e('Infura Project ID', 'login-with-wallet');?></th>
                    <td>
                      <input type="text" name="login_with_wallet_infura_project_id" value="<?php echo esc_attr($settings['infura_project_id']); ?>" />
                      <a href="https://infura.io/dashboard" target="_blank">Get the ID</a>
                      <p><?php esc_html_e('WalletConnect is autenticated with this API key only', 'login-with-wallet');?></p>
                    </td>
                  </tr>
                  <tr valign="top" id="login_with_wallet_ens_type">
                    <th scope="row"><?php esc_html_e('Handle ENS', 'login-with-wallet');?></th>
                    <td>
                      <select name="login_with_wallet_ens_type" value="<?php echo esc_attr($settings['ens_type']); ?>" >
                        <option value="subgraph"<?php echo $settings['ens_type'] == 'subgraph' ? ' selected="selected"' : '';?>><?php esc_html_e('Using ENS Subgraph', 'login-with-wallet');?></option>
                        <option value="contract"<?php echo $settings['ens_type'] == 'contract' ? ' selected="selected"' : '';?>><?php esc_html_e('Directly from Contract', 'login-with-wallet');?></option>
                      </select>
                      <p><?php esc_html_e('ENS subgraph performs faster. Recommended option is MetaMask. It avoids extra code loads.', 'login-with-wallet');?></p>
                    </td>
                  </tr>
                  <tr valign="top" id="login_with_wallet_whitelisted_accounts">
                    <th scope="row"><?php esc_html_e('Whitelisted Accounts', 'login-with-wallet');?></th>
                    <td>
                      <textarea cols="5" type="text" name="login_with_wallet_whitelisted_accounts"><?php echo isset($settings['whitelisted_accounts']) ? esc_attr($settings['whitelisted_accounts']) : ''; ?></textarea>
                      <p><?php esc_html_e('Only allow these accounts to be registered. Seperate with commas.', 'login-with-wallet');?></p>
                    </td>
                  </tr>
                  <tr valign="top" id="login_with_wallet_optional_email">
                    <th scope="row"><?php esc_html_e('Optional Email', 'login-with-wallet');?></th>
                    <td>
                      <select name="login_with_wallet_optional_email" value="<?php echo isset($settings['optional_email']) ? esc_attr($settings['optional_email']) : ''; ?>" >
                        <option value="yes"<?php echo $settings['optional_email'] == 'yes' ? ' selected="selected"' : '';?>><?php esc_html_e('Yes', 'login-with-wallet');?></option>
                        <option value="no"<?php echo $settings['optional_email'] == 'no' ? ' selected="selected"' : '';?>><?php esc_html_e('No', 'login-with-wallet');?></option>
                      </select>
                      <p><?php esc_html_e('Makes email field optional on WordPress <code>profile page</code> and WooCommerce <code>account details page</code>.', 'login-with-wallet');?></p>
                    </td>
                  </tr>
                  <tr valign="top" id="login_with_wallet_if_wallet_is_disconnected">
                    <th scope="row"><?php esc_html_e('If wallet is disconnected', 'login-with-wallet');?></th>
                    <td>
                      <select name="login_with_wallet_if_wallet_is_disconnected" value="<?php echo isset($settings['if_wallet_is_disconnected']) ? esc_attr($settings['if_wallet_is_disconnected']):''; ?>" >
                        <option value="log_out"<?php echo $settings['if_wallet_is_disconnected'] == 'log_out' ? ' selected="selected"' : '';?>><?php esc_html_e('Log out user', 'login-with-wallet');?></option>
                        <option value="do_nothing"<?php echo $settings['if_wallet_is_disconnected'] == 'do_nothing' ? ' selected="selected"' : '';?>><?php esc_html_e('No action required', 'login-with-wallet');?></option>
                      </select>
                      <p><?php esc_html_e('Force users to authenticate via wallet', 'login-with-wallet');?></p>
                    </td>
                  </tr>
                  <tr valign="top" id="login_with_wallet_force_user">
                    <th scope="row"><?php esc_html_e('Force users', 'login-with-wallet');?></th>
                    <td>
                      <select name="login_with_wallet_force_user" value="<?php echo isset($settings['force_user']) ? esc_attr($settings['force_user']) : ''; ?>" >
                        <option value="no"<?php echo $settings['force_user'] == 'no' ? ' selected="selected"' : '';?>><?php esc_html_e('No', 'login-with-wallet');?></option>
                        <option value="yes"<?php echo $settings['force_user'] == 'yes' ? ' selected="selected"' : '';?>><?php esc_html_e('Yes', 'login-with-wallet');?></option>
                      </select>
                      <p><?php esc_html_e('Force users to authenticate via wallet and not with WordPress username and password', 'login-with-wallet');?></p>
                    </td>
                  </tr>
                  <tr valign="top" id="login_with_wallet_layout_direction">
                    <th scope="row"><?php esc_html_e('Layout Direction', 'login-with-wallet');?></th>
                    <td>
                      <select name="login_with_wallet_layout_direction" value="<?php echo isset($settings['layout_direction']) ? esc_attr($settings['layout_direction']) : ''; ?>" >
                        <option value="horizontal"<?php echo $settings['layout_direction'] == 'horizontal' ? ' selected="selected"' : '';?>><?php esc_html_e('Horizontal', 'login-with-wallet');?></option>
                        <option value="vertical"<?php echo $settings['layout_direction'] == 'vertical' ? ' selected="selected"' : '';?>><?php esc_html_e('Vertical', 'login-with-wallet');?></option>
                      </select>
                      <p><?php esc_html_e('Wallet button(s) layout on the modal', 'login-with-wallet');?></p>
                    </td>
                  </tr>
                  <tr valign="top">
                    <th scope="row"><?php esc_html_e('Modal Intro', 'login-with-wallet');?></th>
                    <td>
                      <textarea name="login_with_wallet_modal_intro"><?php echo isset($settings['modal_intro']) ? esc_attr($settings['modal_intro']) : ''; ?></textarea>
                    </td>
                  </tr>
                  <tr valign="top">
                    <th scope="row"><?php esc_html_e('Terms and Conditions', 'login-with-wallet');?></th>
                    <td>
                      <input type="text" name="login_with_wallet_terms_link" value="<?php echo isset($settings['terms_link']) ? esc_attr($settings['terms_link']) : ''; ?>" />
                    </td>
                  </tr>
                </table>

            <hr/>

            <div class="login-with-wallet-settings-notification"></div>
            <button class="login-with-wallet-settings-save button button-hero button-primary" id="login-with-wallet-admin-save" type="submit"><?php esc_html_e('Save Settings', 'login-with-wallet'); ?></button>
          </div>
        </div>
    </form>
</div>
