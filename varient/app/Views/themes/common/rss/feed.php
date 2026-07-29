<?= '<?xml version="1.0" encoding="UTF-8"?>' . "\n"; ?>
<rss version="2.0"
     xmlns:dc="http://purl.org/dc/elements/1.1/"
     xmlns:media="http://search.yahoo.com/mrss/"
     xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:atom="http://www.w3.org/2005/Atom">

    <channel>
        <title><![CDATA[<?= cleanXmlData($feedName ?? ''); ?>]]></title>
        <link><?= xmlEscape($feedURL ?? base_url()); ?></link>
        <description><![CDATA[<?= cleanXmlData($description ?? ''); ?>]]></description>
        <atom:link href="<?= xmlEscape(current_url()); ?>" rel="self" type="application/rss+xml"/>
        <?php if (!empty($pageLanguage)): ?>
            <dc:language><?= xmlEscape($pageLanguage); ?></dc:language>
        <?php endif; ?>

        <?php if (!empty($baseSettings->copyright)): ?>
            <dc:rights><![CDATA[<?= cleanXmlData($baseSettings->copyright); ?>]]></dc:rights>
        <?php endif; ?>

        <?php
        if (!empty($posts)):
            foreach ($posts as $post):

                // URL Generation
                $postBaseUrl = langBaseUrl();

                if (isset($post->lang_id, $config->site_lang) && $post->lang_id == $config->site_lang) {
                    $postBaseUrl = rtrim(base_url(), '/') . '/';
                } elseif (!empty($post->lang_short_form)) {
                    $postBaseUrl = rtrim(base_url($post->lang_short_form), '/') . '/';
                }

                $postUrl = generatePostUrl($post, $postBaseUrl);

                // pubDate Security check
                $timestamp = strtotime($post->created_at ?? '');
                if (!$timestamp) {
                    continue;
                }

                // Keywords (Google News max ~5 recommended)
                $keywords = [];
                if (!empty($post->keywords)) {
                    foreach (array_slice(explode(',', $post->keywords), 0, 5) as $kw) {
                        $kw = trim($kw);
                        if ($kw !== '') {
                            $keywords[] = cleanXmlData($kw);
                        }
                    }
                }

                // Image Logic
                $imageUrl = '';
                $fileSize = 49398; // Fallback size
                $imageType = 'image/jpeg';

                if (!empty($post->image_url)) {
                    $imageUrl = strtok($post->image_url, '?');
                } else {
                    $imageUrl = getPostImageUrl($post, 'big');
                    if (!empty($post->image_big)) {
                        $path = FCPATH . $post->image_big;
                        if (is_file($path)) {
                            $size = filesize($path);
                            if ($size > 0) {
                                $fileSize = $size;
                            }
                        }
                    }
                }
                ?>
                <item>
                    <title><![CDATA[<?= cleanXmlData($post->title ?? ''); ?>]]></title>

                    <link><?= xmlEscape($postUrl); ?></link>
                    <guid isPermaLink="true"><?= xmlEscape($postUrl); ?></guid>

                    <description><![CDATA[<?= cleanXmlData($post->summary ?? ''); ?>]]></description>

                    <?php if ($imageUrl): ?>
                        <enclosure url="<?= xmlEscape($imageUrl); ?>" length="<?= (int)$fileSize; ?>" type="<?= $imageType; ?>"/>

                        <media:content url="<?= xmlEscape($imageUrl); ?>" medium="image"/>
                    <?php endif; ?>

                    <pubDate><?= date(DATE_RSS, $timestamp); ?></pubDate>

                    <dc:creator><![CDATA[<?= cleanXmlData($post->author_username ?? ''); ?>]]></dc:creator>

                    <?php if (!empty($keywords)): ?>
                        <media:keywords><![CDATA[<?= implode(', ', $keywords); ?>]]></media:keywords>
                    <?php endif; ?>

                    <?php if ($showPostContent && !empty($post->content)): ?>
                        <content:encoded><![CDATA[<?= cleanXmlData($post->content); ?>]]></content:encoded>
                    <?php endif; ?>
                </item>
            <?php endforeach;
        endif; ?>
    </channel>
</rss>