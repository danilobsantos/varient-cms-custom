<div id="newsTicker" class="newsticker-wrapper w-100 mx-auto d-flex align-items-center overflow-hidden position-relative">
    <div class="ticker-label text-white d-flex align-items-center justify-content-center flex-shrink-0 gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M5.52.359A.5.5 0 0 1 6 0h4a.5.5 0 0 1 .474.658L8.694 6H12.5a.5.5 0 0 1 .395.807l-7 9a.5.5 0 0 1-.873-.454L6.823 9.5H3.5a.5.5 0 0 1-.48-.641l2.5-8.5z"/>
        </svg>
        <span class="d-none d-md-block"><?= trans("breaking_news"); ?></span>
    </div>

    <div class="flex-grow-1 h-100 overflow-hidden position-relative ps-3 pe-2">
        <ul class="news-list list-unstyled m-0 w-100" id="newstickerList">
            <?php foreach ($breakingNews as $item): ?>
                <li class="news-item">
                    <a href="<?= generatePostUrl($item); ?>"<?= postUrlNewTab($item); ?> class="text-decoration-none text-truncate">
                        <?= esc($item->title); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="d-flex align-items-center gap-1 flex-shrink-0">
        <div class="nav-sm-buttons">
            <button class="btn-prev" id="newstickerPrevBtn" aria-label="newsticker prev">
                <i class="vr-icon vri-angle-left"></i>
            </button>
            <button class="btn-next" id="newstickerNextBtn" aria-label="newsticker next">
                <i class="vr-icon vri-angle-right"></i>
            </button>
        </div>
    </div>
</div>