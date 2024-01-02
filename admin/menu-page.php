<?php
defined('ABSPATH') or die('No script kiddies please!');
function fungate_menu_page(){
    fungate_admin_page_render();
    ?>
    <div class="fungate-wrap">
        <h1>Welcome to Fungate!</h1>
        <p>Thank you for using Fungate! This plugin helps you enable Web3 Login and display NFT gated content on your website.</p>
        <p>For more information and updates, please visit:</p>
        <ul>
            <li><a href="https://tawk.to/fungate" target="_blank">Live Chat Support</a></li>
            <li><a href="https://discord.gg/pjRuMTtDZb" target="_blank">Discord Community</a></li>
            <li><a href="https://fungate.io" target="_blank">Fungate.io Website</a></li>
            <li><a href="https://github.com/stepwn/Fungate" target="_blank">GitHub Repository</a></li>
        </ul>
        
        <h2>Plugin Pages</h2>
        <p>You can configure settings using the following buttons:</p>
        <div class="fungate-plugin-buttons">
            <a class="button-primary" href="<?php echo esc_url(admin_url('admin.php?page=fungate-media')); ?>">Manage Protected Media</a>
            <a class="button-primary" href="<?php echo esc_url(admin_url('admin.php?page=fungate-settings')); ?>">Configure Settings</a>
        </div>
        <br>
        <hr>
        <h1>Block Usage</h1>
        <div class="fungate-shortcode-info">
            <p>The Fungate suite includes three distinct blocks: the Fungate, Fungate Media, and Fungate Account blocks. Each serves a specific purpose in managing and displaying content based on user access and interaction.</p>
            
            <h2>Fungate Block</h2>
            <p>The <strong>Fungate block</strong> acts as a wrapper for other blocks. It's designed to control access to the content placed within it. This is particularly useful for gating content behind a token-based system. With a token gate scheduler, you can define when and how the enclosed content is accessible, providing a flexible way to manage your content visibility based on user tokens.</p>
            
            <h2>Fungate Media Block</h2>
            <p>The <strong>Fungate Media block</strong> is designed to securely serve media from a protected folder. This block can be nested inside a Fungate wrapper block, ensuring that only authorized users can access and stream the protected media. It's an ideal solution for hosting exclusive content that you want to keep secure yet accessible to a select audience.</p>
            
            <h2>Fungate Account Block</h2>
            <p>The <strong>Fungate Account block</strong> provides a user interface for WalletConnect functionality. It displays the currently connected account and offers a disconnect option. Users can easily see which account they are logged in with and manage their connection status, enhancing the user experience and providing a clear, interactive way for users to manage their site interactions.</p>

            <h3>How to Use:</h3>
            <ul>
                <li><strong>Fungate Block:</strong> Start by adding the Fungate block where you want to control access. Then, insert the content blocks you wish to gate inside it.</li>
                <li><strong>Fungate Media Block:</strong> Place the Fungate Media block inside a Fungate block to restrict access to the media content. Configure it to point to the media in your protected folder.</li>
                <li><strong>Fungate Account Block:</strong> Add this block to sections of your site where you want to provide account interaction capabilities. It's a good practice to place it near content that requires user interaction or access control.</li>
            </ul>

            <p>By combining these blocks effectively, you can create a dynamic and secure content experience that caters to your audience and content protection needs.</p>
        </div>

        <hr>
        <h1>Shortcode Usage</h1>
        <div class="fungate-shortcode-info">
            <h2>Fungate Shortcode: [fungate][/fungate]</h2>
            <p>The <code>[fungate][/fungate]</code> shortcode allows you to show content only to specific NFT owners.</p>
            <p>The shortcode can be placed in Shortcode Blocks or in any custom HTML space.</p>
            <p><b>Fungate Gutenburg Blocks and Elementor Blocks Coming Soon!</b></p>
            <p>If any of the provided attributes match the attributes of a user's owned NFTs, the content inside the shortcode will be displayed. Otherwise, the content will not be served.</p>
            
            <h3>Identifying Attributes for NFTs:</h3>
            <ul>
                <li><strong>NFT (nft_id or id):</strong> The unique identifier of the NFT.</li>
                <li><strong>Minter (minter or deployer):</strong> The address of the entity that minted the NFT.</li>
                <li><strong>Contract (contract, token, or token contract):</strong> The contract address associated with the NFT.</li>
            </ul>

            <div class="fungate-shortcode-example">
                <h3>Usage:</h3>
                <pre><code>[fungate nft="0xabc123" minter="0xabc123" contract="0xabc123"]Your content here[/fungate]</code></pre>
                <p>* Only one of the nft, minter, or contract attributes needs to be set to work. If multiple are set, access will only be allowed if all the conditions are true.</p>
                <pre><code>[fungate nft="0xabc123"]Your content here[/fungate]</code></pre>

                <h3>Boolean Logic Operators:</h3>
                <p>You can use boolean logic operators to combine multiple conditions in your shortcode:</p>
                <ul>
                    <li><strong>Comma (,):</strong> Acts as an OR operator. Content is shown if any one of the conditions is true.</li>
                    <li><strong>Plus (+):</strong> Acts as an AND operator. Content is shown only if all conditions are true.</li>
                    <li><strong>Asterisk (*) :</strong> Indicates multiple instances. Used for matching multiple values for a single attribute.</li>
                    <li>Parentheses can be used to group conditions for clarity and precedence.</li>
                </ul>
                <p>Example using boolean logic:</p>
                <pre><code>[fungate nft="0xabc123,0xdef456" minter="0x123456+0x654321"]Your content here[/fungate]</code></pre>
                <p>This example shows content to users who own either NFT 0xabc123 OR 0xdef456 AND any NFT from 0x123456 AND any NFT from 0x654321.</p>
            </div>
        </div>
        <div class="fungate-shortcode-info">
            <h2>Fungate Button Shortcode: [fungate_account]</h2>
            <p>The <code>[fungate_account]</code> shortcode allows you to display the WalletConnect account management button. This button enables users to manage their WalletConnect accounts directly from the page where the shortcode is used.</p>
            <p>When clicked, this button will prompt users to connect their WalletConnect-compatible wallets, facilitating easy account management and interaction with your content that requires NFT ownership verification.</p>

            <h3>Usage:</h3>
            <p>Simply insert the shortcode where you want the WalletConnect button to appear:</p>
            <pre><code>[fungate_account]</code></pre>
            <p>This will render the WalletConnect account management button in the specified location.</p>

            <h3>Styling the Button:</h3>
            <p>You can apply custom styles to the button using CSS. The button has a default class that you can target for styling:</p>
            <pre><code>.fungate-walletconnect-button { /* Your custom styles here */ }</code></pre>
            <p>Alternatively, you can add your own class to the shortcode:</p>
            <pre><code>[fungate_account class="your-custom-class"]</code></pre>
            <p>This allows for more flexibility and customization, ensuring the button fits seamlessly with the design of your site.</p>
        </div>

        <div class="fungate-shortcode-info">
        <h2>Fungate Media Shortcode: [fungate_media]</h2>
            <p>The <code>[fungate_media]</code> shortcode allows you to selectively embed/stream protected media (.mp4, .mp3, .jpg, etc).</p>
            <p>First, upload your protected media file <a class="button-primary" href="<?php echo esc_url(admin_url('admin.php?page=fungate-media')); ?>">Manage Protected Media</a></p>
        
            <div class="fungate-shortcode-example">
                <h3>Usage:</h3>
                <pre><code>[fungate_media type="video" src="https://yoursite.com/wp-content/plugins/Fungate/protected-content/your-file.mp4"]</code></pre>
                * Copy the full link to the protected file into the <code>src=""</code> attribute.<br>
                * Use the <code>type=""</code> attribute to your media type: image, video, or audio.
                <p>Fungate will stream the embedded media</p>
            </div>
        </div>
        <div class="fungate-shortcode-info">
        <h2>Fungate Media Download Shortcode: [fungate_media_download]</h2>
            <p>The <code>[fungate_media_download]</code> shortcode allows you to selectively serve protected media (.mp4, .zip, .webm, .obj, etc).</p>
            <p>First, upload your protected media file <a class="button-primary" href="<?php echo esc_url(admin_url('admin.php?page=fungate-media')); ?>">Manage Protected Media</a></p>
        
            <div class="fungate-shortcode-example">
                <h3>Usage:</h3>
                <pre><code>[fungate_media_download src="https://yoursite.com/wp-content/plugins/Fungate/protected-content/your-file.zip"]</code></pre>
                * Copy the full link to the protected file into the <code>src=""</code> attribute.<br>
                * Use the <code>text="Download Button Text"</code> attribute to change the download button text.
                <p>Fungate will display a download button to serve the protected media file</p>
            </div>
        </div>
    </div>
    <?php
}