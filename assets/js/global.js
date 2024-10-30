var loginWithWalletLoaderInterval = 0;
var loginWithWalletAccountAddress = '';
var loginWithWalletWeb3Loaded = false;
var loginWithWalletBufferLoaded = false;
var loginWithWalletENSLoaded = false;
var loginWithWalletInjected = false;
var loginWithWalletWeb3ProviderLoaded = false;
var loginWithWalletWalletConnectLoaded = false;
var loginWithWalletConnectType = '';
var loginWithWalletInjectedProvider = '';
var loginWithWalletSwitchState = false;

// to check if everything is loaded properly for Injected Provider
// order of conditions matter
function loginWithWalletJSLoaded(type,account = '',data = {}) {
	loginWithWalletLoaderInterval = setInterval(function() {
		if(
			type == 'injected' &&
			loginWithWalletInjected
		) {
				loginWithWalletInitInjected();
				clearInterval(loginWithWalletLoaderInterval);
				loginWithWalletLoaderInterval = 0;
		} else if(
							type == 'walletconnect' &&
							loginWithWalletWeb3Loaded &&
							loginWithWalletWeb3ProviderLoaded &&
							loginWithWalletWalletConnectLoaded
						) {
								loginWithWalletInitWalletConnect();
								clearInterval(loginWithWalletLoaderInterval);
								loginWithWalletLoaderInterval = 0;
			} else if(
							type == 'ens' &&
							loginWithWalletWeb3Loaded &&
							loginWithWalletBufferLoaded &&
							loginWithWalletENSLoaded
						) {
					loginWithWalletInitENSName(account,data);
					clearInterval(loginWithWalletLoaderInterval);
					loginWithWalletLoaderInterval = 0;
				} else if(
								type == 'logout' &&
								loginWithWalletWeb3Loaded &&
								loginWithWalletWeb3ProviderLoaded &&
								loginWithWalletWalletConnectLoaded
							) {
									loginWithWalletCookieLogout();
									clearInterval(loginWithWalletLoaderInterval);
									loginWithWalletLoaderInterval = 0;
					}
		},500);
}

