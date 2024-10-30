<?php

  /**
   * Get and check active wallets from general option
   *
   * $args variable is sending from request's POST via ajax
   * @since     1.0.0
   */
  $settings = get_option(LOGIN_WITH_WALLET_OPTION_NAME);
  $layout_direction = empty($settings['layout_direction']) ? ' horizontal' : ' '.$settings['layout_direction'];
  $wallet_types = empty($settings['wallet_types']) ? [] : $settings['wallet_types'];
  $infura_id = empty($settings['infura_project_id']) ? '' : $settings['infura_project_id'];
  $injected = $args['isInjected'] == 'true' ? true : false;
  $metamask = $args['isMetaMask'] == 'true' ? true : false;
  $coinbase = $args['isCoinBase'] == 'true' ? true : false;
  $niftywallet = $args['isNiftyWallet'] == 'true' ? true : false;
  $eqlwallet = $args['isEQLWallet'] == 'true' ? true : false;
  $framewallet = $args['isFrame'] == 'true' ? true : false; ?>

<div class="login-with-wallet-modal-body">
  <?php do_action('login_with_wallet_modal_before_heading'); ?>
  <h3>
    <?php esc_html_e('Connect a wallet', 'login-with-wallet');?>
  </h3>
  <?php do_action('login_with_wallet_modal_after_heading'); ?>
  <div id="login-with-wallet-choices-notification"></div>
  <div id="alert-error-https"></div>
    <div class="login-with-wallet-choices<?php echo esc_attr($layout_direction);?>">
      <?php

      /**
       * Shows related active wallets
       * Checks general option and users environment for supported wallets
       */
      if ($wallet_types) {
        foreach ($wallet_types as $key => $wallet_type) {
          if ($wallet_type == 'injected'){
            ?>
            <?php if ($eqlwallet) { ?>
              <a href="#<?php echo esc_url($wallet_type);?>" id="eqlwallet" class="injected">
                <i class="logo">
                  <img src="<?php echo LOGIN_WITH_WALLET_URL;?>/assets/images/eql.svg" />
                </i>
                <strong><?php esc_html_e('EQUAL Wallet', 'login-with-wallet');?></strong>
              </a>
            <?php } else if ($coinbase) {?>
              <a href="#<?php echo esc_url($wallet_type);?>" id="coinbase" class="injected">
                <i class="logo">
                  <img src="<?php echo LOGIN_WITH_WALLET_URL;?>/assets/images/coinbase.svg" />
                </i>
                <strong><?php esc_html_e('Coinbase', 'login-with-wallet');?></strong>
              </a>
            <?php } else if ($niftywallet) {?>
              <a href="#<?php echo esc_url($wallet_type);?>" id="nifty" class="injected">
                <i class="logo">
                  <img src="<?php echo LOGIN_WITH_WALLET_URL;?>/assets/images/niftywallet.png" />
                </i>
                <strong><?php esc_html_e('Nifty', 'login-with-wallet');?></strong>
              </a>
            <?php } else if ($framewallet) {?>
              <a href="#<?php echo esc_url($wallet_type);?>" id="frame" class="injected">
                <i class="logo">
                  <img src="<?php echo LOGIN_WITH_WALLET_URL;?>/assets/images/frame.svg" />
                </i>
                <strong><?php esc_html_e('Frame', 'login-with-wallet');?></strong>
              </a>
            <?php } else if ($metamask) { ?>
              <a href="<?php echo esc_url($metamask) ? '#'.esc_url($wallet_type) : 'https://metamask.io/download.html';?>"<?php echo $metamask ? '' : ' target="_blank"';?> id="metamask" class="injected">
                <i class="logo">
                  <img src="<?php echo LOGIN_WITH_WALLET_URL;?>/assets/images/metamask.svg" />
                </i>
                <strong><?php esc_html_e('MetaMask', 'login-with-wallet');?></strong>
              </a>
            <?php } else { ?>
              <a href="https://metamask.io/download.html" target="_blank" id="metamask" class="injected">
                <i class="logo">
                  <img src="<?php echo LOGIN_WITH_WALLET_URL;?>/assets/images/metamask.svg" />
                </i>
                <strong><?php esc_html_e('Download MetaMask', 'login-with-wallet');?></strong>
              </a>
            <?php } ?>
          <?php } ?>
          <?php if ($wallet_type == 'walletconnect' && !empty($infura_id)) { ?>
            <a href="#<?php echo esc_url($wallet_type);?>" id="walletconnect" class="walletconnect">
              <i class="logo">
                <img src="<?php echo LOGIN_WITH_WALLET_URL;?>/assets/images/walletconnect.svg" />
              </i>
              <strong><?php esc_html_e('WalletConnect', 'login-with-wallet');?></strong>
            </a>
          <?php } ?>
        <?php } ?>
      <?php } ?>
      <?php do_action('login_with_wallet_modal_in_choices'); ?>
    </div>
    <?php if (isset($settings['modal_intro']) && !empty($settings['modal_intro'])) { ?>
      <div class="login-with-wallet-choices-terms"><?php echo esc_html($settings['modal_intro']);?></div>
    <?php } ?>
    <?php if (isset($settings['terms_link']) && !empty($settings['terms_link'])) { ?>
      <a href="<?php echo esc_url($settings['terms_link']);?>" class="login-with-wallet-terms-link">
        <?php esc_html_e('How this app uses APIs?s', 'login-with-wallet');?>
        <i>
          <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
          	 viewBox="-255 347 100 100" style="enable-background:new -255 347 100 100;" xml:space="preserve">
          <polygon fill="#FFFFFF" points="-222,366 -216.4,360.2 -179.6,397.2 -216.2,433.8 -221.9,428 -190.8,397.2 "/>
          </svg>
        </i>
      </a>
    <?php } ?>
    <?php do_action('login_with_wallet_modal_footer'); ?>
</div>
