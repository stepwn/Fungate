=== Fungate ===
Contributors: swantech
Tags: nft, blockchain, web3, walletconnect, ethereum, loopring, content gating, membership, media protection, woocommerce, buddypress
Requires at least: 6.0
Tested up to: 6.4.2
Requires PHP: 7.4+
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Fungate is a comprehensive WordPress plugin that integrates blockchain technology for secure Web3 logins, NFT-based content gating, and seamless integration with WooCommerce and BuddyPress. Learn more at fungate.io.

== Description ==

Fungate revolutionizes content management and access on WordPress websites by leveraging blockchain technology. Users can log in using their blockchain wallets, and website owners can control content access through NFT or other token ownership. This plugin supports various blockchain networks, including Ethereum and Loopring, and integrates seamlessly with WooCommerce and BuddyPress for enhanced eCommerce and community features. For more detailed information and guidance, visit [fungate.io](https://fungate.io).

Fungate requires php-gmp to be installed on your server. If you cannot install it, you can obtain a license to handle ethereum signature verification remotely via our api. 

**Key Features:**
- **Fungate Blocks**: Easily gate content by using the dedicated Fungate blocks.
- **Web3 Login**: Secure user authentication via blockchain wallets.
- **NFT-Gated Content**: Control access to posts, pages, and WooCommerce products based on NFT ownership.
- **Protected Media**: Stream and download media files for specified NFT owners.
- **Dynamic Role Assignment**: Assign WordPress roles based on NFT holdings.
- **BuddyPress Integration**: Enhance your community with NFT-based group memberships.
- **WooCommerce Integration**: Token-gated products and coupons. Crypto payments coming soon!
- **Shortcode Integration**: Easily manage content with intuitive shortcodes.

Fungate is ideal for creators, communities, and businesses looking to build a Web3 presence and offer exclusive content to their NFT holders. Discover more at [fungate.io](https://fungate.io).

== Installation ==

1. Upload the `fungate` directory to your `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the Fungate settings page to configure the plugin.

For detailed installation instructions, visit [fungate.io/installation](https://fungate.io/installation).

== Configuration ==

Configure WalletConnect Project ID and Loopring API Key in the plugin settings. Visit [fungate.io/configuration](https://fungate.io/configuration) for step-by-step guidance.

== Usage ==

- **[fungate][/fungate]**: Restrict content access based on NFT ownership.
- **[fungate_media]**: Embed and stream protected media.
- **[fungate_media_download]**: Offer protected media downloads.

For usage examples and tips, check out [fungate.io/usage](https://fungate.io/usage).

== Frequently Asked Questions ==

= How does Fungate integrate with blockchain technology? =

Fungate uses WalletConnect for user authentication and interacts with blockchain networks to verify NFT ownership. Learn more at [fungate.io/faq](https://fungate.io/faq).

= Can I use Fungate without technical blockchain knowledge? =

Absolutely! Fungate is user-friendly. Visit [fungate.io/support](https://fungate.io/support) for assistance.

== Screenshots ==

1. Fungate settings page.
2. NFT-gated content setup example.
3. Protected media management interface.

== Changelog ==

= 1.0.0 =
- Initial release: Web3 login, NFT content gating, media protection, role management, WooCommerce and BuddyPress integration, shortcode support.

== Upgrade Notice ==

= 1.0.0 =
Welcome to Fungate 1.0.0! For installation and usage instructions, visit [fungate.io](https://fungate.io).

## Undocumented use of a 3rd Party or External Service

We value transparency and want to ensure that our users are aware of the external services integrated into this plugin. To comply with these guidelines and for your legal protection, we are disclosing the use of 3rd party services in a clear and informative manner.

### External Service Integration

This plugin relies on the following third-party services to enhance its functionality:

#### WalletConnect Verification Service

- **Purpose:** This plugin utilizes the WalletConnect Verification Service to enable secure wallet verification for users.
- **Service URL:** [WalletConnect Verification Service](https://walletconnect.com)

For detailed information on WalletConnect Verification Service's terms of use and privacy policies, please refer to the following links:

- [Terms of Use](https://walletconnect.com/terms-of-service)
- [Privacy Policy](https://walletconnect.com/privacy-policy)

### Ethereum Providers

We utilize Ethereum providers to interact with the Ethereum blockchain. These providers include:

- **WAGMI (We All Gonna Make It)**
  - **Service URL:** [WAGMI](https://wagmi.sh)
- **Default Ethereum Providers** [Etherscan](https://etherscan.io)

Please note that the use of these Ethereum providers is for specific purposes, such as transaction processing and blockchain interaction. We are committed to ensuring the privacy and security of your data. By using this plugin, you acknowledge and agree to the terms of use and privacy policies of the external services mentioned above.


#### Loopring API

- **Purpose:** The Loopring API is used to fetch user NFT balances.
- **Service URL:** [Loopring API](https://loopring.io/#/)
- [Privacy Policy](https://loopring.io/#/document/privacy_en.md)

For detailed information on Loopring API's terms of use and privacy policies, please refer to the official website of Loopring.

### Domain(s) mentioned in the readme file

We also want to make you aware of the following domains mentioned in this readme file:

- [Fungate.io Licensing Service](https://fungate.io/wp-content/plugins/fungate/core/license/signing-endpoint.php)
- [Fungate.io Chainhopper Download](https://fungate.io/wp-content/plugins/fungate/core/license/download_chainhopper.php?nonce=)

Please note that the use of these third-party services is for specific purposes, as described above. We are committed to ensuring the privacy and security of your data. By using this plugin, you acknowledge and agree to the terms of use and privacy policies of the external services mentioned above.

For any further questions or concerns regarding the use of external services, please don't hesitate to contact us.