function loginWithWalletSetCookie(cname, cvalue, exdays) {
  const d = new Date();
  d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
  let expires = "expires="+d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function loginWithWalletGetCookie(cname) {
  let name = cname + "=";
  let ca = document.cookie.split(';');
  for(let i = 0; i < ca.length; i++) {
    let c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

// Modal: types: inline, ajax
function loginWithWalletModal(type,args = {},content = '') {
	var closeBtn = jQuery('<button class="login-with-wallet-modal-close" title="'+login_with_wallet.labels.close+'"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="-255 347 100 100" style="enable-background:new -255 347 100 100;" xml:space="preserve"><polygon points="-173.3,434.5 -204.2,403.5 -236.4,435.8 -242.5,429.6 -210.4,397.3 -242.5,365.1 -236.4,359 -204.2,391.1 -173.3,360.3 -167.2,366.4 -198,397.3 -167.2,428.3 "/></svg></button>');
	if(jQuery('.login-with-wallet-modal').length < 1) {
		jQuery('body').append('<div class="login-with-wallet-modal"><div class="login-with-wallet-modal-inner"></div></div>');
	}
	var modal = jQuery('.login-with-wallet-modal-inner');
	if(type == 'inline') {
		modal.html('<div class="login-with-wallet-modal-body"><div class="modal-inline">'+content+'</div></div>');
		modal.append(closeBtn);
	} else {
		jQuery.ajax({
			url: login_with_wallet.ajax_url,
			type: 'post',
			data: {
				action:'login_with_wallet_get_modal',
				type:type,
				args:args,
				_wpnonce:login_with_wallet.nonce
			},
			beforeSend:function() {
				modal.addClass('lww-loading');
			},
			success: function (data) {
				modal.removeClass('lww-loading');
				modal.html(data.data.html);
				modal.append(closeBtn);
			}
		});
	}
}

function loginWithWalletUILoading(toggle = 'on') {
	toggle == 'on' ? jQuery(login_with_wallet.css_selector).addClass('lww-loading') : jQuery(login_with_wallet.css_selector).removeClass('lww-loading')
}

function loginWithWalletUIReset() {
	jQuery(login_with_wallet.css_selector).html('<span>'+login_with_wallet.labels.connectWallet+'</span>').removeClass('connected');
}

function loginWithWalletModalInnerMessage(message,type = '') {
	var elm = jQuery('#login-with-wallet-choices-notification');
	elm.html(message).attr('class','show '+type);
}

function loginWithWalletUpdateUI(account) {
	jQuery(login_with_wallet.css_selector).html('<span class="login-with-wallet-info-wrapper">\
																	<span class="account">'+(login_with_wallet.eth_account_ens ? login_with_wallet.eth_account_ens : account)+'</span>\
																	<span class="wallet-menu">\
																		<span class="disconnect">'+login_with_wallet.labels.disconnect+'</span>\
																	</span>\
															 </span>');
	loginWithWalletUILoading('off')
	jQuery(login_with_wallet.css_selector).addClass('connected');
}

function loginWithWalletAuthUser(account) {
	jQuery.ajax({
		url: login_with_wallet.ajax_url,
		type: 'post',
		data: {
			action:'login_with_wallet_auth_user',
			account:account,
			connect_type:loginWithWalletConnectType,
			_wpnonce:login_with_wallet.nonce
		},
		beforeSend:function() {
			loginWithWalletUILoading('off')
			loginWithWalletModalInnerMessage(login_with_wallet.labels.connecting,'info');
		},
		success: function (data) {
			if(data.success == true) {
				if(data.data.auth_type == 'update' || data.data.auth_type == 'register') {
					if(login_with_wallet.ens_type == 'subgraph') {
						name = loginWithWalletENSSubgraphLookUp(account);
						if(data.data.auth_type == 'update') {
							loginWithWalletUpdateENSName(account,name,data.data.nonce);
						} else if(data.data.auth_type == 'register'){
							loginWithWalletRegisterUser(account,name,data.data.nonce);
						}
					} else {
						loginWithWalletLoadJS('ens');
						loginWithWalletJSLoaded('ens',account,data);
					}
				} else {
					loginWithWalletModalInnerMessage(login_with_wallet.labels.authenticating,'success');
					document.location.reload();
				}

				if(jQuery(login_with_wallet.css_selector).hasClass('wp-login')) {
					document.location.href = login_with_wallet.home_url + '/wp-admin/profile.php';
					return true;
				}
			} else {
				loginWithWalletModal('inline',{},data.data.message);
			}
		},
		error:function(request) {
			var data = jQuery.parseJSON(request.responseText);
			loginWithWalletModal('inline',{},data.data.message);
			loginWithWalletUIReset();
		}
	});
}

function loginWithWalletRegisterUser(account,name,nonce) {
	jQuery.ajax({
		url: login_with_wallet.ajax_url,
		type: 'post',
		data: {
			action:'login_with_wallet_register_user',
			account:account,
			connect_type:loginWithWalletConnectType,
			name:name,
			_wpnonce:nonce
		},
		beforeSend:function() {
			loginWithWalletModalInnerMessage(login_with_wallet.labels.registering,'success');
		},
		success: function (data) {
			if(data.success == true) {
				jQuery('.login-with-wallet-modal').remove();
				if(jQuery(login_with_wallet.css_selector).hasClass('wp-login')) {
					document.location.href = login_with_wallet.home_url + '/wp-admin/profile.php';
					return true;
				}
			 	document.location.reload();
			} else {
				loginWithWalletModal('inline',{},data.data.message);
			}
		},
		error:function(request){
			var data = jQuery.parseJSON(request.responseText);
			loginWithWalletModal('inline',{},data.data.message);
		}
	});
}

function loginWithWalletUpdateENSName(account,name,nonce) {
	jQuery.ajax({
		url: login_with_wallet.ajax_url,
		type: 'post',
		data: {
			action:'login_with_wallet_update_ensname',
			account:account,
			name:name,
			_wpnonce:nonce
		},
		beforeSend:function() {
			loginWithWalletModalInnerMessage(login_with_wallet.labels.updating,'success');
		},
		success: function (data) {
			if(data.success == true) {
				jQuery('.login-with-wallet-modal').remove();
				document.location.reload();
			} else {
				loginWithWalletModal('inline',{},data.data.message);
			}
		},
		error:function(request) {
			var data = jQuery.parseJSON(request.responseText);
			loginWithWalletModal('inline',{},data.data.message);
		}
	});
}

function loginWithWalletDisconnectWP() {
	jQuery.ajax({
			url: login_with_wallet.ajax_url,
			type: 'post',
			async:false,
			data: {
				action:'login_with_wallet_disconnect',
				_wpnonce:login_with_wallet.nonce
			},
			beforeSend:function() {
				loginWithWalletUILoading()
			},
			success: function (data) {
			 loginWithWalletUILoading('off')
			}
	});
}

function loginWithWalletLogoutUser() {
	jQuery.ajax({
		url: login_with_wallet.ajax_url,
		type: 'post',
		data: {
			action:'login_with_wallet_logout_user',
			_wpnonce:login_with_wallet.nonce
		},
		beforeSend:function() {
			loginWithWalletUILoading()
		},
		success: function (data) {
		 loginWithWalletUILoading('off')
		 loginWithWalletUIReset();
		 document.location.reload();
		}
	});
}

function loginWithWalletENSSubgraphLookUp(account) {
	var endpoint = 'https://api.thegraph.com/subgraphs/name/ensdomains/ens';
	var query = {'query':'{domains(where:{owner:"'+account+'"}) {name}}'}
	var name = '';
	jQuery.ajax({
		url:endpoint,
		contentType: "application/json",
		type:'POST',
		data:JSON.stringify(query),
		async:false,
		beforeSend:function() {
			loginWithWalletModalInnerMessage(login_with_wallet.labels.searchingENSName,'pending');
		},
		success:function(data){
			if(data.data.domains.length > 0) {
				name = data.data.domains[0].name;
			}
		},
		error:function(request) {
			console.log(request);
		}
	});
	return name;
}
