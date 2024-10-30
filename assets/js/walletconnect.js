async function loginWithWalletWalletConnectConnect() {
	const WalletConnectProvider = window.WalletConnectProvider.default;
	const walletConnectProvider = new WalletConnectProvider({
      infuraId: login_with_wallet.infura_id, // Required
      qrcode: true
    });
  await walletConnectProvider.enable().catch((err) => {
			jQuery('.login-with-wallet-connect a').removeClass('initializing');
			if(err != 'Error: User closed modal') {
				loginWithWalletModal('inline',{},err);
			} else {
				jQuery('.login-with-wallet-modal-close').click();
			}
    });
	return walletConnectProvider;
}

async function loginWithWalletIsWalletConnectConnected(provider) {
	var connected = false;
	await provider.request({ method: 'eth_accounts' })
	.then((accounts) => {
		connected = accounts.length > 0 ? true : false;
	});
	return connected;
}

async function loginWithWalletWalletConnectDisConnect(provider) {
	await provider.disconnect();
	localStorage.removeItem( 'walletconnect' );
}

async function loginWithWalletWalletConnectDoConnect(provider) {
  await provider.enable();
  return loginWithWalletWalletConnectGetAccount(provider);
}

async function loginWithWalletWalletConnectGetAccount(provider) {
	var connected_accounts = [];
	await provider.request({ method: 'eth_accounts' })
	.then((accounts) => {
		connected_accounts = accounts;
	});
	return connected_accounts[0];
}

function loginWithWalletCookieLogout() {
	loginWithWalletWalletConnectConnect().then((provider) => {
		loginWithWalletIsWalletConnectConnected(provider).then( (connected) => {
			if(connected) {
				loginWithWalletSetCookie('login_with_wallet_logged_out','false',0);
				loginWithWalletWalletConnectDisConnect(provider);
			}
		});
	});
}

function loginWithWalletInitWalletConnect() {

	loginWithWalletWalletConnectConnect().then((provider) => {
		// Subscribe to accounts change
		loginWithWalletIsWalletConnectConnected(provider).then( (connected) => {
			if(connected) {
				loginWithWalletWalletConnectGetAccount(provider).then((account) => {
					// check user status on WP
					loginWithWalletAccountAddress = account;
					if(login_with_wallet.auth_status == 'loggedout') {
						loginWithWalletAuthUser(account);
					} else if (!loginWithWalletSwitchState && (login_with_wallet.eth_account != '' && login_with_wallet.eth_account != account)){
						var switchButton = '<button class="login-with-wallet-connect switch">'+login_with_wallet.labels.switchWallet+'</button>'
						loginWithWalletModal('inline',{},login_with_wallet.labels.youHaveChangedYourWallet+switchButton);
						if(login_with_wallet.if_wallet_is_disconnected == 'log_out') {
							loginWithWalletLogoutUser();
							loginWithWalletWalletConnectDisConnect();
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
				if(login_with_wallet.auth_status == 'loggedin') {
					if(login_with_wallet.if_wallet_is_disconnected == 'log_out') {
						loginWithWalletLogoutUser();
						return false;
					}
				}
				loginWithWalletWalletConnectDoConnect().then( (account) => {
					loginWithWalletAuthUser(account);
				});
			}
		});

		provider.on("accountsChanged", (accounts) => {
		  console.log(accounts);
		});

		// Subscribe to chainId change
		provider.on("chainChanged", (chainId) => {
		  console.log(chainId);
		});

		// Subscribe to session disconnection
		provider.on("disconnect", (code, reason) => {
			if(code == 1000) {
				loginWithWalletDisconnectWP();
				loginWithWalletUIReset();
				if(login_with_wallet.if_wallet_is_disconnected == 'log_out') {
					loginWithWalletModal('inline',{},login_with_wallet.labels.youHaveDisconnectedYourWalletAndLogoutAccordingly);
					loginWithWalletLogoutUser();
				} else {
					loginWithWalletModal('inline',{},login_with_wallet.labels.youHaveDisconnectedYourWallet);
					document.location.reload();
				}
			}
		});

		jQuery(document).delegate('.disconnect','click',function(e) {
			var if_wallet_is_disconnected = '';
			if(login_with_wallet.if_wallet_is_disconnected == 'log_out') {
				if (!confirm(login_with_wallet.labels.youWillBeLoggedoutAlert)) {
					return false;
				}
			} else {
					if (!confirm(login_with_wallet.labels.youWillBeDisconnectedAlert)) {
						return false;
					}
				}

			loginWithWalletWalletConnectDisConnect(provider);
			loginWithWalletDisconnectWP();
			loginWithWalletUIReset();
			if(login_with_wallet.if_wallet_is_disconnected == 'log_out') {
				loginWithWalletLogoutUser();
			} else {
				document.location.reload();
			}

		});
	});
}
