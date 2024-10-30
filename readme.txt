=== Login With Wallet - Authenticate using your Ethereum wallet ===
Contributors: roveteam
Tags: ethereum, crypto, login, authentication, social-login
Requires at least: 5.0
Tested up to: 5.9
Stable tag: 1.0.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Log in using your Ethereum wallet. This plugin enables you to register and log in to WordPress only using your Ethereum wallet.
A remarkable number of injected wallets are supported, such as MetaMask, Coinbase, NiftyWallet, EQual Wallet, etc., or you can easily connect your wallet by scanning a QRcode using WalletConnect.

= What is the Login with Wallet WordPress plugin? =
As a website administrator and marketer, you’re always looking for ways to reduce friction in your customer experience. From discovering your brand to receiving an order confirmation email, customers expect a smooth buying experience and exactly zero unnecessary steps.
One common inconvenience on membership sites and ecommerce sites is the need to create user accounts. User accounts engage customers and simplify the buying process...as long as they get past the hurdle of creating an account.
For this reason, Login with Wallet can be an indispensable feature on your site. Login with Wallet allows a user to create and sign in to an account on your website using their cryptocurrency wallets, like MetaMask or WalletConnect. This way, users don’t need to make an entirely new account just for your site.
Login with Wallet updates often to stay current with the APIs it works with. It’s also perfectly compatible with WooCommerce.

= Features =
* Login with any Ethereum wallet
* WordPress login/register page support
* WooCommerce forms are supported (Login, Register, Lost Password)
* ENS Names support
* Shortcode
* Widget
* CSS Selectors to activate any element on the page
* Whitelist addresses
* Control over Authentication
* Control over connected wallet status
* Force users to only use their wallets instead of username and password
* Modal Layout
* Hooks to customize modal interface
* Fully optimized on loading dependencies

= Supported wallets =
* Injected Wallets:
-- MetaMask
-- Coinbase
-- NiftyWallet
-- Trust
-- Safe
-- Dapper
-- Cipher
-- imToken
-- Status
-- Tokenry
-- Opera
-- Frame
* WalletConnect

= How does it control the authentication? =
Login With Wallet is an alternative to the WordPress login and registration process. It automatically adds the Login with Wallet functionality to the WordPress Login Page. Enabling the WooCommerce Option from the Settings page of the plugin will also enable the WooCommerce Login and Registration Forms.
Manual settings are always possible such as shortcode, widget, and CSS selectors.

== Installation ==
You can install Login With Wallet plugin automatically or manually.

= Automatic installation =
Automatic installation is the easiest option -- WordPress will handles the file transfer, and you won’t need to leave your web browser. To do an automatic installation of Login With Wallet, log in to your WordPress dashboard, navigate to the Plugins menu, and click “Add New.”
In the search field type “Login With Wallet” then click “Search Plugins.” Once you’ve found us, you can view details about it such as the point release, rating, and description. Most importantly of course, you can install it by! Clicking “Install Now,” and WordPress will take it from there.

= Manual installation =
 * Place the repository in wp-content/plugins/`
 * Activate the plugin in WordPress > Plugins > Login With Wallet > Activate
 * Configure your settings in WordPress > Settings > Login With Wallet

== Upgrade Notice ==
PLEASE make sure to configure Login With Wallet first (Navigation: WP Dashboard / Settings / Login With Wallet)

== Frequently Asked Questions ==

= How does it work? =
Authentication is simple; you can use your wallet. Basically, you can register, login, or connect your wallet and authenticate. You can control and limit the registration and login process.

=  Can CryptoCurrency wallets be used for authentication? =
Your wallet address is unique and only you have access to it, so just like social login, you can log in with your wallet address.

= Is it safe to use Login with Wallet? =
Yes! Login With Wallet just obtains the account address of the user's wallet. Any action will need users' permission.

= GDPR =
Login With Wallet will only store the user's wallet address to authenticate. The data will be removed once the user disconnects.

Note: we only use wallet addresses, but the permission includes other scopes! This is because, currently, there isn’t a way to request a specific scope.

== Screenshots ==

1. Authentication modal
2. Modal notifications
3. WalletConnect QR code
4. WP login form
5. Widget
6. Shortcode
7. Woocommerce login / register forms
8. Woocommerce lostpassword form
9. Connected state
10. Settings page
11. WP my profile page


== Changelog ==

= 1.0 =
* Initial version
