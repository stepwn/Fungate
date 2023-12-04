"use strict";

window.addEventListener('load', async () => {
    // Create and append the script for Web3Modal
    var script = document.createElement('script');
    script.src = plugin_dir + '/assets/bundle.js';

    // Add an event listener to the script to detect when it's loaded
    script.onload = async () => {
        // Listening for account changes
    const metaDescription = document.querySelector("meta[name='description']");
    const siteDescription = metaDescription ? metaDescription.content : "Default Description";
     // Now you can safely call window.initializeWeb3
     await window.initializeWeb3(wc_projectId, {
        name: document.title,
        description: siteDescription,
        url: window.location.origin,
        icons: [window.location.origin + "/favicon.ico"],
        themeVariables: {
            '--w3m-color-mix': 'red',
            '--w3m-color-mix-strength': 40
          }
    });
    window.watchAccount(account => {
        window.fungate_eth_address = account.address;
        Array.from(document.getElementsByClassName("fungateButton")).forEach(element => {
            element.innerText = "Unlock Content";
        });        
        if(account.isDisconnected){
            Array.from(document.getElementsByClassName("fungateButton")).forEach(element => {
                element.innerText = "Connect Wallet";
            });   
            if(fungateKey){
                // Make an AJAX call to WordPress to log out
                jQuery.post('/wp-admin/admin-ajax.php', { action: 'web3modal_logout' }, function(response) {
                    console.log('Logged out:', response);
                    if(response == true){
                        window.location.href = window.location.href;
                    }
                });
            }
        }
        else{
            if(!fungateKey && !window.nonce){
                //sendLoginRequest(account.address);
            }
        }
    });
};

// Append the script to the document head
document.head.appendChild(script);
});

async function fungate_unlock(el) {
    if (window.fungate_eth_address) {
        if(!fungateKey && !window.nonce){
            await sendLoginRequest(window.fungate_eth_address);
        }
    }
    else{
        window.web3Modal.open();
    }
}

async function sendLoginRequest(userAddress) {
    const modal = document.createElement('div');
    modal.id = 'fungate_sign_modal';
    modal.style.display = 'none'; // Initially hidden
    modal.innerHTML = `
      <div class="fungate-sign-modal-content">
        <h2>Sign request sent.</h2>
        <p>Check your wallet to sign your login nonce.</p>
        <p id="fungate_sign_nonce"></p>
        <button onclick="document.getElementById('fungate_sign_modal').style.display='none'">Close</button>
      </div>
    `;
    document.body.appendChild(modal);

  // Request nonce from the server
    fetch(plugin_dir+'/login-user.php?address='+encodeURIComponent(userAddress))
        .then(response => response.json())
        .then(data => {
            const nonce = data.nonce;
            window.fungate_nonce = nonce;
            // display modal with prompt to sign
            document.getElementById('fungate_sign_modal').style.display = "block";
            document.getElementById('fungate_sign_nonce').innerText = nonce;
            // Prompt user to sign the nonce
            window.signUserMessage(nonce).then(signedNonce => {
                // Setup AJAX request to send signed nonce
                const xhr = new XMLHttpRequest();
                xhr.open('POST', plugin_dir+'/login-user.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                // Handle response
                xhr.onload = function() {
                    if (this.status === 200) {
                        // Handle success
                        console.log('Login successful:', this.responseText);
                        document.getElementById('fungate_sign_nonce').innerText = "Login Successful!";
                        //document.getElementById('fungate_sign_modal').style.display = "none";
                        window.location.href = window.location.href;
                    } else {
                        // Handle error
                        console.error('Login failed:', this.status, this.responseText);
                        document.getElementById('fungate_sign_nonce').innerText = "Login Failed.";
                    }
                };

                // Send request with user address and signed nonce
                xhr.send('address=' + encodeURIComponent(userAddress) + '&signedMessage=' + encodeURIComponent(signedNonce) + '&nonceFromClient=' + encodeURIComponent(nonce));
            });
        })
        .catch(error => console.error('Error fetching nonce:', error));
}
window.sendLoginRequest = sendLoginRequest;