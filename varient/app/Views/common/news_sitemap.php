<?= '<?xml version="1.0" encoding="UTF-8"?>' . "\n"; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
    <?php foreach ($posts as $post): ?>
        <url>
            <loc><?= htmlspecialchars(generatePostUrl($post), ENT_XML1, 'UTF-8'); ?></loc>
            <news:news>
                <news:publication>
                    <news:name><?= htmlspecialchars($siteName, ENT_XML1, 'UTF-8'); ?></news:name>
                    <news:language><?= htmlspecialchars($post->lang_short_form, ENT_XML1, 'UTF-8'); ?></news:language>
                </news:publication>
                <news:publication_date><?= date('c', strtotime($post->created_at)); ?></news:publication_date>
                <news:title><?= htmlspecialchars($post->title, ENT_XML1, 'UTF-8'); ?></news:title>
            </news:news>
        </url>
    <?php endforeach; ?>
</urlset>