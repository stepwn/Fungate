Contributors: Stephen@swantech.us

Tags: nft, authentication, content protection, web3 login, dApp

Requires at least: 5.0

Tested up to: 6.4.2

Stable tag: 1.0.4

PHP: 8.2

License: GPL-3.0

License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

Discord: https://discord.gg/gqwrFkwcGg


<h1>Fungate</h1>
<figure>
  <img src="https://github.com/stepwn/Fungate/assets/7231316/f0b456fa-226e-47fd-ba5c-3466f605fb30.png" alt="Token Gated Content">
  <figcaption>Token Gated Content</figcaption>
</figure>


<hr>
Fungate is a powerful and easy-to-use WordPress plugin that enables users to gate content based on NFT ownership. With Fungate, you can easily protect your content and offer exclusive access to users who own a specific NFT. Fungate uses PHP and JS to run standalone on your WordPress site's server. (That means its free to use!)


<h2>Features</h2>

Protect content with NFT ownership verification based on token, minter, or NFT ID

Supports WalletConnect for secure authentication

Easy installation and configuration

Safety First!

<h2>Installation</h2>

![image](https://github.com/stepwn/Fungate/assets/7231316/c0d0a594-b735-4ff5-9daa-30ec8bc6b799)

<hr>
 To install Fungate, simply follow these steps:
<ul>
<li>Download <a href="https://github.com/stepwn/Fungate/blob/main/Fungate.zip">Fungate.zip</a>
<br><b>*If you clone the repo, rename the inner folder to "Fungate.zip"</b>

<li>Upload the plugin to your WordPress site

<li>Activate the plugin in your WordPress dashboard

<li>Configure the plugin settings according to your requirements
</ul>

<h2>Configuration</h2> 

After installing Fungate, you'll need to configure the plugin according to your requirements or options. To configure Fungate, follow these steps:
<ol>
<li>Export your Loopring API key -> https://github.com/stepwn/Fungate/assets/7231316/00dad3da-61f8-49de-bfed-2f10adbf918d
<br> Look for "api key" in the exported account.
  
<li>Add your API key to the "Fungate Settings" on the main sidebar of the WordPress admin dashboard

<li>Obtain a ProjectId from https://cloud.walletconnect.com and save it in the Fungate settings page.

If you need any help with configuration, don't hesitate to reach out for support. https://discord.gg/gqwrFkwcGg


## Usage

![Shortcode](https://user-images.githubusercontent.com/7231316/235256376-74517595-6721-4087-919b-c793e913d625.png)
*eg Shortcode placement in the WordPress Page Editor*

---

![Fungate Post Type](https://github.com/stepwn/Fungate/assets/7231316/51c672c0-4d05-4fc8-8fd5-340ba22e71fd)
*Fungate Post Type*


Using Fungate is easy. Here are the basic steps to get started:
<ol>
<li>Create a new post or page in WordPress

<li>Use the [fungate token="tokencollectioncontract" minter="minterethaddress" nft_id="individualNFTID"] shortcode to protect content based on NFT ownership

<li>Customize the shortcode attributes to fit your requirements (token or minter or nft_id is required)

<li>Put your token gated content on the page using the WordPress content editor

<li>At the end of your gated content put the [/fungate] shortcode tag.

<li>Save and publish!

For more detailed instructions and examples, check out our documentation.


<h2>Support</h2> 

If you encounter any issues with Fungate, please don't hesitate to reach out for support. You can find help in the following ways:

Visit our discord https://discord.gg/gqwrFkwcGg

We're here to help, so don't hesitate to reach out if you need assistance.

<h2>Frequently asked questions</h2> 

Q: How does Fungate verify NFT ownership? 

A: Fungate uses the Loopring Rest API to authenticate users and verify NFT ownership once the browser or WalletConnect finishes Public Key Verification.

Q: How Safe is this plugin? 

A: Fungate sets up a local proxy for Loopring API requests so your key is never exposed to the client. Additionally, users are only signing a verification request to prove they own the Eth address they submit, Fungate can not transfer tokens.

Q: How can I give away NFTs?

A: Loopring has a NFT red packet system. you can put the red packet behind a token gate (or not!)

Q: I have no idea whats happening

A: <a href="https://github.com/stepwn/Fungate/blob/main/user-onboarding-guide.md">User Onboarding Guide</a>


<h2>Summary</h2> 

Fungate is the ultimate WordPress plugin for gating content based on NFT ownership. With its powerful features and easy-to-use interface, you can protect your content and offer exclusive access to your audience. Download Fungate today and start gating your content based on NFT ownership!
