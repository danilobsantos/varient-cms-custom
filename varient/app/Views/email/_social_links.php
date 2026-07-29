<?php if (!empty($socialLinks)):
    foreach ($socialLinks as $link):
        if (!empty($link->url)):
            $iconName = $link->key ?? '';
            $iconName = isset($darkTheme) && $link->key == 'x_twitter' ? $iconName . '-light' : $iconName; ?>
            <td style="padding: 0 10px;">
                <a href="<?= esc($link->url); ?>" target="_blank">
                    <img src="<?= base_url('assets/media/social/' . $iconName . '.png'); ?>" width="24" height="24" alt="" style="display: block; border: 0;">
                </a>
            </td>
        <?php endif;
    endforeach;
endif; ?>