<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('<?= base_url('pwa-sw.js');?>')
                .then(function (registration) {
                }, function (err) {
                    console.log('ServiceWorker registration failed: ', err);
                }).catch(function (err) {
                console.log(err);
            });
        });
    } else {
        console.log('service worker is not supported');
    }
</script>