<?php if (!empty($hasAccess)): ?>

    <?= $this->section('styles'); ?>
    <link rel="stylesheet" href="<?= base_url('assets/vendor/plyr/plyr.min.css'); ?>"/>
    <?= $this->endSection(); ?>

    <?php if (!empty($postVideo)):
        $videoUrl = getStorageFileUrl($postVideo->file_path, $postVideo->storage);
        $imgUrl = getPostImageUrl($post, 'big'); ?>

        <div id="plyr-wrapper" <?= $isRtl ? 'style="direction: ltr; text-align: left;"' : ''; ?>>
            <video id="player" playsinline controls preload="metadata" poster="<?= esc($imgUrl); ?>">
                <source src="<?= esc($videoUrl); ?>" type="video/mp4">
                <source src="<?= esc($videoUrl); ?>" type="video/webm">
            </video>
        </div>
    <?php elseif (!empty($post->video_embed_code)): ?>
        <div class="post-image post-video">
            <div class="ratio ratio-16x9">
                <iframe src="<?= $post->video_embed_code; ?>" allowfullscreen></iframe>
            </div>
        </div>
    <?php endif; ?>

    <?= $this->section('scripts'); ?>

    <script src="<?= base_url('assets/vendor/plyr/plyr.min.js'); ?>"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            try {
                const e = new Plyr("#player", {ratio: '16:9', controls: ["play-large", "play", "progress", "current-time", "mute", "volume", "settings", "pip", "airplay", "fullscreen"], settings: ["quality", "speed"], quality: {default: 720, options: [720, 576]}, storage: {enabled: !1, key: "plyr"}, ads: {enabled: !1, publisherId: ""}});
                e.on("ready", e => {
                    const t = document.getElementById("plyr-wrapper");
                    t && t.classList.add("visible")
                }), setTimeout(() => {
                    const e = document.getElementById("plyr-wrapper");
                    e && !e.classList.contains("visible") && e.classList.add("visible")
                }, 1e3)
            } catch (e) {
                console.error("Plyr initialization error:", e)
            }
        });
    </script>

    <?= $this->endSection(); ?>

<?php endif; ?>