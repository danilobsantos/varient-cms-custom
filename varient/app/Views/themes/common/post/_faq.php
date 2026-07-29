<?php if (!empty($post->faq) && !empty($post->faq->items) && countItems($post->faq->items) > 0):

    $schemaData = [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => []
    ];

    if (!empty($post->faq->items)) {
        foreach ($post->faq->items as $item) {
            $schemaData['mainEntity'][] = [
                    '@type'          => 'Question',
                    'name'           => $item->q,
                    'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text'  => $item->a
                    ]
            ];
        }
    }
    ?>

    <script type="application/ld+json">
        <?= json_encode($schemaData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>

    </script>

    <div class="faq-section">

        <?php if (!empty($post->faq->title)):
            if ($post->post_format === 'event'): ?>
                <h3 class="event-section-title"><?= esc($post->faq->title); ?></h3>
            <?php else: ?>
                <div class="text-center mb-4">
                    <h3 class="faq-title"><?= esc($post->faq->title); ?></h3>
                </div>
            <?php endif;
        endif; ?>

        <?php if (!empty($post->faq->items)): ?>

            <div class="accordion faq-accordion" id="faqAccordion">

                <?php $isOpenFirst = false;
                foreach ($post->faq->items as $index => $item):
                    $itemId = uniqid();
                    $isOpen = $isOpenFirst && ($index === 0); ?>

                    <div class="accordion-item <?= $isOpen ? 'active-item' : ''; ?>">
                        <h4 class="accordion-header" id="heading<?= $itemId; ?>">
                            <button class="accordion-button <?= $isOpen ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFaq<?= $itemId; ?>" aria-expanded="<?= $isOpen ? 'true' : 'false'; ?>" aria-controls="collapseFaq<?= $itemId; ?>">
                                <span><?= esc($item->q); ?></span>
                                <span class="accordion-icon-wrapper">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m6 9 6 6 6-6"/>
                                    </svg>
                                </span>
                            </button>
                        </h4>
                        <div id="collapseFaq<?= $itemId; ?>" class="accordion-collapse collapse <?= $isOpen ? 'show' : ''; ?>" aria-labelledby="heading<?= $itemId; ?>" data-bs-parent="#faqAccordion">
                            <div class="accordion-body entry-content">
                                <?= esc($item->a); ?>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>