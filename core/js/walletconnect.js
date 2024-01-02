"use strict";

window.addEventListener('load', async () => {
    // Create and append the script for Web3Modal
    const script = document.createElement('script');
    script.src = plugin_dir + '/assets/bundle.js';

    // Add an event listener to the script to detect when it's loaded
    script.onload = async () => {
        // Initialize Web3Modal with project ID and configuration
        await window.initializeWeb3(wc_projectId, {
            name: document.title,
            description: document.querySelector("meta[name='description']")?.content || "Default Description",
            url: window.location.origin,
            icons: [window.location.origin + "/favicon.ico"],
            themeVariables: {
                '--w3m-color-mix': 'red',
                '--w3m-color-mix-strength': 40
            }
        });

        // Watch account changes
        window.watchAccount(account => {
            window.fungate_eth_address = account.address;
            document.querySelectorAll(".fungateButton").forEach(element => {
                element.innerText = account.isDisconnected ? "Connect Wallet" : "Sign in Web3";
            });

            if (account.isDisconnected && fungateKey) {
                // Log out via REST API
                fetch('/wp-json/fungate/v1/logout', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                })
                .then(response => response.json())
                .then(response => {
                    console.log('Logged out:', response);
                    window.location.reload();
                })
                .catch(error => console.error('Logout error:', error));
            } else if (!fungateKey && !window.nonce) {
                // Send login request if needed
                
            }
        });
    };

    // Append the script to the document head
    document.head.appendChild(script);
});

async function fungate_unlock() {
    if (window.fungate_eth_address) {
        if (!fungateKey && !window.nonce) {
            await sendLoginRequest(window.fungate_eth_address);
        }
    } else {
        window.web3Modal.open();
    }
}

async function sendLoginRequest(userAddress) {
    const modal = document.createElement('div');
    modal.id = 'fungate_sign_modal';
    modal.style.display = 'none';
    modal.innerHTML = `
        <div class="fungate-sign-modal-content">
            <h2>Sign request sent.</h2>
            <p>Check your wallet to sign your login nonce.</p>
            <p id="fungate_sign_nonce"></p>
            <button onclick="document.getElementById('fungate_sign_modal').style.display='none'">Close</button>
        </div>
    `;
    document.body.appendChild(modal);

    // Request nonce from the server via REST API
    const nonceResponse = await fetch(`/wp-json/fungate/v1/generate-nonce?address=${encodeURIComponent(userAddress)}`);
    const nonceData = await nonceResponse.json();

    if (nonceData.error) {
        console.error('Error fetching nonce:', nonceData.error);
        return;
    }

    const nonce = nonceData;
    window.fungate_nonce = nonce;
    document.getElementById('fungate_sign_modal').style.display = "block";
    document.getElementById('fungate_sign_nonce').innerText = nonce;

    // Prompt user to sign the nonce
    const signedNonce = await window.signUserMessage(nonce);

    // Send signed nonce via REST API
    const loginResponse = await fetch('/wp-json/fungate/v1/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ address: userAddress, signedMessage: signedNonce, nonceFromClient: nonce })
    });
    const loginData = await loginResponse.json();

    if (loginData) {
        console.log('Login successful:', loginData);
        document.getElementById('fungate_sign_nonce').innerText = "Login Successful!";
        window.location.reload();
    } else {
        console.error('Login failed:', loginData);
        document.getElementById('fungate_sign_nonce').innerText = "Login Failed.";
    }
}

window.fungate_unlock = fungate_unlock;
window.sendLoginRequest = sendLoginRequest;
