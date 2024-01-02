document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('fungate-media-upload-form');

    uploadForm.addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission

        const formData = new FormData(uploadForm);

        fetch(fungateData.upload_url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-WP-Nonce': fungateData.nonce
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            window.location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error uploading file');
        });
    });
     // Listen for clicks on elements with the class 'copy-link-button'
        document.querySelectorAll('.copy-link-button').forEach(button => {
            button.addEventListener('click', function(event) {
                // Prevent the default action
                event.preventDefault();

                // Get the URL from the data-url attribute of the button
                var fileUrl = button.getAttribute('data-url');

                // Create a temporary text area to hold the URL
                var textArea = document.createElement('textarea');
                textArea.value = fileUrl;

                // Prevent this element from affecting the layout
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';

                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();

                try {
                    // Copy the text from the text area to the clipboard
                    var successful = document.execCommand('copy');
                    var msg = successful ? 'successful' : 'unsuccessful';
                    console.log('Copying text command was ' + msg);
                    alert('Copied to Clipboard: ' + fileUrl);
                } catch (err) {
                    console.error('Oops, unable to copy', err);
                    alert('Failed to Copy');
                }

                // Clean up the temporary text area
                document.body.removeChild(textArea);
            });
        });

        // Handle delete buttons
    document.querySelectorAll('.delete-button').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();

            // Confirm before deleting
            if (!confirm('Are you sure you want to delete this file?')) {
                return;
            }

            const file = button.getAttribute('data-file'); // Assuming the file name is stored in this attribute
            const formData = new FormData();
            formData.append('file', file);

            fetch(fungateData.delete_url, { // Ensure you have a delete_url in your localized script data
                method: 'POST',
                body: formData,
                headers: {
                    'X-WP-Nonce': fungateData.nonce
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                console.log(data); // Handle success
                alert('File deleted successfully');

                // Optionally, remove the file element from the list or refresh the page
                button.closest('li').remove();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting file');
            });
        });
    });
});
