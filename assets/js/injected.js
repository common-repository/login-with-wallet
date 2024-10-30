async function loginWithWalletIsInjectedConnected() {
	var connected = false;
	await window.ethereum.request({ method: 'eth_accounts' })
	.then((accounts) => {
		connected = accounts.length > 0 ? true : false;
	});
	return connected;
}

async function loginWithWalletInjectedDisConnect() {
	await window.ethereum.request({
	  method: "eth_requestAccounts",
	  params: [
	    {
	      eth_accounts: {}
	    }
	  ]
	});
}

async function loginWithWalletInjectedConnect() {
	await window.ethereum.enable().catch((err) => {
			jQuery('.login-with-wallet-connect a').removeClass('initializing');
	    if (err.code === 4001) { // User Rejected Request
	      loginWithWalletModal('inline',{},login_with_wallet.labels.errors.cancelled);
	    } else if (err.code === 4100) { // Unauthorized
	      loginWithWalletModal('inline',{},login_with_wallet.labels.errors.unauthorized);
	    } else if (err.code === 4200) { // Unsupported Method
	      loginWithWalletModal('inline',{},login_with_wallet.labels.errors.unsupported);
	    } else if (err.code === 4900) { // Disconnected
	      loginWithWalletModal('inline',{},login_with_wallet.labels.errors.disconnected);
	    } else if (err.code === 4901) { // Chain Disconnected
	      loginWithWalletModal('inline',{},login_with_wallet.labels.errors.chainDisconnected);
	    } else {
				loginWithWalletModal('inline',{},login_with_wallet.labels.errors.pendingOrUnknown);
	    }
	  });

		return loginWithWalletInjectedGetAccount();
}

async function loginWithWalletInjectedGetAccount() {
	var connected_accounts = [];
	await window.ethereum.request({ method: 'eth_accounts' })
	.then((accounts) => {
		connected_accounts = accounts;
	});
	return connected_accounts ? connected_accounts[0] : '';
}

function loginWithWalletInitInjected() {
	if(loginWithWalletInjectedProvider) {

		// checks if account is unlocked. Experimental feature so first check if it exists
		if(typeof window.ethereum._metamask != 'undefined') {
			window.ethereum._metamask.isUnlocked().then((unlocked) => {
				if(!unlocked) {
					loginWithWalletModal('inline',{},login_with_wallet.labels.unlockFirst);
				}
			});
		}
    loginWithWalletIsInjectedConnected().then( (connected) => {
			if(connected) {
				loginWithWalletInjectedGetAccount().then((account) => {
          // check user status on WP
					loginWithWalletAccountAddress = account;
          if(login_with_wallet.auth_status == 'loggedout') {
            loginWithWalletAuthUser(account);
          } else if (!loginWithWalletSwitchState && (login_with_wallet.eth_account != '' && login_with_wallet.eth_account != account)){
						var switchButton = '<button class="login-with-wallet-connect switch">'+login_with_wallet.labels.switchWallet+'</button>'
						loginWithWalletModal('inline',{},login_with_wallet.labels.youHaveChangedYourWallet+switchButton);
						if(login_with_wallet.if_wallet_is_disconnected == 'log_out') {
							loginWithWalletInjectedDisConnect();
	            loginWithWalletLogoutUser();
	            return false;
	          }
          } else {
						loginWithWalletUILoading();
						loginWithWalletUpdateUI(account);
						if(loginWithWalletSwitchState || login_with_wallet.eth_account == '') {
							loginWithWalletAuthUser(account);
						}
					}
        });
			} else {
        if(login_with_wallet.auth_status == 'loggedin' && login_with_wallet.eth_connect_type != '') {
          if(login_with_wallet.if_wallet_is_disconnected == 'log_out') {
            loginWithWalletLogoutUser();
            return false;
          } else if(loginWithWalletConnectType == '') {
						return false;
					}
        }

				loginWithWalletInjectedConnect().then( (account) => {
					if(typeof account != 'undefined' && account != '') {
						loginWithWalletAuthUser(account);
					}
        });
			}
		});
		//
		window.ethereum.on("accountsChanged", (accounts) => {
			 if (accounts.length == 0) {
				 if(login_with_wallet.auth_status == 'loggedin' && login_with_wallet.eth_connect_type != '') {
					 loginWithWalletDisconnectWP();
	 				 loginWithWalletUIReset();
					 if(login_with_wallet.if_wallet_is_disconnected == 'log_out') {
						 loginWithWalletModal('inline',{},login_with_wallet.labels.youHaveDisconnectedYourWalletAndLogoutAccordingly);
						 loginWithWalletLogoutUser();
					 }
				 }
			 } else {
				 if (accounts[0] != loginWithWalletAccountAddress && login_with_wallet.eth_connect_type != '') {
					 loginWithWalletAuthUser(accounts[0]);
				 }
			 }
		});
		//
		// Subscribe to chainId change
		window.ethereum.on("chainChanged", (chainId) => {
		  console.log(chainId);
		});
		//
		// Subscribe to provider connection
		window.ethereum.on("connect", ( info ) => {
		  console.log(info);
		});
		//
		// Subscribe to provider disconnection
		window.ethereum.on("disconnect", (error) => {
		  loginWithWalletLogoutUser();
		});

		jQuery(document).delegate('.disconnect','click',function(e) {
			var if_wallet_is_disconnected = '';
			if(login_with_wallet.if_wallet_is_disconnected == 'log_out') {
				if (!confirm(login_with_wallet.labels.youWillBeLoggedoutAlertInjected)) {
					return false;
				}
			} else {
					if (!confirm(login_with_wallet.labels.youWillBeDisconnectedAlertInjected)) {
						return false;
					}
				}

			loginWithWalletInjectedDisConnect();
			loginWithWalletDisconnectWP();
			loginWithWalletUIReset();
			if(login_with_wallet.if_wallet_is_disconnected == 'log_out') {
				loginWithWalletLogoutUser();
			} else {
				document.location.reload();
			}

		});
	}
}
