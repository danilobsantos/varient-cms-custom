<?php if (!empty($hasAccess)):

    $jsPlaylistData = [];

    if (countItems($postAudios) > 0):
        foreach ($postAudios as $audio) {
            $audioUrl = getStorageFileUrl($audio->file_path, $audio->storage);
            $jsPlaylistData[] = [
                    'id'             => $audio->id ?? 0,
                    'name'           => esc(pathinfo($audio->file_name ?? '', PATHINFO_FILENAME)),
                    'artist'         => '',
                    'album'          => '',
                    'url'            => $audioUrl,
                    'cover_art_url'  => getPostImageUrl($post, 'slider'),
                    'duration'       => '',
                    'isDownloadable' => (int)$audio->is_downloadable === 1 ? 1 : 0,
            ];
        }

        $imgUrl = getPostImageUrl($post, 'slider');
        ?>

        <div class="amp-player-wrapper">
            <div id="amplitude-player">

                <div id="amplitude-left">
                    <img src="<?= esc($imgUrl); ?>" class="album-art" alt="<?= esc($post->title, 'attr'); ?>"/>

                    <div id="player-left-bottom">
                        <div id="meta-container">
                            <span data-amplitude-song-info="name" class="song-name"></span>
                            <div class="song-artist-album">
                                <span data-amplitude-song-info="artist"></span>
                            </div>
                        </div>

                        <div id="time-container">
                        <span class="current-time">
                            <span class="amplitude-current-minutes"></span>:<span class="amplitude-current-seconds"></span>
                        </span>
                            <div id="progress-container">
                                <input type="range" class="amplitude-song-slider"/>
                                <progress id="song-played-progress" class="amplitude-song-played-progress"></progress>
                            </div>
                            <span class="duration">
                            <span class="amplitude-duration-minutes"></span>:<span class="amplitude-duration-seconds"></span>
                        </span>
                        </div>

                        <div id="control-container">
                            <div id="repeat-container">
                                <div class="amplitude-repeat control-btn" id="repeat">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m17 2 4 4-4 4"/>
                                        <path d="M3 11v-1a4 4 0 0 1 4-4h14"/>
                                        <path d="m7 22-4-4 4-4"/>
                                        <path d="M21 13v1a4 4 0 0 1-4 4H3"/>
                                    </svg>
                                </div>
                                <div class="amplitude-shuffle control-btn" id="shuffle">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m18 14 4 4-4 4"/>
                                        <path d="m18 2 4 4-4 4"/>
                                        <path d="M2 18h1.973a4 4 0 0 0 3.3-1.7l5.454-8.6a4 4 0 0 1 3.3-1.7H22"/>
                                        <path d="M2 6h1.972a4 4 0 0 1 3.6 2.2"/>
                                        <path d="M22 18h-6.041a4 4 0 0 1-3.3-1.8l-.359-.45"/>
                                    </svg>
                                </div>
                            </div>

                            <div id="central-control-container">
                                <div id="central-controls">
                                    <div class="amplitude-prev control-btn" id="previous">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M4 4a.5.5 0 0 1 1 0v3.248l6.267-3.636c.54-.313 1.232.066 1.232.696v7.384c0 .63-.692 1.01-1.232.697L5 8.753V12a.5.5 0 0 1-1 0z"/>
                                        </svg>
                                    </div>

                                    <div class="amplitude-play-pause" id="play-pause">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="svg-icon icon-play" viewBox="0 0 16 16">
                                            <path d="m11.596 8.697-6.363 3.692c-.54.313-1.233-.066-1.233-.697V4.308c0-.63.692-1.01 1.233-.696l6.363 3.692a.802.802 0 0 1 0 1.393"/>
                                        </svg>

                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="svg-icon icon-pause" viewBox="0 0 16 16">
                                            <path d="M5.5 3.5A1.5 1.5 0 0 1 7 5v6a1.5 1.5 0 0 1-3 0V5a1.5 1.5 0 0 1 1.5-1.5m5 0A1.5 1.5 0 0 1 12 5v6a1.5 1.5 0 0 1-3 0V5a1.5 1.5 0 0 1 1.5-1.5"/>
                                        </svg>
                                    </div>

                                    <div class="amplitude-next control-btn" id="next">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="svg-icon" viewBox="0 0 16 16">
                                            <path d="M12.5 4a.5.5 0 0 0-1 0v3.248L5.233 3.612C4.693 3.3 4 3.678 4 4.308v7.384c0 .63.692 1.01 1.233.697L11.5 8.753V12a.5.5 0 0 0 1 0z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div id="volume-container">
                                <div class="volume-controls">
                                    <div class="amplitude-mute">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="icon-volume" viewBox="0 0 16 16">
                                            <path d="M11.536 14.01A8.47 8.47 0 0 0 14.026 8a8.47 8.47 0 0 0-2.49-6.01l-.708.707A7.48 7.48 0 0 1 13.025 8c0 2.071-.84 3.946-2.197 5.303z"/>
                                            <path d="M10.121 12.596A6.48 6.48 0 0 0 12.025 8a6.48 6.48 0 0 0-1.904-4.596l-.707.707A5.48 5.48 0 0 1 11.025 8a5.48 5.48 0 0 1-1.61 3.89z"/>
                                            <path d="M8.707 11.182A4.5 4.5 0 0 0 10.025 8a4.5 4.5 0 0 0-1.318-3.182L8 5.525A3.5 3.5 0 0 1 9.025 8 3.5 3.5 0 0 1 8 10.475zM6.717 3.55A.5.5 0 0 1 7 4v8a.5.5 0 0 1-.812.39L3.825 10.5H1.5A.5.5 0 0 1 1 10V6a.5.5 0 0 1 .5-.5h2.325l2.363-1.89a.5.5 0 0 1 .529-.06"/>
                                        </svg>

                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="icon-mute" viewBox="0 0 16 16">
                                            <path d="M6.717 3.55A.5.5 0 0 1 7 4v8a.5.5 0 0 1-.812.39L3.825 10.5H1.5A.5.5 0 0 1 1 10V6a.5.5 0 0 1 .5-.5h2.325l2.363-1.89a.5.5 0 0 1 .529-.06m7.137 2.096a.5.5 0 0 1 0 .708L12.207 8l1.647 1.646a.5.5 0 0 1-.708.708L11.5 8.707l-1.646 1.647a.5.5 0 0 1-.708-.708L10.793 8 9.146 6.354a.5.5 0 1 1 .708-.708L11.5 7.293l1.646-1.647a.5.5 0 0 1 .708 0"/>
                                        </svg>
                                    </div>
                                    <input type="range" class="amplitude-volume-slider"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="amplitude-right" class="vr-scrollbar"></div>
            </div>
        </div>
    <?php endif; ?>

    <?= $this->section('scripts'); ?>

    <script src="<?= base_url('assets/vendor/amplitudejs/amplitude.min.js'); ?> "></script>

    <script>
        (function () {
            "use strict";

            const songList = <?= json_encode($jsPlaylistData); ?>;
            const showDownloadBtn = <?= ($config->audio_download_button == 1) ? 'true' : 'false'; ?>;

            const downloadConfig = {
                url: "<?= base_url('download-file'); ?>",
                csrfName: "<?= csrf_token(); ?>",
                csrfHash: "<?= csrf_hash(); ?>"
            };

            // Creates a temporary form and submits it to download the file
            function downloadAudio(id) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = downloadConfig.url;
                form.style.display = 'none';

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = downloadConfig.csrfName;
                csrfInput.value = downloadConfig.csrfHash;
                form.appendChild(csrfInput);

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = id;
                form.appendChild(idInput);

                const typeInput = document.createElement('input');
                typeInput.type = 'hidden';
                typeInput.name = 'file_type';
                typeInput.value = 'audio';
                form.appendChild(typeInput);

                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }

            function generatePlaylist() {
                try {
                    const container = document.getElementById('amplitude-right');
                    if (!container) return;

                    let html = '';
                    const icons = {
                        music: `<svg width="16" height="16" fill="currentColor" class="icon-music" viewBox="0 0 16 16"><path d="M9 13c0 1.105-1.12 2-2.5 2S4 14.105 4 13s1.12-2 2.5-2 2.5.895 2.5 2"/><path fill-rule="evenodd" d="M9 3v10H8V3z"/><path d="M8 2.82a1 1 0 0 1 .804-.98l3-.6A1 1 0 0 1 13 2.22V4L8 5z"/></svg>`,
                        listPlay: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="icon-list-play" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M6.79 5.093A.5.5 0 0 0 6 5.5v5a.5.5 0 0 0 .79.407l3.5-2.5a.5.5 0 0 0 0-.814z"/></svg>`,
                        playing: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="icon-playing" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8.5 2a.5.5 0 0 1 .5.5v11a.5.5 0 0 1-1 0v-11a.5.5 0 0 1 .5-.5m-2 2a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5m4 0a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5m-6 1.5A.5.5 0 0 1 5 6v4a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m8 0a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m-10 1A.5.5 0 0 1 3 7v2a.5.5 0 0 1-1 0V7a.5.5 0 0 1 .5-.5m12 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0V7a.5.5 0 0 1 .5-.5"/></svg>`,
                        download: `<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>`
                    };

                    songList.forEach((song, index) => {
                        let downloadHtml = '';
                        if (showDownloadBtn && song.isDownloadable) {
                            downloadHtml = `
                            <div class="download-btn" title="Download" onclick="event.stopPropagation(); downloadAudio('${song.id}');">
                               ${icons.download}
                            </div>`;
                        }

                        html += `
                        <div class="song amplitude-song-container amplitude-play-pause" data-amplitude-song-index="${index}">
                            <div class="song-now-playing-icon-container">
                               ${icons.music}
                               ${icons.listPlay}
                               ${icons.playing}
                            </div>
                            <div class="song-meta-data">
                              <span class="song-title">${song.name}</span>
                              <span class="song-artist">${song.artist}</span>
                            </div>
                            <span class="song-duration">${song.duration}</span>
                            ${downloadHtml}
                        </div>`;
                    });

                    container.innerHTML = html;
                } catch (err) {
                    console.error("Playlist generation failed:", err);
                }
            }

            function initAmplitude() {
                try {
                    if (typeof Amplitude === 'undefined') return;
                    window.downloadAudio = downloadAudio;

                    generatePlaylist();

                    Amplitude.init({
                        "songs": songList,
                        "preload": "metadata",
                        "continue_next": true,
                        "waveforms": {
                            "sample_rate": 50
                        }
                    });
                } catch (e) {
                    console.error("AmplitudeJS init error:", e);
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initAmplitude);
            } else {
                initAmplitude();
            }
        })();
    </script>

    <?= $this->endSection(); ?>

<?php endif; ?>
