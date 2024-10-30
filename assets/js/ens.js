async function loginWithWalletENSReverseLookUp(account,provider) {
	web3 = new Web3(provider);
  var lookup = account.toLowerCase().substr(2) + '.addr.reverse'
  var ResolverContract = await web3.eth.ens.getResolver(lookup);
  var nh = loginWithWalletNameHash(lookup,web3);
  try {
    var ensname = await ResolverContract.methods.name(nh).call((name) => ensname = name);
    // lookup should match too
    if (ensname && ensname.length) {
      const verifiedAddress = await web3.eth.ens.getAddress(ensname);
      if (verifiedAddress && verifiedAddress.toLowerCase() === account.toLowerCase()) {
        return ensname;
      }
    }
  } catch(e) {
    console.log(e);
  }
  return '';
}


async function loginWithWalletGetENSName(account,provider = null){
	var name = '';
	name = await loginWithWalletENSReverseLookUp(account,provider);
	return name;
}

function loginWithWalletInitENSName(account,data) {
	let web3 = new Web3(Web3.givenProvider);
	loginWithWalletGetENSName(account,web3).then((name) => {
		if(data.data.auth_type == 'update') {
			loginWithWalletUpdateENSName(account,name,data.data.nonce);
		} else if(data.data.auth_type == 'register') {
			loginWithWalletRegisterUser(account,name,data.data.nonce);
		}
	});
}
