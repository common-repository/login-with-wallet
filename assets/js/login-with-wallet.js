// Loads JSs dynamically
function loginWithWalletLoadJS(type = 'injected') {
	var scripts = [];
	if(type == 'injected') {
			scripts = [{
				handle : 'injected',
				url: login_with_wallet.template_url+'assets/js/injected.js',
				type: 'text/javascript'
			}];
		} else if(type == 'ens'){
			scripts = [
				{
					handle : 'web3',
					url: login_with_wallet.template_url+'assets/js/web3.min.js',
					type: 'text/javascript'
				},
				{
					handle : 'buffer', // WalletConnectProvider
					url: login_with_wallet.template_url+'assets/js/buffer.min.js',
					type: 'text/javascript'
				},
				{
					handle : 'ens',
					url: login_with_wallet.template_url+'assets/js/ens.js',
					type: 'text/javascript'
				}
			];
			} else {
				scripts = [
					{
						handle : 'web3',
						url: login_with_wallet.template_url+'assets/js/web3.min.js',
						type: 'text/javascript'
					},
					{
						handle : 'web3provider', // WalletConnectProvider
						url: login_with_wallet.template_url+'assets/js/web3-provider.min.js',
						type: 'text/javascript'
					},
					{
						handle : 'walletconnect',
						url: login_with_wallet.template_url+'assets/js/walletconnect.js',
						type: 'text/javascript'
					}
				];
		}
		// append before this script
		var script_id = "login-with-wallet-script-js";
		var plugin_script = document.getElementById(script_id);
		scripts.forEach((script, i) => {
			var script_tag = document.createElement("script");
			if(script.type != '') {
				script_tag.type = script.type;
			}

			if(script.handle == 'web3' && !loginWithWalletWeb3Loaded) {
				plugin_script.before(script_tag);
			} else if(script.handle == 'buffer' && !loginWithWalletBufferLoaded) {
				plugin_script.before(script_tag);
			} else if(script.handle == 'ens' && !loginWithWalletENSLoaded) {
				plugin_script.before(script_tag);
			} else if(script.handle == 'injected' && !loginWithWalletInjected) {
				plugin_script.before(script_tag);
			} else if(script.handle == 'web3provider' && !loginWithWalletWeb3ProviderLoaded) {
				plugin_script.before(script_tag);
			} else if(script.handle == 'walletconnect' && !loginWithWalletWalletConnectLoaded) {
				plugin_script.before(script_tag);
			}

	    script_tag.onload = function(){
				if(script.handle == 'web3') {
					loginWithWalletWeb3Loaded = true;
				} else if(script.handle == 'buffer') {
					loginWithWalletBufferLoaded = true;
				} else if(script.handle == 'injected') {
					loginWithWalletInjected = true;
				} else if(script.handle == 'ens') {
					loginWithWalletENSLoaded = true;
				} else if(script.handle == 'web3provider') {
					loginWithWalletWeb3ProviderLoaded = true;
				} else if(script.handle == 'walletconnect') {
					loginWithWalletWalletConnectLoaded = true;
				}
	    };

			script_tag.src = script.url;
		});

 }

function loginWithWalletInit() {
	loginWithWalletInjectedProvider = (typeof window.ethereum !== 'undefined') ? true : false;
	if(login_with_wallet.auth_status == 'loggedin' && login_with_wallet.eth_connect_type != '') {
		loginWithWalletLoadJS(login_with_wallet.eth_connect_type);
		loginWithWalletJSLoaded(login_with_wallet.eth_connect_type);
	}

	if( login_with_wallet.auth_type == 'only_login' || login_with_wallet.auth_type == 'login_and_register') {
		jQuery('#loginform').append(
			'<button class="button button-hero login-with-wallet-connect wp-login">'+login_with_wallet.labels.loginWithWallet+'</button>'
		);
	}

	if( login_with_wallet.auth_type == 'only_register' || login_with_wallet.auth_type == 'login_and_register') {
		jQuery('#registerform').append(
			'<button class="button button-hero login-with-wallet-connect wp-login">'+login_with_wallet.labels.registerWithWallet+'</button>'
		);
	}

	var cookie = loginWithWalletGetCookie('login_with_wallet_logged_out');
	if(cookie != '' && cookie == 'walletconnect' && login_with_wallet.auth_status == 'loggedout') {
		loginWithWalletLoadJS('logout');
		loginWithWalletJSLoaded('logout');
	}

}

jQuery(document).ready(function ($) {
	// checks the environment
	loginWithWalletInit();

	// opens login modal
	$(document).delegate(login_with_wallet.css_selector,'click',function(e) {
		e.preventDefault();
		if($(this).is(":not(.connected)") && !$(e.target).hasClass('disconnect')) {
			var args = {
				isInjected:false,
				isMetaMask:false,
				isCoinBase:false,
				isEQLWallet:false,
				isSafe:false,
				isNiftyWallet:false,
				isTrust:false,
				isDapper:false,
				isCipher:false,
				isimToken:false,
				isStatus:false,
				isTokenry:false,
				isOpera:false,
				isFrame:false,
				isLiquality:false,
			};
			args.isInjected = (typeof window.ethereum !== 'undefined') ? true : false;
			if(args.isInjected) {
				args.isInjected = window.ethereum.isMetaMask ? true : false;
				args.isMetaMask = window.ethereum.isMetaMask ? true : false;
				args.isCoinBase = window.ethereum.isCoinbaseWallet ? true : false;
				args.isNiftyWallet = window.ethereum.isNiftyWallet ? true : false;
				args.isEQLWallet = window.ethereum.isEQLWallet ? true : false;
				args.isFrame = window.ethereum.isFrame ? true : false;
			}
			if($(this).hasClass('switch')) {
				loginWithWalletSwitchState = true;
			}
			loginWithWalletModal('choose_wallet',args);
		}
	});

	// closes login with wallet's modals
 $(document).delegate('.login-with-wallet-modal','click',function(e) {
    if(!$(e.target).hasClass('login-with-wallet-modal')) {
      return false;
    }
    $('.login-with-wallet-modal').remove();
  });

 $(document).delegate('.login-with-wallet-modal-close','click',function(e) {
    $('.login-with-wallet-modal').remove();
  });


	// load proper JSes then run the callbacks
 $(document).delegate(".login-with-wallet-choices a[target!='_blank']",'click',function(e) {
		e.preventDefault();
		var t = $(this);
		t.addClass('lww-loading');
		loginWithWalletModalInnerMessage(login_with_wallet.labels.initializing);
		if(t.hasClass('injected')) {
			loginWithWalletConnectType = 'injected';
			loginWithWalletLoadJS(loginWithWalletConnectType);
			loginWithWalletJSLoaded(loginWithWalletConnectType);
		} else if(t.hasClass('walletconnect')) {
			loginWithWalletConnectType = 'walletconnect';
			loginWithWalletLoadJS(loginWithWalletConnectType);
			loginWithWalletJSLoaded(loginWithWalletConnectType);
		}

  });

});
