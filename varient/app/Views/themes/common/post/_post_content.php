<?php
$arrayContent = [];
$articleAd1 = [];
$articleAd2 = [];

if (!empty($adSpaces)) {
    foreach ($adSpaces as $item) {
        if ($item->ad_space === 'in_article_1') {
            $articleAd1 = $item;
        }
        if ($item->ad_space === 'in_article_2') {
            $articleAd2 = $item;
        }
    }
}

if (!empty($post->content)) {
    $arrayContent = explode('</p>', $post->content);
}

$paywallType = !empty($premiumMembership->paywallAppearance) ? $premiumMembership->paywallAppearance : 'fade';
$fadeParagraphLimit = 1;

if (!$hasAccess && $paywallType === 'hard') {
    echo loadCommonView('premium/_paywall', [
        'restrictionType' => $restrictionType,
        'paywallType'     => 'hard'
    ]);
} else {
    if (!empty($arrayContent)) {
        if (!$hasAccess && $paywallType === 'fade') {
            echo '<div style="position: relative; overflow: hidden; min-height: 520px;">';
        }

        $i = 1;
        foreach ($arrayContent as $p) {
            if (trim($p) !== '') {
                echo $p . '</p>';
            }

            if (!empty($articleAd1) && !empty($articleAd1->paragraph_number) && $articleAd1->paragraph_number == $i) {
                echo loadCommonView('partials/_ad_spaces', ['adSpace' => 'in_article_1', 'class' => 'mb-3 mt-2']);
            }
            if (!empty($articleAd2) && !empty($articleAd2->paragraph_number) && $articleAd2->paragraph_number == $i) {
                echo loadCommonView('partials/_ad_spaces', ['adSpace' => 'in_article_2', 'class' => 'mb-3 mt-2']);
            }

            if (!$hasAccess && $paywallType === 'fade' && $i === $fadeParagraphLimit) {
                echo loadCommonView('premium/_paywall', [
                    'restrictionType' => $restrictionType,
                    'paywallType'     => 'fade'
                ]);
                break;
            }
            $i++;
        }

        if (!$hasAccess && $paywallType === 'fade') {
            echo '</div>';
        }
    }
}
?>