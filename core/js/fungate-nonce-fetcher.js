document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-base-url]').forEach(function(media) {
        let baseUrl = media.getAttribute('data-base-url');
        let accessKey = media.getAttribute('data-access-key');

        fetch(`/wp-json/fungate/v1/nonce?access_key=${accessKey}`)
            .then(response => response.json())
            .then(data => {
                let newUrl = baseUrl + '&nonce=' + data.nonce;
                if (media.tagName.toLowerCase() === 'img') {
                    media.src = newUrl;
                } else { // for audio and video
                    let source = media.querySelector('source');
                    source.src = newUrl;
                    media.load(); // Reload the media element to update the source
                }
            })
            .catch(error => console.error('Error fetching nonce:', error));
    });
});
