<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = [
            'db_host'     => $_POST['db_host'],
            'db_user'     => $_POST['db_user'],
            'db_password' => $_POST['db_password'],
            'db_name'     => $_POST['db_name']
    ];
    try {
        $connection = new mysqli($data['db_host'], $data['db_user'], $data['db_password'], $data['db_name']);
        if ($connection->connect_error) {
            $error = "Failed to connect to database, please check your database credentials!";
        } else {
            $connection->query("SET CHARACTER SET utf8mb4");
            $connection->query("SET NAMES utf8mb4");

            update($connection);
            $success = 'The update has been successfully completed! Please delete the "update_database.php" file.';
            $connection->close();
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

function runQuery($sql)
{
    global $connection;
    $result = mysqli_query($connection, $sql);

    // If the query fails, throw an Exception instead of killing the script
    if ($result === false) {
        throw new Exception("SQL Error: " . mysqli_error($connection) . " | Failed Query: " . $sql);
    }

    return $result;
}

function dropIndexIfExists($tableName, $indexName)
{
    global $connection;
    try {
        $checkSql = "SELECT COUNT(1) as index_exists 
                     FROM INFORMATION_SCHEMA.STATISTICS 
                     WHERE table_schema = DATABASE() 
                     AND table_name = '$tableName' 
                     AND index_name = '$indexName'";

        $result = $connection->query($checkSql);

        if ($result) {
            $row = $result->fetch_assoc();
            if ($row['index_exists'] > 0) {
                $connection->query("ALTER TABLE `$tableName` DROP INDEX `$indexName`");
            }
        }
    } catch (\Throwable $e) {
    }
}

function dropAllIndexesExceptFulltext($tableName)
{
    global $connection;

    try {
        $checkSql = "SELECT DISTINCT INDEX_NAME 
                     FROM INFORMATION_SCHEMA.STATISTICS 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = '$tableName' 
                     AND INDEX_NAME != 'PRIMARY' 
                     AND INDEX_TYPE != 'FULLTEXT'";

        $result = $connection->query($checkSql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $indexName = $row['INDEX_NAME'];
                $connection->query("ALTER TABLE `$tableName` DROP INDEX `$indexName`");
            }
        }
    } catch (\Throwable $e) {
    }
}

function addIndexesIfNotExists($tableName, $indexes)
{
    global $connection;
    try {
        $existingIndexes = [];
        $checkSql = "SELECT DISTINCT INDEX_NAME 
                     FROM INFORMATION_SCHEMA.STATISTICS 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = '$tableName'";

        $result = $connection->query($checkSql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $existingIndexes[] = $row['INDEX_NAME'];
            }
        }

        $addParts = [];
        foreach ($indexes as $indexName => $columns) {
            if (!in_array($indexName, $existingIndexes)) {
                $addParts[] = "ADD INDEX `$indexName` ($columns)";
            }
        }

        if (!empty($addParts)) {
            $alterQuery = "ALTER TABLE `$tableName` " . implode(", ", $addParts);
            $connection->query($alterQuery);
        }

    } catch (\Throwable $e) {
    }
}

function convertSerializedToJson($serializedData)
{
    if (empty($serializedData)) {
        return '';
    }

    try {
        $unserialized = @unserialize($serializedData);
        if ($unserialized === false && $serializedData !== 'b:0;') {
            return '';
        }

        $json = json_encode($unserialized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $json;

    } catch (\Throwable $e) {
        return '';
    }
}

function update($connection)
{
    updateFrom21To22($connection);
    updateFrom22To23($connection);
    updateFrom23To24($connection);
    updateFrom24To30($connection);
}

function updateFrom21To22()
{
    $tblPostPollVotes = "CREATE TABLE `post_poll_votes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `post_id` int(11) DEFAULT NULL,
    `question_id` int(11) DEFAULT NULL,
    `answer_id` int(11) DEFAULT NULL,
    `user_id` int(11) DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    runQuery($tblPostPollVotes);
    runQuery("ALTER TABLE general_settings ADD COLUMN `post_format_poll` TINYINT(1) DEFAULT 1");
    runQuery("ALTER TABLE general_settings ADD COLUMN `image_file_format` varchar(30) DEFAULT 'JPG'");
    runQuery("ALTER TABLE general_settings ADD COLUMN `google_news` TINYINT(1) DEFAULT 0");
    runQuery("ALTER TABLE posts ADD COLUMN `is_poll_public` TINYINT(1) DEFAULT 0");
    runQuery("ALTER TABLE quiz_answers ADD COLUMN `total_votes` INT DEFAULT 0");
    runQuery("ALTER TABLE settings ADD COLUMN `tiktok_url` varchar(500)");
    runQuery("ALTER TABLE users ADD COLUMN `tiktok_url` varchar(500)");
    runQuery("ALTER TABLE users ADD COLUMN `personal_website_url` varchar(500)");
    runQuery("UPDATE general_settings SET `version` = '2.2' WHERE id = 1;");
    sleep(1);
    //update role names
    runQuery("UPDATE roles_permissions SET `role_name` = 'a:1:{i:0;a:2:{s:7:\"lang_id\";s:1:\"1\";s:4:\"name\";s:5:\"Admin\";}}' WHERE `role` = 'admin';");
    runQuery("UPDATE roles_permissions SET `role_name` = 'a:1:{i:0;a:2:{s:7:\"lang_id\";s:1:\"1\";s:4:\"name\";s:9:\"Moderator\";}}' WHERE `role` = 'moderator';");
    runQuery("UPDATE roles_permissions SET `role_name` = 'a:1:{i:0;a:2:{s:7:\"lang_id\";s:1:\"1\";s:4:\"name\";s:6:\"Author\";}}' WHERE `role` = 'author';");
    runQuery("UPDATE roles_permissions SET `role_name` = 'a:1:{i:0;a:2:{s:7:\"lang_id\";s:1:\"1\";s:4:\"name\";s:4:\"User\";}}' WHERE `role` = 'user';");
    //add new translations
    $p = array();
    $p["ad_space_index_top"] = "Index (Top)";
    $p["ad_space_index_bottom"] = "Index (Bottom)";
    $p["ad_space_post_top"] = "Post Details (Top)";
    $p["ad_space_post_bottom"] = "Post Details (Bottom)";
    $p["ad_space_posts_top"] = "Posts (Top)";
    $p["ad_space_posts_bottom"] = "Posts (Bottom)";
    $p["ad_space_in_article"] = "In-Article";
    $p["image_file_format"] = "Image File Format";
    $p["personal_website_url"] = "Personal Website URL";
    $p["poll_exp"] = "Get user opinions about something";
    $p["total_votes"] = "Total Votes";
    $p["google_news"] = "Google News";
    $p["generate_feed_url"] = "Generate Feed URL";
    $p["limit"] = "Limit";
    $p["google_news_exp"] = "According to Google News rules, there can be a maximum of 1000 publications in an XML file. Therefore, it is not recommended to increase this limit.";
    $p["google_news_cache_exp"] = "This system uses cache system. So the records in your XML file will be automatically updated every 15 minutes.";
    $p["accept_cookies"] = "Accept Cookies";
    addTranslations($p);
    //delete old translations
    runQuery("DELETE FROM language_translations WHERE `label`='add_subcategory';");
    runQuery("DELETE FROM language_translations WHERE `label`='subcategories';");
    //add indexes
    runQuery("ALTER TABLE audios ADD INDEX idx_user_id (user_id);");
    runQuery("ALTER TABLE comments ADD INDEX idx_user_id (user_id);");
    runQuery("ALTER TABLE files ADD INDEX idx_user_id (user_id);");
    runQuery("ALTER TABLE followers ADD INDEX idx_following_id (following_id);");
    runQuery("ALTER TABLE followers ADD INDEX idx_follower_id (follower_id);");
    runQuery("ALTER TABLE images ADD INDEX idx_user_id (user_id);");
    runQuery("ALTER TABLE payouts ADD INDEX idx_user_id (user_id);");
    runQuery("ALTER TABLE poll_votes ADD INDEX idx_poll_id (poll_id);");
    runQuery("ALTER TABLE poll_votes ADD INDEX idx_user_id (user_id);");
    runQuery("ALTER TABLE post_audios ADD INDEX idx_post_id (post_id);");
    runQuery("ALTER TABLE post_audios ADD INDEX idx_audio_id (audio_id);");
    runQuery("ALTER TABLE post_files ADD INDEX idx_post_id (post_id);");
    runQuery("ALTER TABLE post_files ADD INDEX idx_file_id (file_id);");
    runQuery("ALTER TABLE post_gallery_items ADD INDEX idx_post_id (post_id);");
    runQuery("ALTER TABLE post_images ADD INDEX idx_post_id (post_id);");
    runQuery("ALTER TABLE post_pageviews_month ADD INDEX idx_post_user_id (post_user_id);");
    runQuery("ALTER TABLE post_poll_votes ADD INDEX idx_post_id (post_id);");
    runQuery("ALTER TABLE post_poll_votes ADD INDEX idx_question_id (question_id);");
    runQuery("ALTER TABLE post_poll_votes ADD INDEX idx_user_id (user_id);");
    runQuery("ALTER TABLE post_poll_votes ADD INDEX idx_answer_id (answer_id);");
    runQuery("ALTER TABLE post_sorted_list_items ADD INDEX idx_post_id (post_id);");
    runQuery("ALTER TABLE quiz_answers ADD INDEX idx_question_id (question_id);");
    runQuery("ALTER TABLE quiz_images ADD INDEX idx_user_id (user_id);");
    runQuery("ALTER TABLE quiz_questions ADD INDEX idx_post_id (post_id);");
    runQuery("ALTER TABLE quiz_results ADD INDEX idx_post_id (post_id);");
    runQuery("ALTER TABLE reactions ADD INDEX idx_post_id (post_id);");
    runQuery("ALTER TABLE reading_lists ADD INDEX idx_post_id (post_id);");
    runQuery("ALTER TABLE reading_lists ADD INDEX idx_user_id (user_id);");
    runQuery("ALTER TABLE videos ADD INDEX idx_user_id (user_id);");
}

function updateFrom22To23()
{
    runQuery("ALTER TABLE categories DROP COLUMN `show_at_homepage`;");
    runQuery("ALTER TABLE categories ADD COLUMN `show_on_homepage` TINYINT(1) DEFAULT 1");
    runQuery("ALTER TABLE general_settings ADD COLUMN `post_format_table_of_contents` TINYINT(1) DEFAULT 1");
    runQuery("ALTER TABLE general_settings ADD COLUMN `post_format_recipe` TINYINT(1) DEFAULT 1");
    runQuery("ALTER TABLE general_settings ADD COLUMN `delete_images_with_post` TINYINT(1) DEFAULT 0");
    runQuery("ALTER TABLE general_settings ADD COLUMN `sticky_sidebar` TINYINT(1) DEFAULT 0");
    runQuery("ALTER TABLE posts ADD COLUMN `link_list_style` varchar(255)");
    runQuery("ALTER TABLE posts ADD COLUMN `recipe_info` TEXT");
    runQuery("ALTER TABLE posts ADD COLUMN `post_data` TEXT");
    runQuery("ALTER TABLE post_sorted_list_items ADD COLUMN `parent_link_num` INT DEFAULT 0");
    runQuery("ALTER TABLE settings ADD COLUMN `whatsapp_url` varchar(500)");
    runQuery("ALTER TABLE settings ADD COLUMN `discord_url` varchar(500)");
    runQuery("ALTER TABLE settings ADD COLUMN `twitch_url` varchar(500)");
    runQuery("ALTER TABLE users ADD COLUMN `whatsapp_url` varchar(500)");
    runQuery("ALTER TABLE users ADD COLUMN `discord_url` varchar(500)");
    runQuery("ALTER TABLE users ADD COLUMN `twitch_url` varchar(500)");
    runQuery("UPDATE general_settings SET `version` = '2.3' WHERE id = 1;");

    //add new translations
    $p = array();
    $p["progressive_web_app"] = "Progressive Web App (PWA)";
    $p["table_of_contents"] = "Table of Contents";
    $p["table_of_contents_exp"] = "List of links based on the headings";
    $p["add_table_of_contents"] = "Add Table of Contents";
    $p["table_of_contents_items"] = "Table Of Contents Items";
    $p["update_table_of_contents"] = "Update Table of Contents";
    $p["link_list_style"] = "Link List Style";
    $p["number"] = "Number";
    $p["circle"] = "Circle";
    $p["link_type"] = "Link Type";
    $p["level_1"] = "Level 1";
    $p["level_2"] = "Level 2";
    $p["level_3"] = "Level 3";
    $p["recipe"] = "Recipe";
    $p["recipe_exp"] = "A list of ingredients and directions for cooking";
    $p["show_list_style_post_text"] = "Show List Style in Post Text";
    $p["add_recipe"] = "Add Recipe";
    $p["ingredients"] = "Ingredients";
    $p["add_new"] = "Add New";
    $p["nutritional_information"] = "Nutritional Information ";
    $p["recipe_video"] = "Recipe video";
    $p["value"] = "Value";
    $p["ingredient"] = "Ingredient";
    $p["prep_time"] = "Prep Time";
    $p["cook_time"] = "Cook Time";
    $p["difficulty"] = "Difficulty";
    $p["easy"] = "Easy";
    $p["intermediate"] = "Intermediate";
    $p["advanced"] = "Advanced";
    $p["directions"] = "Directions";
    $p["serving"] = "Serving";
    $p["update_recipe"] = "Update Recipe";
    $p["info_about_recipe"] = "Information About the Recipe";
    $p["minute_short"] = "min";
    $p["delete_images_with_post"] = "Delete Images Along with Post";
    $p["sticky_sidebar"] = "Sticky Sidebar";
    $p["number_short_thousand"] = "k";
    $p["number_short_million"] = "m";
    $p["number_short_billion"] = "b";
    $p["ingredient_ex"] = "Example: 1 tablespoon olive oil";
    $p["nutritional_ex"] = "Example: Protein 34g";
    $p["show_on_homepage"] = "Show on Homepage";
    addTranslations($p);

    runQuery("DELETE FROM language_translations WHERE `label`='show_at_homepage';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_cron_scheduled';");
}

function updateFrom23To24()
{
    global $connection;

    runQuery("TRUNCATE TABLE ci_sessions");
    runQuery("RENAME TABLE tags TO tags1;");
    runQuery("RENAME TABLE quiz_images TO post_item_images;");
    runQuery("RENAME TABLE post_sorted_list_items TO post_list_items;");

    $tblTags = "CREATE TABLE `tags` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `tag` varchar(255) DEFAULT NULL,
            `tag_slug` varchar(255) DEFAULT NULL,
            `lang_id` int(11) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    $tblPostTags = "CREATE TABLE `post_tags` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `tag_id` int(11) DEFAULT NULL,
            `post_id` int(11) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    $tblPostSelections = "CREATE TABLE `post_selections` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `post_id` int(11) DEFAULT NULL,
            `selection_type` varchar(30) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    $tblRoles = "CREATE TABLE `roles` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `role_name` text DEFAULT NULL,
            `permissions` text DEFAULT NULL,
            `is_default` tinyint(1) DEFAULT 0,
            `is_super_admin` tinyint(1) DEFAULT 0
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    runQuery($tblTags);
    runQuery($tblPostTags);
    runQuery($tblPostSelections);
    runQuery($tblRoles);

    runQuery("ALTER TABLE categories CHANGE `name_slug` `slug` varchar(255);");
    runQuery("ALTER TABLE categories ADD COLUMN `category_status` TINYINT(1) DEFAULT 1;");
    runQuery("ALTER TABLE general_settings ADD COLUMN `pwa_logo` TEXT;");
    runQuery("ALTER TABLE general_settings ADD COLUMN `static_cache_system` TINYINT(1) DEFAULT 0;");
    runQuery("ALTER TABLE general_settings ADD COLUMN `newsletter_image` varchar(255);");
    runQuery("ALTER TABLE general_settings ADD COLUMN `human_verification` varchar(255);");
    runQuery("ALTER TABLE general_settings ADD COLUMN `ai_writer` TEXT;");
    runQuery("ALTER TABLE general_settings ADD COLUMN `google_indexing_api` TINYINT(1) DEFAULT 0;");
    runQuery("ALTER TABLE general_settings ADD COLUMN `payout_methods` TEXT;");
    runQuery("ALTER TABLE general_settings ADD COLUMN `bulk_post_upload_for_authors` TINYINT(1) DEFAULT 1;");
    runQuery("ALTER TABLE general_settings ADD COLUMN `logo_size` varchar(30);");
    runQuery("ALTER TABLE general_settings ADD COLUMN `routes` TEXT");
    runQuery("ALTER TABLE payouts ADD COLUMN `status` TINYINT(1) DEFAULT 1;");
    runQuery("ALTER TABLE posts CHANGE `title_slug` `slug` varchar(500);");
    runQuery("ALTER TABLE posts CHANGE `summary` `summary` TEXT;");

    runQuery("ALTER TABLE post_pageviews_month ADD COLUMN `visit_hash` varchar(255);");
    runQuery("ALTER TABLE settings ADD COLUMN `social_media_data` TEXT;");
    runQuery("ALTER TABLE users ADD COLUMN `social_media_data` TEXT;");
    runQuery("ALTER TABLE users ADD COLUMN `role_id` INT(11) DEFAULT 3;");
    runQuery("ALTER TABLE users ADD COLUMN `payout_methods` TEXT;");
    runQuery("ALTER TABLE post_item_images ADD COLUMN `item_type` varchar(30) DEFAULT 'quiz';");

    //insert roles
    runQuery("INSERT INTO `roles` (`id`, `role_name`, `permissions`, `is_default`, `is_super_admin`) VALUES
    (1, 'a:1:{i:0;a:2:{s:7:\"lang_id\";s:1:\"1\";s:4:\"name\";s:11:\"Super Admin\";}}', '', 1, 1),
    (2, 'a:1:{i:0;a:2:{s:7:\"lang_id\";s:1:\"1\";s:4:\"name\";s:6:\"Author\";}}', 'add_post,admin_panel', 1, 0),
    (3, 'a:1:{i:0;a:2:{s:7:\"lang_id\";s:1:\"1\";s:4:\"name\";s:6:\"Member\";}}', '', 1, 0);");

    //insert new font
    runQuery("INSERT INTO `fonts` ( `font_name`, `font_key`, `font_url`, `font_family`, `font_source`, `has_local_file`, `is_default`) VALUES
    ('Source Sans 3', 'source-sans-3', NULL, 'font-family: \"Source Sans 3\", Helvetica, sans-serif', 'local', 1, 0);");

    //set settings
    $result = runQuery("SELECT * FROM settings ORDER BY id;");
    if ($result->num_rows > 0) {
        while ($row = mysqli_fetch_array($result)) {
            $data = [
                    'facebook'  => !empty($row['facebook_url']) ? $row['facebook_url'] : '',
                    'twitter'   => !empty($row['twitter_url']) ? $row['twitter_url'] : '',
                    'instagram' => !empty($row['instagram_url']) ? $row['instagram_url'] : '',
                    'tiktok'    => !empty($row['tiktok_url']) ? $row['tiktok_url'] : '',
                    'whatsapp'  => !empty($row['whatsapp_url']) ? $row['whatsapp_url'] : '',
                    'youtube'   => !empty($row['youtube_url']) ? $row['youtube_url'] : '',
                    'discord'   => !empty($row['discord_url']) ? $row['discord_url'] : '',
                    'telegram'  => !empty($row['telegram_url']) ? $row['telegram_url'] : '',
                    'pinterest' => !empty($row['pinterest_url']) ? $row['pinterest_url'] : '',
                    'linkedin'  => !empty($row['linkedin_url']) ? $row['linkedin_url'] : '',
                    'twitch'    => !empty($row['twitch_url']) ? $row['twitch_url'] : '',
                    'vk'        => !empty($row['vk_url']) ? $row['vk_url'] : '',
            ];
            $socialMediaData = serialize($data);
            $stmt = $connection->prepare("UPDATE settings SET social_media_data = ? WHERE id = ?");
            $stmt->bind_param("si", $socialMediaData, $row['id']);
            $stmt->execute();
        }
    }

    //set payout settings
    $result = runQuery("SELECT * FROM general_settings WHERE id = 1");
    while ($row = mysqli_fetch_array($result)) {
        $payoutMethods = [
                'paypal_status'      => !empty($row['payout_paypal_status']) ? 1 : 0,
                'paypal_min_amount'  => 50,
                'bitcoin_status'     => 0,
                'bitcoin_min_amount' => 50,
                'iban_status'        => !empty($row['payout_iban_status']) ? 1 : 0,
                'iban_min_amount'    => 50,
                'swift_status'       => !empty($row['payout_swift_status']) ? 1 : 0,
                'swift_min_amount'   => 100
        ];
        $payoutMethods = serialize($payoutMethods);
        $stmt = $connection->prepare("UPDATE general_settings SET payout_methods = ? WHERE id = 1");
        $stmt->bind_param("s", $payoutMethods);
        $stmt->execute();
    }

    //set users
    $result = runQuery("SELECT * FROM users;");
    if ($result->num_rows > 0) {
        while ($row = mysqli_fetch_array($result)) {
            $roleId = 3;
            if ($row['role'] == 'admin') {
                $roleId = 1;
            } elseif ($row['role'] == 'author') {
                $roleId = 2;
            }
            $data = [
                    'facebook'             => !empty($row['facebook_url']) ? $row['facebook_url'] : '',
                    'twitter'              => !empty($row['twitter_url']) ? $row['twitter_url'] : '',
                    'instagram'            => !empty($row['instagram_url']) ? $row['instagram_url'] : '',
                    'tiktok'               => !empty($row['tiktok_url']) ? $row['tiktok_url'] : '',
                    'whatsapp'             => !empty($row['whatsapp_url']) ? $row['whatsapp_url'] : '',
                    'youtube'              => !empty($row['youtube_url']) ? $row['youtube_url'] : '',
                    'discord'              => !empty($row['discord_url']) ? $row['discord_url'] : '',
                    'telegram'             => !empty($row['telegram_url']) ? $row['telegram_url'] : '',
                    'pinterest'            => !empty($row['pinterest_url']) ? $row['pinterest_url'] : '',
                    'linkedin'             => !empty($row['linkedin_url']) ? $row['linkedin_url'] : '',
                    'twitch'               => !empty($row['twitch_url']) ? $row['twitch_url'] : '',
                    'vk'                   => !empty($row['vk_url']) ? $row['vk_url'] : '',
                    'personal_website_url' => !empty($row['personal_website_url']) ? $row['personal_website_url'] : ''
            ];
            $socialMediaData = serialize($data);
            $stmt = $connection->prepare("UPDATE users SET social_media_data = ?, role_id = ? WHERE id = ?");
            $stmt->bind_param("sii", $socialMediaData, $roleId, $row['id']);
            $stmt->execute();
        }
    }

    //set payout accounts
    $result = runQuery("SELECT * FROM user_payout_accounts;");
    if ($result->num_rows > 0) {
        while ($row = mysqli_fetch_array($result)) {
            $payout = [
                    'paypal_email'                   => $row['payout_paypal_email'],
                    'btc_address'                    => '',
                    'iban_full_name'                 => $row['iban_full_name'],
                    'iban_country'                   => $row['iban_country'],
                    'iban_bank_name'                 => $row['iban_bank_name'],
                    'iban_number'                    => $row['iban_number'],
                    'swift_full_name'                => $row['swift_full_name'],
                    'swift_address'                  => $row['swift_address'],
                    'swift_state'                    => $row['swift_state'],
                    'swift_city'                     => $row['swift_city'],
                    'swift_postcode'                 => $row['swift_postcode'],
                    'swift_country'                  => $row['swift_country'],
                    'swift_bank_account_holder_name' => $row['swift_bank_account_holder_name'],
                    'swift_iban'                     => $row['swift_iban'],
                    'swift_code'                     => $row['swift_code'],
                    'swift_bank_name'                => $row['swift_bank_name'],
                    'swift_bank_branch_city'         => $row['swift_bank_branch_city'],
                    'swift_bank_branch_country'      => $row['swift_bank_branch_country'],
                    'paypal_email'                   => $row['payout_paypal_email'],
            ];
            $payoutMethods = serialize($payout);

            $stmt = $connection->prepare("UPDATE users SET payout_methods = ? WHERE id = ?");
            $stmt->bind_param("si", $payoutMethods, $row['user_id']);
            $stmt->execute();
        }
    }

    runQuery("ALTER TABLE posts ADD COLUMN `image_id` INT(11) DEFAULT NULL;");
    runQuery("ALTER TABLE posts ADD COLUMN `comment_count` INT(11) DEFAULT 0;");
    runQuery("ALTER TABLE posts ADD INDEX idx_image_big (image_big);");
    runQuery("ALTER TABLE images ADD INDEX idx_image_big (image_big);");
    $query = "SELECT posts.*, 
       (SELECT id FROM images WHERE images.image_big = posts.image_big LIMIT 1) AS img_id,
       (SELECT COUNT(comments.id) FROM comments WHERE comments.post_id = posts.id) AS total_comments
        FROM `posts`";
    $result = runQuery($query);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $imgId = $row['img_id'];
            if (empty($imgId)) {
                $imgId = 0;
            }
            $commentCount = 0;
            if (!empty($row['total_comments'])) {
                $commentCount = $row['total_comments'];
            }

            $updateQuery = "UPDATE posts SET image_id = " . $imgId . ", comment_count = " . $commentCount . "  WHERE id = " . $row['id'];
            runQuery($updateQuery);

            //slider post
            if ($row['is_slider'] == 1) {
                runQuery("INSERT INTO post_selections (post_id, selection_type) VALUES(" . $row['id'] . ", 'slider');");
            }
            //featured post
            if ($row['is_featured'] == 1) {
                runQuery("INSERT INTO post_selections (post_id, selection_type) VALUES(" . $row['id'] . ", 'featured');");
            }
            //breaking post
            if ($row['is_breaking'] == 1) {
                runQuery("INSERT INTO post_selections (post_id, selection_type) VALUES(" . $row['id'] . ", 'breaking');");
            }
            //breaking post
            if ($row['is_recommended'] == 1) {
                runQuery("INSERT INTO post_selections (post_id, selection_type) VALUES(" . $row['id'] . ", 'recommended');");
            }

            if ($row['post_type'] == 'recipe') {
                $title = '';
                $order = 1;
                $stmt = $connection->prepare("INSERT INTO post_list_items (`post_id`, `title`, `content`, `item_order`) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("issi", $row['id'], $title, $row['content'], $order);
                $stmt->execute();
            }
        }
    }


    //rearrange tags
    runQuery("ALTER TABLE tags1 ADD COLUMN `lang_id` int DEFAULT 1;");
    runQuery("UPDATE tags1 JOIN posts ON tags1.post_id = posts.id SET tags1.lang_id = posts.lang_id;");
    runQuery("INSERT INTO tags (tag, tag_slug, lang_id) SELECT DISTINCT tag, tag_slug, lang_id FROM tags1");
    runQuery("INSERT INTO post_tags (post_id, tag_id) SELECT t.post_id, tg.id FROM tags1 t JOIN tags tg
        ON t.tag = tg.tag AND t.tag_slug = tg.tag_slug AND t.lang_id = tg.lang_id;");

    //add new translations
    $p["active_payment_request_error"] = "You already have an active payment request! Once this is complete, you can make a new request.";
    $p["add_role"] = "Add Role";
    $p["add_tag"] = "Add Tag";
    $p["ai_content_creator"] = "AI Content Creator";
    $p["ai_writer"] = "AI Writer";
    $p["automatically_calculated"] = "Automatically Calculated";
    $p["bitcoin"] = "Bitcoin";
    $p["bitcoin_address"] = "Bitcoin Address";
    $p["bulk_post_upload_for_authors"] = "Bulk Post Upload for Authors";
    $p["comments_contact"] = "Comments & Contact Messages";
    $p["discord"] = "Discord";
    $p["download"] = "Download";
    $p["edited"] = "Edited";
    $p["enter_2_characters"] = "Enter at least 2 characters";
    $p["enter_topic"] = "Enter topic";
    $p["enter_url"] = "Enter URL";
    $p["generated_text"] = "Generated Text";
    $p["generate_text"] = "Generate Text";
    $p["generating_text"] = "Generating text...";
    $p["google_indexing_api"] = "Google Indexing API";
    $p["human_verification"] = "Human Verification";
    $p["human_verification_exp"] = "Validate user activity through mouse movements, scrolling, and time spent on the page to ensure genuine interaction and prevent bots.";
    $p["instagram"] = "Instagram";
    $p["invalid_withdrawal_amount"] = "Invalid withdrawal amount!";
    $p["length_of_text"] = "Length of Text";
    $p["linkedin"] = "Linkedin";
    $p["logo_size"] = "Logo Size";
    $p["long"] = "Long";
    $p["manage_tags"] = "Manage Tags";
    $p["medium"] = "Medium";
    $p["min_mouse_movements"] = "Minimum Mouse Movements";
    $p["min_poyout_amount"] = "Minimum payout amount";
    $p["min_poyout_amounts"] = "Minimum Payout Amounts";
    $p["min_scroll_movements"] = "Minimum Scroll Movements";
    $p["min_time_spent_on_page"] = "Minimum Time Spent on the Page (Seconds)";
    $p["model"] = "Model";
    $p["msg_request_sent"] = "The request has been sent successfully!";
    $p["msg_tag_exists"] = "This tag already exists!";
    $p["msg_topic_empty"] = "Topic cannot be empty!";
    $p["my_earnings"] = "My Earnings";
    $p["new_payout_request"] = "New Payout Request";
    $p["pending"] = "Pending";
    $p["pinterest"] = "Pinterest";
    $p["pwa_logo"] = "PWA Logo";
    $p["refresh"] = "Refresh";
    $p["regenerate"] = "Regenerate";
    $p["roles"] = "Roles";
    $p["searching"] = "Searching...";
    $p["short"] = "Short";
    $p["static_cache_system"] = "Static Cache System";
    $p["submit"] = "Submit";
    $p["telegram"] = "Telegram";
    $p["temperature_response_diversity"] = "Temperature (Response Diversity)";
    $p["test_api"] = "Test API";
    $p["tiktok"] = "Tiktok";
    $p["tone_academic"] = "Academic";
    $p["tone_casual"] = "Casual";
    $p["tone_critical"] = "Critical";
    $p["tone_formal"] = "Formal";
    $p["tone_humorous"] = "Humorous";
    $p["tone_inspirational"] = "Inspirational";
    $p["tone_persuasive"] = "Persuasive";
    $p["tone_professional"] = "Professional";
    $p["tone_style"] = "Tone/Style";
    $p["topic"] = "Topic";
    $p["trending_posts"] = "Trending Posts";
    $p["twitch"] = "Twitch";
    $p["use_text"] = "Use Text";
    $p["very_long"] = "Very Long";
    $p["very_short"] = "Very Short";
    $p["view_post"] = "View Post";
    $p["vk"] = "VK";
    $p["warning_documentation"] = "Read the documentation before enabling this option";
    $p["whatsapp"] = "WhatsApp";
    $p["withdraw_amount"] = "Withdrawal Amount";
    $p["withdraw_method"] = "Withdrawal Method";
    $p["your_balance"] = "Your Balance";
    $p["youtube"] = "YouTube";
    addTranslations($p);

    //delete old translations
    runQuery("DELETE FROM language_translations WHERE `label`='administrators';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_role_changed';");
    runQuery("DELETE FROM language_translations WHERE `label`='no_thanks';");
    runQuery("DELETE FROM language_translations WHERE `label`='priority_none';");
    runQuery("DELETE FROM language_translations WHERE `label`='pwa_warning';");
    runQuery("DELETE FROM language_translations WHERE `label`='server_response';");
    runQuery("DELETE FROM language_translations WHERE `label`='set_default_payment_account';");
    runQuery("DELETE FROM language_translations WHERE `label`='warning_default_payout_account';");

    //indexes
    runQuery("ALTER TABLE ci_sessions ADD INDEX idx_id (id);");
    runQuery("CREATE INDEX idx_comments_optimized ON comments (post_id, parent_id, status);");
    runQuery("ALTER TABLE posts ADD INDEX idx_slug (slug);");
    runQuery("ALTER TABLE posts ADD INDEX idx_title_hash (title_hash);");
    runQuery("ALTER TABLE posts ADD INDEX idx_post_type (post_type);");
    runQuery("ALTER TABLE posts ADD INDEX idx_feed_id (feed_id);");
    runQuery("ALTER TABLE posts ADD INDEX idx_image_id (image_id);");
    runQuery("CREATE INDEX idx_latest_category_posts ON posts (is_scheduled, visibility, status, category_id, created_at);");
    runQuery("CREATE INDEX idx_posts_optimized ON posts (lang_id, is_scheduled, visibility, status, category_id, user_id);");
    runQuery("CREATE INDEX idx_posts_profile ON posts (lang_id, is_scheduled, visibility, status, user_id, created_at);");
    runQuery("CREATE FULLTEXT INDEX idx_fulltext ON posts (title, summary, content);");
    runQuery("CREATE INDEX idx_user_rewards ON post_pageviews_month (post_user_id, reward_amount, created_at);");
    runQuery("ALTER TABLE post_selections ADD INDEX idx_post_id (post_id);");
    runQuery("ALTER TABLE post_tags ADD INDEX idx_post_id (post_id);");
    runQuery("CREATE INDEX idx_tag_post ON post_tags (tag_id, post_id);");
    runQuery("ALTER TABLE tags ADD INDEX idx_tag_slug (tag_slug);");
    runQuery("ALTER TABLE tags ADD INDEX idx_lang_id (lang_id);");
    runQuery("ALTER TABLE users ADD INDEX idx_status (status);");
    runQuery("ALTER TABLE users ADD INDEX idx_reward_system_enabled (reward_system_enabled);");
    runQuery("ALTER TABLE users ADD INDEX idx_reward_balance (balance);");
    runQuery("ALTER TABLE users ADD INDEX idx_slug (slug);");
    runQuery("ALTER TABLE post_item_images ADD INDEX idx_item_type (item_type);");

    runQuery("UPDATE general_settings SET sitemap_frequency = 'auto', sitemap_last_modification = 'auto', sitemap_priority = 'auto', version = '2.4';");

    runQuery("ALTER TABLE general_settings DROP COLUMN `payout_paypal_status`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `payout_iban_status`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `payout_swift_status`;");
    runQuery("ALTER TABLE posts DROP COLUMN `is_slider`;");
    runQuery("ALTER TABLE posts DROP COLUMN `is_featured`;");
    runQuery("ALTER TABLE posts DROP COLUMN `is_recommended`;");
    runQuery("ALTER TABLE posts DROP COLUMN `is_breaking`;");
    runQuery("ALTER TABLE posts DROP COLUMN `image_big`;");
    runQuery("ALTER TABLE posts DROP COLUMN `image_default`;");
    runQuery("ALTER TABLE posts DROP COLUMN `image_slider`;");
    runQuery("ALTER TABLE posts DROP COLUMN `image_mid`;");
    runQuery("ALTER TABLE posts DROP COLUMN `image_small`;");
    runQuery("ALTER TABLE posts DROP COLUMN `image_mime`;");
    runQuery("ALTER TABLE posts DROP COLUMN `image_storage`;");
    runQuery("ALTER TABLE post_pageviews_month DROP COLUMN `user_agent`;");
    runQuery("ALTER TABLE settings DROP COLUMN `facebook_url`;");
    runQuery("ALTER TABLE settings DROP COLUMN `twitter_url`;");
    runQuery("ALTER TABLE settings DROP COLUMN `instagram_url`;");
    runQuery("ALTER TABLE settings DROP COLUMN `tiktok_url`;");
    runQuery("ALTER TABLE settings DROP COLUMN `whatsapp_url`;");
    runQuery("ALTER TABLE settings DROP COLUMN `youtube_url`;");
    runQuery("ALTER TABLE settings DROP COLUMN `discord_url`;");
    runQuery("ALTER TABLE settings DROP COLUMN `telegram_url`;");
    runQuery("ALTER TABLE settings DROP COLUMN `pinterest_url`;");
    runQuery("ALTER TABLE settings DROP COLUMN `linkedin_url`;");
    runQuery("ALTER TABLE settings DROP COLUMN `twitch_url`;");
    runQuery("ALTER TABLE settings DROP COLUMN `vk_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `facebook_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `twitter_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `instagram_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `tiktok_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `whatsapp_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `youtube_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `discord_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `telegram_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `pinterest_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `linkedin_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `twitch_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `vk_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `personal_website_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `role`;");
    runQuery("DROP TABLE tags1;");
    runQuery("DROP TABLE roles_permissions;");
    runQuery("DROP TABLE user_payout_accounts;");
    runQuery("DROP TABLE routes;");
    runQuery("ALTER TABLE images DROP INDEX idx_image_big;");

    //clear cache
    $cacheDir = __DIR__ . '/writable/cache';
    if (is_dir($cacheDir)) {
        $files = glob($cacheDir . '/*');
        if ($files !== false) {
            foreach ($files as $file) {
                if (is_file($file) && basename($file) !== 'index.html') {
                    @unlink($file);
                }
            }
        }
    }
}

function updateFrom24To30($connection)
{
    runQuery("DROP TABLE IF EXISTS `fonts`;");
    runQuery("RENAME TABLE `reactions` TO `old_reactions`");
    runQuery("RENAME TABLE `contacts` TO `contact_messages`");
    runQuery("RENAME TABLE `gallery` TO `gallery_images`");
    runQuery("RENAME TABLE `subscribers` TO `newsletter`");
    runQuery("RENAME TABLE `polls` TO `old_polls`");
    runQuery("RENAME TABLE `poll_votes` TO `old_poll_votes`");

    runQuery("CREATE TABLE IF NOT EXISTS `config` (
`id` INT AUTO_INCREMENT PRIMARY KEY,
`site_lang` int NOT NULL DEFAULT '1',
`multilingual_system` tinyint(1) DEFAULT '1',
`theme_mode` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'light',
`timezone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'America/New_York',
`app_icon` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`app_key` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`logo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`show_rss` tinyint(1) DEFAULT '1',
`rss_content_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '''summary''',
`rss_max_posts_per_feed` int DEFAULT '50',
`g_analytics_status` tinyint(1) DEFAULT '0',
`g_analytics_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`show_featured_section` tinyint(1) DEFAULT '1',
`show_latest_posts` tinyint(1) DEFAULT '1',
`pwa_status` tinyint(1) DEFAULT '0',
`registration_system` tinyint(1) DEFAULT '1',
`post_url_structure` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '''slug''',
`comment_system` tinyint(1) DEFAULT '1',
`comment_approval_system` tinyint(1) DEFAULT '1',
`show_post_author` tinyint(1) DEFAULT '1',
`show_post_date` tinyint(1) DEFAULT '1',
`show_pageviews` tinyint(1) DEFAULT '1',
`popular_posts_limit` smallint DEFAULT '5',
`related_posts_limit` smallint DEFAULT '6',
`custom_header_codes` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`custom_footer_codes` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`adsense_activation_code` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`emoji_reactions` tinyint(1) DEFAULT '1',
`mail_contact_status` tinyint(1) DEFAULT '0',
`mail_contact` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`content_cache_system` tinyint(1) DEFAULT '0',
`app_cache_system` tinyint(1) DEFAULT '0',
`content_cache_ttl` int DEFAULT '1800',
`auto_cache_refresh` tinyint(1) DEFAULT '0',
`email_verification` tinyint(1) DEFAULT '0',
`pagination_per_page` int DEFAULT '16',
`file_manager_show_all_files` tinyint(1) DEFAULT '1',
`audio_download_button` tinyint(1) DEFAULT '1',
`require_approval_new_posts` tinyint(1) DEFAULT '1',
`require_approval_edited_posts` tinyint(1) DEFAULT '1',
`show_home_link` tinyint(1) DEFAULT '1',
`post_formats` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`show_user_email_on_profile` tinyint(1) DEFAULT '1',
`reward_system_status` tinyint(1) DEFAULT '0',
`reward_amount` double DEFAULT '1',
`human_verification` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`payout_methods` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`premium_membership_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`security_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`currency_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`captcha_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`newsletter_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`email_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`storage_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`active_storage` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local',
`maintenance_mode_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`social_login_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`featured_content_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`sitemap_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`google_news_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`auto_post_deletion_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`redirect_rss_posts_to_original` tinyint(1) DEFAULT '0',
`image_file_format` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '''JPG''',
`allowed_file_extensions` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`file_upload_limits` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`google_maps_status` tinyint(1) DEFAULT '0',
`google_maps_api_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`delete_images_with_post` tinyint(1) DEFAULT '0',
`sticky_sidebar` tinyint(1) DEFAULT '0',
`ai_writer` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`bulk_post_upload_for_authors` tinyint DEFAULT '1',
`routes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`invoice_prefix` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'INV',
`cron_secret_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`last_system_cron_run` timestamp NULL DEFAULT NULL,
`version` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    runQuery("CREATE TABLE IF NOT EXISTS `reactions` (
`id` INT AUTO_INCREMENT PRIMARY KEY,
`post_id` int DEFAULT NULL,
`reaction` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`total` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    runQuery("CREATE TABLE IF NOT EXISTS `countries` (
`id` INT AUTO_INCREMENT PRIMARY KEY,
`name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
`vat_rate` decimal(5,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    runQuery("CREATE TABLE IF NOT EXISTS `email_blacklist` (
`id` INT AUTO_INCREMENT PRIMARY KEY,
`email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    runQuery("CREATE TABLE IF NOT EXISTS `event_registrations` (
`id` INT AUTO_INCREMENT PRIMARY KEY,
`post_id` int DEFAULT NULL,
`user_id` int DEFAULT NULL,
`full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`custom_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    runQuery("CREATE TABLE IF NOT EXISTS `fonts` (
`id` INT AUTO_INCREMENT PRIMARY KEY,
`font_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`font_family` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`font_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`font_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`font_source` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local',
`path_400` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`path_600` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`path_700` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`is_default` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    runQuery("CREATE TABLE IF NOT EXISTS `login_attempts` (
`id` INT AUTO_INCREMENT PRIMARY KEY,
`ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`attempts` int DEFAULT '1',
`last_attempt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    runQuery("CREATE TABLE IF NOT EXISTS `media` (
`id` INT AUTO_INCREMENT PRIMARY KEY,
`media_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`storage` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local',
`user_id` int DEFAULT NULL,
`is_downloadable` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    runQuery("CREATE TABLE IF NOT EXISTS `orders` (
`id` INT AUTO_INCREMENT PRIMARY KEY,
`order_token` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`user_id` int DEFAULT NULL,
`item_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`item_id` int DEFAULT NULL,
`item_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`billing_cycle` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'monthly',
`payment_method_id` int DEFAULT NULL,
`payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`subtotal` decimal(15,2) DEFAULT NULL,
`tax_rate` decimal(5,2) DEFAULT NULL,
`tax` decimal(15,2) DEFAULT NULL,
`transaction_fee_rate` decimal(5,2) DEFAULT NULL,
`transaction_fee` decimal(15,2) DEFAULT NULL,
`total_amount` decimal(15,2) DEFAULT NULL,
`billing_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`billing_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`billing_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`billing_city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`billing_country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`billing_zip_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`billing_company_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`billing_tax_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`ip_address` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`payment_url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`gateway_plan_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
`updated_at` timestamp NULL DEFAULT NULL,
`created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    runQuery("CREATE TABLE IF NOT EXISTS `payment_gateways` (
`id` INT AUTO_INCREMENT PRIMARY KEY,
`name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`name_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`public_key` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`secret_key` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`webhook_secret` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`environment` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'production',
`transaction_fee_rate` decimal(5,2) DEFAULT '0.00',
`base_product_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`status` tinyint(1) DEFAULT '0',
`logos` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    runQuery("CREATE TABLE IF NOT EXISTS `polls` (
`id` INT AUTO_INCREMENT PRIMARY KEY,
`lang_id` int DEFAULT '1',
`question` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`status` tinyint(1) DEFAULT '1',
`vote_permission` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'all',
`created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    runQuery("CREATE TABLE IF NOT EXISTS `poll_options` (
`id` INT AUTO_INCREMENT PRIMARY KEY,
`poll_id` int DEFAULT NULL,
`option_text` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`votes` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    runQuery("CREATE TABLE IF NOT EXISTS `poll_votes` (
`id` INT AUTO_INCREMENT PRIMARY KEY,
`poll_id` int DEFAULT NULL,
`option_id` int DEFAULT NULL,
`user_id` int DEFAULT NULL,
`created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    runQuery("CREATE TABLE IF NOT EXISTS `post_items` (
`id` INT AUTO_INCREMENT PRIMARY KEY,
`post_id` int DEFAULT NULL,
`title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`image_id` int DEFAULT NULL,
`image_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`item_order` smallint DEFAULT NULL,
`parent_link_num` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    runQuery("CREATE TABLE IF NOT EXISTS `post_additional_images` (
`id` INT AUTO_INCREMENT PRIMARY KEY,
`post_id` int DEFAULT NULL,
`image_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    runQuery("CREATE TABLE IF NOT EXISTS `post_media` (
`id` INT AUTO_INCREMENT PRIMARY KEY,
`post_id` int DEFAULT NULL,
`media_id` int DEFAULT NULL,
`selection_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    runQuery("CREATE TABLE IF NOT EXISTS `subscription_plans` (
`id` INT AUTO_INCREMENT PRIMARY KEY,
`plan_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`price` decimal(10,2) DEFAULT '0.00',
`billing_cycle` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'monthly',
`sort_order` int DEFAULT '1',
`is_popular` tinyint(1) DEFAULT '0',
`is_lifetime` tinyint(1) DEFAULT '0',
`is_free_plan` tinyint(1) DEFAULT '0',
`is_ad_free` tinyint(1) DEFAULT '0',
`badge_id` int DEFAULT NULL,
`status` tinyint(1) DEFAULT '1',
`features` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`stripe_price_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`updated_at` timestamp NULL DEFAULT NULL,
`created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    runQuery("CREATE TABLE IF NOT EXISTS `transactions` (
`id` INT AUTO_INCREMENT PRIMARY KEY,
`order_id` int DEFAULT NULL,
`user_id` int DEFAULT NULL,
`gateway_transaction_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`total_amount` decimal(15,2) DEFAULT NULL,
`status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'succeeded',
`created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    runQuery("CREATE TABLE IF NOT EXISTS `user_badges` (
`id` INT AUTO_INCREMENT PRIMARY KEY,
`badge_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
`color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`icon` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    runQuery("CREATE TABLE IF NOT EXISTS `user_purchases` (
`id` INT AUTO_INCREMENT PRIMARY KEY,
`user_id` int DEFAULT NULL,
`post_id` int DEFAULT NULL,
`order_id` int DEFAULT NULL,
`created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    runQuery("CREATE TABLE IF NOT EXISTS `user_subscriptions` (
`id` INT AUTO_INCREMENT PRIMARY KEY,
`user_id` int DEFAULT NULL,
`plan_id` int DEFAULT NULL,
`order_id` int DEFAULT NULL,
`gateway_name_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`gateway_subscription_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
`status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'active',
`reminder_1_day_sent` tinyint(1) DEFAULT '0',
`reminder_3_days_sent` tinyint(1) DEFAULT '0',
`expires_at` timestamp NULL DEFAULT NULL,
`updated_at` timestamp NULL DEFAULT NULL,
`created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    runQuery("ALTER TABLE `categories` 
    ADD COLUMN `parent_slug` varchar(255),
    ADD COLUMN `path` varchar(255),
    ADD COLUMN `depth` INT,
    ADD COLUMN `meta_title` varchar(255),
    ADD COLUMN `is_premium` TINYINT(1) DEFAULT 0,
    ADD COLUMN `is_exclusive` TINYINT(1) DEFAULT 0,
    ADD COLUMN `exclusive_price` decimal(15,2),
    ADD COLUMN `updated_at` timestamp,
    CHANGE `category_status` `status` TINYINT(1) DEFAULT 1
    ");

    runQuery("ALTER TABLE `images` 
    CHANGE `image_mime` `file_extension` varchar(50) DEFAULT 'jpg',
    ADD COLUMN `alt_text` varchar(255),
    ADD COLUMN `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
    ");

    runQuery("ALTER TABLE `pages` 
    ADD COLUMN `meta_title` varchar(255),
    ADD COLUMN `updated_at` timestamp,
    CHANGE `visibility` `status` TINYINT(1) DEFAULT 1
    ");

    runQuery("ALTER TABLE `posts` 
    ADD COLUMN `guid` varchar(32),
    ADD COLUMN `video_id` INT,
    ADD COLUMN `extra_data` TEXT,
    ADD COLUMN `meta_title` varchar(500),
    ADD COLUMN `meta_description` varchar(1000),
    ADD COLUMN `faq` TEXT,
    ADD COLUMN `is_premium` TINYINT(1) DEFAULT 0,
    ADD COLUMN `is_exclusive` TINYINT(1) DEFAULT 0,
    ADD COLUMN `exclusive_price` decimal(15,2),
    ADD COLUMN `scheduled_at` timestamp,
    ADD COLUMN `event_start_date` timestamp,
    ADD COLUMN `event_end_date` timestamp,
    CHANGE `keywords` `meta_keywords` varchar(500),
    CHANGE `slider_order` `slider_order` INT DEFAULT 1,
    CHANGE `featured_order` `featured_order` INT DEFAULT 1,
    CHANGE `show_right_column` `full_width_post` TINYINT(1) DEFAULT 0,
    CHANGE `post_type` `post_format` varchar(50) DEFAULT 'article'
    ");

    runQuery("ALTER TABLE `post_selections` 
    ADD COLUMN `lang_id` INT,
    ADD COLUMN `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
    ");

    runQuery("ALTER TABLE `settings` 
    ADD COLUMN `font_size` TEXT,
    ADD COLUMN `site_social_media` TEXT,
    ADD COLUMN `profile_social_media` TEXT,
    ADD COLUMN `invoice_details` TEXT,
    CHANGE `tertiary_font` `content_font` smallint DEFAULT 7,
    CHANGE `cookies_warning_text` `cookies_warning_data` TEXT
    ");

    runQuery("ALTER TABLE `users` 
    ADD COLUMN `storage_avatar` varchar(30) DEFAULT 'local',
    ADD COLUMN `storage_cover` varchar(30) DEFAULT 'local',
    ADD COLUMN `billing_info` TEXT,
    ADD COLUMN `has_used_free_trial` TINYINT(1) DEFAULT 0,
    ADD COLUMN `reset_token` varchar(100),
    ADD COLUMN `reset_token_created_at` timestamp,
    ADD COLUMN `last_login` timestamp,
    ADD COLUMN `updated_at` timestamp,
    CHANGE `token` `auth_token` varchar(255),
    CHANGE `user_type` `oauth_provider` varchar(50),
    CHANGE `google_id` `oauth_uid` varchar(255),
    CHANGE `reward_system_enabled` `reward_system` TINYINT(1) DEFAULT 0
    ");

    runQuery("ALTER TABLE `widgets` 
    CHANGE `type` `widget_type` varchar(100),
    CHANGE `visibility` `status` TINYINT(1) DEFAULT 1,
    CHANGE `is_custom` `is_custom` TINYINT(1) DEFAULT 1
    ");

    runQuery("ALTER TABLE `post_tags` ADD COLUMN `lang_id` INT");
    runQuery("ALTER TABLE `quiz_answers` ADD COLUMN `image_id` INT");
    runQuery("ALTER TABLE `quiz_questions` ADD COLUMN `image_id` INT");
    runQuery("ALTER TABLE `quiz_results` ADD COLUMN `image_id` INT");
    runQuery("ALTER TABLE `roles` ADD COLUMN `badge_id` INT");
    runQuery("ALTER TABLE `rss_feeds` ADD COLUMN `last_checked_at` timestamp");
    runQuery("ALTER TABLE `post_item_images` CHANGE `image_mime` `file_extension` varchar(50) DEFAULT 'jpg'");
    runQuery("ALTER TABLE `post_pageviews_month` ADD COLUMN `lang_id` INT");
    runQuery("ALTER TABLE `gallery_albums` ADD COLUMN `sort_order` INT DEFAULT 1");
    runQuery("ALTER TABLE `themes` ADD COLUMN `theme_mode` varchar(20) DEFAULT 'light'");

    insertNewRows();
    migrateConfig($connection);
    migrateCategories($connection);
    migrateTranslations($connection);
    migrateAudios($connection);
    migrateVideos($connection);
    migrateFiles($connection);
    migrateReactions();
    migratePolls($connection);
    migrateRowsToNewStructure();
    migrateSerializedColumnsToJson($connection);


    // Drop Indexes
    dropAllIndexesExceptFulltext('categories');
    dropAllIndexesExceptFulltext('comments');
    dropAllIndexesExceptFulltext('posts');
    dropAllIndexesExceptFulltext('post_pageviews_month');
    dropAllIndexesExceptFulltext('post_poll_votes');
    dropAllIndexesExceptFulltext('post_tags');
    dropAllIndexesExceptFulltext('quiz_answers');
    dropAllIndexesExceptFulltext('quiz_questions');
    dropAllIndexesExceptFulltext('quiz_results');
    dropAllIndexesExceptFulltext('reading_lists');

    // Add Indexes
    addIndexesIfNotExists('categories', [
            'idx_slug'      => 'slug',
            'idx_parent_id' => 'parent_id',
            'idx_path'      => 'path',
            'idx_depth'     => 'depth'
    ]);

    addIndexesIfNotExists('comments', [
            'idx_parent_id'       => 'parent_id',
            'idx_user_id'         => 'user_id',
            'idx_status_id'       => 'status, id',
            'idx_post_comments'   => 'post_id, status, parent_id, created_at, id',
            'idx_latest_comments' => 'created_at, id'
    ]);

    addIndexesIfNotExists('posts', [
            'idx_category_id'           => 'category_id',
            'idx_created_at'            => 'created_at',
            'idx_is_scheduled'          => 'is_scheduled',
            'idx_status'                => 'status',
            'idx_slug'                  => 'slug',
            'idx_post_format'           => 'post_format',
            'idx_feed_id'               => 'feed_id',
            'idx_image_id'              => 'image_id',
            'idx_guid'                  => 'guid',
            'idx_is_premium'            => 'is_premium',
            'idx_is_exclusive'          => 'is_exclusive',
            'idx_latest_posts'          => 'visibility, status, is_scheduled, created_at',
            'idx_latest_language_posts' => 'lang_id, visibility, status, is_scheduled, created_at',
            'idx_category_posts'        => 'lang_id, visibility, status, is_scheduled, created_at, category_id',
            'idx_author_posts'          => 'user_id, lang_id, visibility, status, is_scheduled, created_at',
            'idx_author_panel_posts'    => 'user_id, visibility, status, created_at',
            'idx_event_dates'           => 'event_start_date, event_end_date'
    ]);

    addIndexesIfNotExists('post_pageviews_month', [
            'idx_visit_hash'   => 'visit_hash',
            'idx_lang_post_id' => 'lang_id, post_id',
            'idx_report'       => 'created_at, reward_amount',
            'idx_report_user'  => 'post_user_id, created_at, reward_amount'
    ]);

    addIndexesIfNotExists('post_poll_votes', [
            'idx_question_id'     => 'question_id',
            'idx_user_id'         => 'user_id',
            'idx_answer_id'       => 'answer_id',
            'idx_post_id_user_id' => 'post_id, user_id'
    ]);

    addIndexesIfNotExists('post_tags', [
            'idx_post_id'       => 'post_id',
            'idx_tag_id'        => 'tag_id',
            'idx_lang_tag_post' => 'lang_id, tag_id, post_id'
    ]);

    addIndexesIfNotExists('quiz_answers', ['idx_question_id_id' => 'question_id, id']);
    addIndexesIfNotExists('quiz_questions', ['idx_post_id_question_order' => 'post_id, question_order']);
    addIndexesIfNotExists('quiz_results', ['idx_post_id_result_order' => 'post_id, result_order']);
    addIndexesIfNotExists('reading_lists', ['idx_user_post_unique' => 'user_id, post_id']);

    addIndexesIfNotExists('orders', [
            'idx_order_token'           => 'order_token',
            'idx_user_id'               => 'user_id',
            'idx_item_type'             => 'item_type',
            'idx_payment_method'        => 'payment_method',
            'idx_gateway_plan_id'       => 'gateway_plan_id',
            'idx_created_at'            => 'created_at',
            'idx_orders_status_created' => 'status, created_at'
    ]);

    addIndexesIfNotExists('transactions', [
            'idx_user_id'                => 'user_id',
            'idx_order_id'               => 'order_id',
            'idx_gateway_transaction_id' => 'gateway_transaction_id',
            'idx_created_at'             => 'created_at'
    ]);

    addIndexesIfNotExists('media', [
            'idx_media_type' => 'media_type',
            'idx_user_id'    => 'user_id'
    ]);

    addIndexesIfNotExists('post_media', [
            'idx_post_media'        => 'post_id, selection_type, id',
            'idx_selection_type_id' => 'selection_type, id'
    ]);

    addIndexesIfNotExists('user_subscriptions', [
            'idx_user_id'                 => 'user_id',
            'idx_status'                  => 'status',
            'idx_gateway_subscription_id' => 'gateway_subscription_id'
    ]);

    addIndexesIfNotExists('user_purchases', [
            'idx_post_id'   => 'post_id',
            'idx_user_post' => 'user_id, post_id'
    ]);

    addIndexesIfNotExists('event_registrations', [
            'idx_post_id' => 'post_id',
            'idx_user_id' => 'user_id'
    ]);

    addIndexesIfNotExists('poll_options', ['idx_poll_id' => 'poll_id']);
    addIndexesIfNotExists('poll_votes', ['idx_user_poll_vote' => 'user_id, poll_id']);
    addIndexesIfNotExists('gallery_images', ['idx_album_id' => 'album_id']);
    addIndexesIfNotExists('login_attempts', ['idx_ip_address' => 'ip_address']);
    addIndexesIfNotExists('newsletter', ['idx_email' => 'email', 'idx_token' => 'token']);
    addIndexesIfNotExists('post_items', ['idx_post_item_order' => 'post_id, item_order']);
    addIndexesIfNotExists('post_additional_images', ['idx_post_images' => 'post_id, image_id']);
    addIndexesIfNotExists('post_selections', ['idx_lang_type_id_post' => 'lang_id, selection_type, id, post_id']);
    addIndexesIfNotExists('reactions', ['idx_post_id' => 'post_id']);
    addIndexesIfNotExists('users', ['idx_reset_token' => 'reset_token']);

    // Reset session records
    runQuery("TRUNCATE TABLE `ci_sessions`;");

    // Drop columns
    runQuery("ALTER TABLE `posts` 
    DROP COLUMN `title_hash`, 
    DROP COLUMN `video_path`, 
    DROP COLUMN `video_storage`, 
    DROP COLUMN `recipe_info`");

    runQuery("ALTER TABLE `post_pageviews_month` 
    DROP COLUMN `ip_address`");

    runQuery("ALTER TABLE `quiz_answers` 
    DROP COLUMN `image_path`, 
    DROP COLUMN `image_storage`");

    runQuery("ALTER TABLE `quiz_questions` 
    DROP COLUMN `image_path`, 
    DROP COLUMN `image_storage`");

    runQuery("ALTER TABLE `quiz_results` 
    DROP COLUMN `image_path`,
    DROP COLUMN `image_storage`");

    runQuery("ALTER TABLE `rss_feeds` 
    DROP COLUMN `is_cron_updated`");

    runQuery("ALTER TABLE `users` 
    DROP COLUMN `facebook_id`, 
    DROP COLUMN `vk_id`");

    // Drop tables
    runQuery("DROP TABLE IF EXISTS 
    `audios`, 
    `post_audios`, 
    `files`, 
    `post_files`, 
    `general_settings`, 
    `videos`, 
    `old_reactions`, 
    `old_polls`, 
    `old_poll_votes`, 
    `post_gallery_items`, 
    `post_list_items`, 
    `post_images`;
");

    // Clear cache files
    $cacheDir = __DIR__ . '/writable/cache';
    if (is_dir($cacheDir)) {
        $files = glob($cacheDir . '/*');
        if ($files !== false) {
            foreach ($files as $file) {
                if (is_file($file) && basename($file) !== 'index.html') {
                    @unlink($file);
                }
            }
        }
    }
}

function insertNewRows()
{
    // Insert countries
    runQuery("INSERT IGNORE INTO `countries` (`id`, `name`, `vat_rate`) VALUES
(1, 'Afghanistan', 0.00),
(2, 'Albania', 0.00),
(3, 'Algeria', 0.00),
(4, 'American Samoa', 0.00),
(5, 'Andorra', 0.00),
(6, 'Angola', 0.00),
(7, 'Anguilla', 0.00),
(8, 'Antarctica', 0.00),
(9, 'Antigua and Barbuda', 0.00),
(10, 'Argentina', 0.00),
(11, 'Armenia', 0.00),
(12, 'Aruba', 0.00),
(13, 'Australia', 0.00),
(14, 'Austria', 0.00),
(15, 'Azerbaijan', 0.00),
(16, 'Bahamas', 0.00),
(17, 'Bahrain', 0.00),
(18, 'Bangladesh', 0.00),
(19, 'Barbados', 0.00),
(20, 'Belarus', 0.00),
(21, 'Belgium', 0.00),
(22, 'Belize', 0.00),
(23, 'Benin', 0.00),
(24, 'Bermuda', 0.00),
(25, 'Bhutan', 0.00),
(26, 'Bolivia', 0.00),
(27, 'Bosnia and Herzegovina', 0.00),
(28, 'Botswana', 0.00),
(29, 'Bouvet Island', 0.00),
(30, 'Brazil', 0.00),
(31, 'British Indian Ocean Territory', 0.00),
(32, 'Brunei Darussalam', 0.00),
(33, 'Bulgaria', 0.00),
(34, 'Burkina Faso', 0.00),
(35, 'Burundi', 0.00),
(36, 'Cambodia', 0.00),
(37, 'Cameroon', 0.00),
(38, 'Canada', 0.00),
(39, 'Cape Verde', 0.00),
(40, 'Cayman Islands', 0.00),
(41, 'Central African Republic', 0.00),
(42, 'Chad', 0.00),
(43, 'Chile', 0.00),
(44, 'China', 0.00),
(45, 'Christmas Island', 0.00),
(46, 'Cocos (Keeling) Islands', 0.00),
(47, 'Colombia', 0.00),
(48, 'Comoros', 0.00),
(49, 'Congo', 0.00),
(50, 'Cook Islands', 0.00),
(51, 'Costa Rica', 0.00),
(52, 'Croatia (Hrvatska)', 0.00),
(53, 'Cuba', 0.00),
(54, 'Cyprus', 0.00),
(55, 'Czech Republic', 0.00),
(56, 'Denmark', 0.00),
(57, 'Djibouti', 0.00),
(58, 'Dominica', 0.00),
(59, 'Dominican Republic', 0.00),
(60, 'East Timor', 0.00),
(61, 'Ecuador', 0.00),
(62, 'Egypt', 0.00),
(63, 'El Salvador', 0.00),
(64, 'Equatorial Guinea', 0.00),
(65, 'Eritrea', 0.00),
(66, 'Estonia', 0.00),
(67, 'Ethiopia', 0.00),
(68, 'Falkland Islands (Malvinas)', 0.00),
(69, 'Faroe Islands', 0.00),
(70, 'Fiji', 0.00),
(71, 'Finland', 0.00),
(72, 'France', 0.00),
(73, 'France, Metropolitan', 0.00),
(74, 'French Guiana', 0.00),
(75, 'French Polynesia', 0.00),
(76, 'French Southern Territories', 0.00),
(77, 'Gabon', 0.00),
(78, 'Gambia', 0.00),
(79, 'Georgia', 0.00),
(80, 'Germany', 0.00),
(81, 'Ghana', 0.00),
(82, 'Gibraltar', 0.00),
(83, 'Greece', 0.00),
(84, 'Greenland', 0.00),
(85, 'Grenada', 0.00),
(86, 'Guadeloupe', 0.00),
(87, 'Guam', 0.00),
(88, 'Guatemala', 0.00),
(89, 'Guernsey', 0.00),
(90, 'Guinea', 0.00),
(91, 'Guinea-Bissau', 0.00),
(92, 'Guyana', 0.00),
(93, 'Haiti', 0.00),
(94, 'Heard and McDonald Islands', 0.00),
(95, 'Honduras', 0.00),
(96, 'Hong Kong', 0.00),
(97, 'Hungary', 0.00),
(98, 'Iceland', 0.00),
(99, 'India', 0.00),
(100, 'Indonesia', 0.00),
(101, 'Iran', 0.00),
(102, 'Iraq', 0.00),
(103, 'Ireland', 0.00),
(104, 'Isle of Man', 0.00),
(105, 'Israel', 0.00),
(106, 'Italy', 0.00),
(107, 'Ivory Coast', 0.00),
(108, 'Jamaica', 0.00),
(109, 'Japan', 0.00),
(110, 'Jersey', 0.00),
(111, 'Jordan', 0.00),
(112, 'Kazakhstan', 0.00),
(113, 'Kenya', 0.00),
(114, 'Kiribati', 0.00),
(115, 'Kosovo', 0.00),
(116, 'Kuwait', 0.00),
(117, 'Kyrgyzstan', 0.00),
(118, 'Lao', 0.00),
(119, 'Latvia', 0.00),
(120, 'Lebanon', 0.00),
(121, 'Lesotho', 0.00),
(122, 'Liberia', 0.00),
(123, 'Libyan Arab Jamahiriya', 0.00),
(124, 'Liechtenstein', 0.00),
(125, 'Lithuania', 0.00),
(126, 'Luxembourg', 0.00),
(127, 'Macau', 0.00),
(128, 'Macedonia', 0.00),
(129, 'Madagascar', 0.00),
(130, 'Malawi', 0.00),
(131, 'Malaysia', 0.00),
(132, 'Maldives', 0.00),
(133, 'Mali', 0.00),
(134, 'Malta', 0.00),
(135, 'Marshall Islands', 0.00),
(136, 'Martinique', 0.00),
(137, 'Mauritania', 0.00),
(138, 'Mauritius', 0.00),
(139, 'Mayotte', 0.00),
(140, 'Mexico', 0.00),
(141, 'Micronesia, Federated States of', 0.00),
(142, 'Moldova, Republic of', 0.00),
(143, 'Monaco', 0.00),
(144, 'Mongolia', 0.00),
(145, 'Montenegro', 0.00),
(146, 'Montserrat', 0.00),
(147, 'Morocco', 0.00),
(148, 'Mozambique', 0.00),
(149, 'Myanmar', 0.00),
(150, 'Namibia', 0.00),
(151, 'Nauru', 0.00),
(152, 'Nepal', 0.00),
(153, 'Netherlands', 0.00),
(154, 'Netherlands Antilles', 0.00),
(155, 'New Caledonia', 0.00),
(156, 'New Zealand', 0.00),
(157, 'Nicaragua', 0.00),
(158, 'Niger', 0.00),
(159, 'Nigeria', 0.00),
(160, 'Niue', 0.00),
(161, 'Norfolk Island', 0.00),
(162, 'North Korea', 0.00),
(163, 'Northern Mariana Islands', 0.00),
(164, 'Norway', 0.00),
(165, 'Oman', 0.00),
(166, 'Pakistan', 0.00),
(167, 'Palau', 0.00),
(168, 'Palestine', 0.00),
(169, 'Panama', 0.00),
(170, 'Papua New Guinea', 0.00),
(171, 'Paraguay', 0.00),
(172, 'Peru', 0.00),
(173, 'Philippines', 0.00),
(174, 'Pitcairn', 0.00),
(175, 'Poland', 0.00),
(176, 'Portugal', 0.00),
(177, 'Puerto Rico', 0.00),
(178, 'Qatar', 0.00),
(179, 'Reunion', 0.00),
(180, 'Romania', 0.00),
(181, 'Russian Federation', 0.00),
(182, 'Rwanda', 0.00),
(183, 'Saint Kitts and Nevis', 0.00),
(184, 'Saint Lucia', 0.00),
(185, 'Saint Vincent and the Grenadines', 0.00),
(186, 'Samoa', 0.00),
(187, 'San Marino', 0.00),
(188, 'Sao Tome and Principe', 0.00),
(189, 'Saudi Arabia', 0.00),
(190, 'Senegal', 0.00),
(191, 'Serbia', 0.00),
(192, 'Seychelles', 0.00),
(193, 'Sierra Leone', 0.00),
(194, 'Singapore', 0.00),
(195, 'Slovakia', 0.00),
(196, 'Slovenia', 0.00),
(197, 'Solomon Islands', 0.00),
(198, 'Somalia', 0.00),
(199, 'South Africa', 0.00),
(200, 'South Georgia South Sandwich Islands', 0.00),
(201, 'South Korea', 0.00),
(202, 'Spain', 0.00),
(203, 'Sri Lanka', 0.00),
(204, 'St. Helena', 0.00),
(205, 'St. Pierre and Miquelon', 0.00),
(206, 'Sudan', 0.00),
(207, 'Suriname', 0.00),
(208, 'Svalbard and Jan Mayen Islands', 0.00),
(209, 'Swaziland', 0.00),
(210, 'Sweden', 0.00),
(211, 'Switzerland', 0.00),
(212, 'Syrian Arab Republic', 0.00),
(213, 'Taiwan', 0.00),
(214, 'Tajikistan', 0.00),
(215, 'Tanzania', 0.00),
(216, 'Thailand', 0.00),
(217, 'Togo', 0.00),
(218, 'Tokelau', 0.00),
(219, 'Tonga', 0.00),
(220, 'Trinidad and Tobago', 0.00),
(221, 'Tunisia', 0.00),
(222, 'Turkey', 0.00),
(223, 'Turkmenistan', 0.00),
(224, 'Turks and Caicos Islands', 0.00),
(225, 'Tuvalu', 0.00),
(226, 'Uganda', 0.00),
(227, 'Ukraine', 0.00),
(228, 'United Arab Emirates', 0.00),
(229, 'United Kingdom', 0.00),
(230, 'United States', 0.00),
(231, 'United States minor outlying islands', 0.00),
(232, 'Uruguay', 0.00),
(233, 'Uzbekistan', 0.00),
(234, 'Vanuatu', 0.00),
(235, 'Vatican City State', 0.00),
(236, 'Venezuela', 0.00),
(237, 'Vietnam', 0.00),
(238, 'Virgin Islands (British)', 0.00),
(239, 'Virgin Islands (U.S.)', 0.00),
(240, 'Wallis and Futuna Islands', 0.00),
(241, 'Western Sahara', 0.00),
(242, 'Yemen', 0.00),
(243, 'Zaire', 0.00),
(244, 'Zambia', 0.00),
(245, 'Zimbabwe', 0.00);");

    // Insert fonts
    runQuery("INSERT IGNORE INTO `fonts` (`id`, `font_name`, `font_family`, `font_key`, `font_type`, `font_source`, `path_400`, `path_600`, `path_700`, `is_default`) VALUES
(1, 'Arial', 'Arial, Helvetica, sans-serif', 'arial', 'sans-serif', 'system', NULL, NULL, NULL, 1),
(2, 'Courier New', '\"Courier New\", Courier, monospace', 'courier-new', 'monospace', 'system', NULL, NULL, NULL, 1),
(3, 'DM Sans', '\"DM Sans\", sans-serif', 'dm-sans', 'sans-serif', 'local', 'assets/fonts/dm-sans/dm-sans-400.woff2', 'assets/fonts/dm-sans/dm-sans-600.woff2', 'assets/fonts/dm-sans/dm-sans-700.woff2', 1),
(4, 'Georgia', 'Georgia, serif', 'georgia', 'serif', 'system', NULL, NULL, NULL, 1),
(5, 'Helvetica Neue', '\"Helvetica Neue\", Helvetica, Arial, sans-serif', 'helvetica', 'sans-serif', 'system', NULL, NULL, NULL, 1),
(6, 'IBM Plex Sans', '\"IBM Plex Sans\", sans-serif', 'ibm-plex-sans', 'sans-serif', 'local', 'assets/fonts/ibm-plex-sans/ibm-plex-sans-400.woff2', 'assets/fonts/ibm-plex-sans/ibm-plex-sans-600.woff2', 'assets/fonts/ibm-plex-sans/ibm-plex-sans-700.woff2', 1),
(7, 'Inter', '\"Inter\", sans-serif', 'inter', 'sans-serif', 'local', 'assets/fonts/inter/inter-400.woff2', 'assets/fonts/inter/inter-600.woff2', 'assets/fonts/inter/inter-700.woff2', 1),
(8, 'Jost', '\"Jost\", sans-serif', 'jost', 'sans-serif', 'local', 'assets/fonts/jost/jost-400.woff2', 'assets/fonts/jost/jost-600.woff2', 'assets/fonts/jost/jost-700.woff2', 1),
(9, 'Libre Baskerville', '\"Libre Baskerville\", serif', 'libre-baskerville', 'serif', 'local', 'assets/fonts/libre-baskerville/libre-baskerville-400.woff2', 'assets/fonts/libre-baskerville/libre-baskerville-600.woff2', 'assets/fonts/libre-baskerville/libre-baskerville-700.woff2', 1),
(10, 'Merriweather', '\"Merriweather\", serif', 'merriweather', 'serif', 'local', 'assets/fonts/merriweather/merriweather-400.woff2', 'assets/fonts/merriweather/merriweather-600.woff2', 'assets/fonts/merriweather/merriweather-700.woff2', 1),
(11, 'Montserrat', '\"Montserrat\", sans-serif', 'montserrat', 'sans-serif', 'local', 'assets/fonts/montserrat/montserrat-400.woff2', 'assets/fonts/montserrat/montserrat-600.woff2', 'assets/fonts/montserrat/montserrat-700.woff2', 1),
(12, 'Noto Serif', '\"Noto Serif\", serif', 'noto-serif', 'serif', 'local', 'assets/fonts/noto-serif/noto-serif-400.woff2', 'assets/fonts/noto-serif/noto-serif-600.woff2', 'assets/fonts/noto-serif/noto-serif-700.woff2', 1),
(13, 'Open Sans', '\"Open Sans\", sans-serif', 'open-sans', 'sans-serif', 'local', 'assets/fonts/open-sans/open-sans-400.woff2', 'assets/fonts/open-sans/open-sans-600.woff2', 'assets/fonts/open-sans/open-sans-700.woff2', 1),
(14, 'Plus Jakarta Sans', '\"Plus Jakarta Sans\", sans-serif', 'plus-jakarta-sans', 'sans-serif', 'local', 'assets/fonts/plus-jakarta-sans/plus-jakarta-sans-400.woff2', 'assets/fonts/plus-jakarta-sans/plus-jakarta-sans-600.woff2', 'assets/fonts/plus-jakarta-sans/plus-jakarta-sans-700.woff2', 1),
(15, 'Poppins', '\"Poppins\", sans-serif', 'poppins', 'sans-serif', 'local', 'assets/fonts/poppins/poppins-400.woff2', 'assets/fonts/poppins/poppins-600.woff2', 'assets/fonts/poppins/poppins-700.woff2', 1),
(16, 'Roboto', '\"Roboto\", sans-serif', 'roboto', 'sans-serif', 'local', 'assets/fonts/roboto/roboto-400.woff2', 'assets/fonts/roboto/roboto-600.woff2', 'assets/fonts/roboto/roboto-700.woff2', 1),
(17, 'Source Sans 3', '\"Source Sans 3\", sans-serif', 'source-sans-3', 'sans-serif', 'local', 'assets/fonts/source-sans-3/source-sans-3-400.woff2', 'assets/fonts/source-sans-3/source-sans-3-600.woff2', 'assets/fonts/source-sans-3/source-sans-3-700.woff2', 1),
(18, 'Tahoma', 'Tahoma, Geneva, sans-serif', 'tahoma', 'sans-serif', 'system', NULL, NULL, NULL, 1),
(19, 'Times New Roman', '\"Times New Roman\", Times, serif', 'times-new-roman', 'serif', 'system', NULL, NULL, NULL, 1),
(20, 'Trebuchet MS', '\"Trebuchet MS\", Helvetica, sans-serif', 'trebuchet-ms', 'sans-serif', 'system', NULL, NULL, NULL, 1),
(21, 'Verdana', 'Verdana, Geneva, sans-serif', 'verdana', 'sans-serif', 'system', NULL, NULL, NULL, 1),
(22, 'Work Sans', '\"Work Sans\", sans-serif', 'work-sans', 'sans-serif', 'local', 'assets/fonts/work-sans/work-sans-400.woff2', 'assets/fonts/work-sans/work-sans-600.woff2', 'assets/fonts/work-sans/work-sans-700.woff2', 1);");

    // Insert payment gateways
    runQuery("INSERT IGNORE INTO `payment_gateways` (`id`, `name`, `name_key`, `public_key`, `secret_key`, `webhook_secret`, `environment`, `transaction_fee_rate`, `base_product_id`, `status`, `logos`) VALUES
(1, 'PayPal', 'paypal', NULL, NULL, NULL, 'sandbox', 0.00, NULL, 0, 'paypal,visa,mastercard,amex,discover'),
(2, 'Stripe', 'stripe', NULL, NULL, NULL, 'sandbox', 0.00, NULL, 0, 'stripe,visa,mastercard,amex,discover'),
(3, 'Razorpay', 'razorpay', NULL, NULL, NULL, 'sandbox', 0.00, NULL, 0, 'razorpay,visa,mastercard,amex,maestro,rupay'),
(4, 'Iyzico', 'iyzico', NULL, NULL, NULL, 'sandbox', 0.00, NULL, 0, 'iyzico,visa,mastercard,amex,troy'),
(5, 'Mercado Pago', 'mercado_pago', NULL, NULL, NULL, 'sandbox', 0.00, NULL, 0, 'mercado_pago,visa,mastercard,amex,discover,boleto'),
(6, 'PayTabs', 'paytabs', NULL, NULL, NULL, 'sandbox', 0.00, NULL, 0, 'paytabs,visa,mastercard,amex,discover');");
}

function migrateConfig($connection)
{
    try {
        runQuery("INSERT IGNORE INTO `config` (`id`, `site_lang`, `multilingual_system`, `theme_mode`, `timezone`, `app_icon`, `app_key`, `logo`, `show_rss`, `rss_content_type`, `rss_max_posts_per_feed`, `g_analytics_status`, `g_analytics_id`, `show_featured_section`, `show_latest_posts`, `pwa_status`, `registration_system`, `post_url_structure`, `comment_system`, `comment_approval_system`, `show_post_author`, `show_post_date`, `show_pageviews`, `popular_posts_limit`, `related_posts_limit`, `custom_header_codes`, `custom_footer_codes`, `adsense_activation_code`, `emoji_reactions`, `mail_contact_status`, `mail_contact`, `content_cache_system`, `app_cache_system`, `content_cache_ttl`, `auto_cache_refresh`, `email_verification`, `pagination_per_page`, `file_manager_show_all_files`, `audio_download_button`, `require_approval_new_posts`, `require_approval_edited_posts`, `show_home_link`, `post_formats`, `show_user_email_on_profile`, `reward_system_status`, `reward_amount`, `human_verification`, `payout_methods`, `premium_membership_settings`, `security_settings`, `currency_settings`, `captcha_settings`, `newsletter_settings`, `email_settings`, `storage_settings`, `active_storage`, `maintenance_mode_settings`, `social_login_settings`, `featured_content_settings`, `sitemap_settings`, `google_news_settings`, `auto_post_deletion_settings`, `redirect_rss_posts_to_original`, `image_file_format`, `allowed_file_extensions`, `file_upload_limits`, `google_maps_status`, `google_maps_api_key`, `delete_images_with_post`, `sticky_sidebar`, `ai_writer`, `bulk_post_upload_for_authors`, `routes`, `invoice_prefix`, `cron_secret_key`, `last_system_cron_run`, `version`) VALUES
(1, 1, 1, 'light', 'America/New_York', NULL, NULL, '{\"width\":150,\"height\":50}', 1, 'summary', 50, 0, '', 1, 1, 0, 1, 'slug', 1, 1, 1, 1, 1, 5, 6, NULL, NULL, NULL, 1, 0, NULL, 0, 0, 1800, 0, 0, 16, 0, 1, 1, 1, 1, '{\"article\":1,\"gallery\":1,\"sorted_list\":1,\"table_of_contents\":1,\"video\":1,\"audio\":1,\"trivia_quiz\":1,\"personality_quiz\":1,\"poll\":1,\"recipe\":1,\"event\":1}', 1, 0, 0.25, '{\"status\":0,\"time_spent\":0,\"mouse\":0,\"scroll\":0}', '{\"paypal_status\":0,\"paypal_min_amount\":0,\"bitcoin_status\":0,\"bitcoin_min_amount\":0,\"iban_status\":0,\"iban_min_amount\":0,\"swift_status\":0,\"swift_min_amount\":0}', '{\"subscription_status\":0,\"subscription_mode\":\"all\",\"exclusive_sale_status\":0,\"default_content_price\":\"0\",\"paywall_appearance\":\"hard\",\"subscription_button_color\":\"#18181b\",\"subscription_button_visibility\":0}', '{\"max_login_attempts\":5,\"lockout_time\":300,\"password_min_length\":4,\"password_require_complex\":0,\"spam_protection_mode_post\":\"sanitize\",\"spam_protection_mode_public\":\"block\"}', '{\"code\":\"USD\",\"symbol\":\"$\",\"symbol_direction\":\"left\",\"thousand_separator\":\".\",\"decimal_separator\":\",\"}', NULL, '{\"status\":1,\"popup_status\":0}', NULL, NULL, 'local', '{\"status\":0,\"title\":\"Coming Soon!\",\"description\":\"Our website is under construction. We\'ll be here soon with our new awesome site.\"}', NULL, '{\"slider_source\":\"manual\",\"slider_sorting\":\"slider_order\",\"slider_duration\":10,\"slider_limit\":15,\"featured_status\":false,\"featured_source\":\"manual\",\"featured_sorting\":\"featured_order\",\"featured_duration\":10,\"exclude_slider_posts\":\"0\",\"recommended_sorting\":\"by_date\",\"recommended_duration\":20,\"recommended_limit\":5,\"br_news_status\":\"1\",\"br_news_source\":\"manual\",\"br_news_sorting\":\"by_date\",\"br_news_duration\":5,\"br_news_limit\":15}', '{\"frequency\":\"auto\",\"last_modification\":\"auto\",\"priority\":\"auto\"}', '{\"status\":0,\"site_name\":\"My News\",\"content_type\":\"content\",\"post_limit\":50}', '{\"status\":0,\"days\":30,\"deletion_method\":\"all\"}', 0, 'JPG', '[\"jpg\",\"png\",\"pdf\"]', '{\"image\":20,\"video\":50,\"audio\":20,\"file\":50}', 0, '', 0, 0, NULL, 1, '{\"admin\":\"admin\",\"profile\":\"profile\",\"tag\":\"tag\",\"reading_list\":\"reading-list\",\"account_settings\":\"account-settings\",\"social_accounts\":\"social-accounts\",\"change_password\":\"change-password\",\"forgot_password\":\"forgot-password\",\"reset_password\":\"reset-password\",\"delete_account\":\"delete-account\",\"manage_subscription\":\"manage-subscription\",\"purchased_content\":\"purchased-content\",\"billing\":\"billing\",\"payment_history\":\"payment-history\",\"sign_up\":\"sign-up\",\"posts\":\"posts\",\"search\":\"search\",\"rss_feeds\":\"rss-feeds\",\"gallery\":\"gallery\",\"subscription\":\"subscription\",\"plans\":\"plans\",\"checkout\":\"checkout\",\"payment\":\"payment\",\"invoice\":\"invoice\",\"success\":\"success\"}', 'INV', NULL, '2026-04-14 16:19:29', '3.0');");

        $generalSettings = runQuery("SELECT * FROM general_settings LIMIT 1");
        if ($generalSettings && $generalSettings->num_rows > 0) {
            $row = $generalSettings->fetch_assoc();
            $data = [
                    'site_lang'                      => $row['site_lang'] ?? 1,
                    'multilingual_system'            => $row['multilingual_system'] ?? 1,
                    'theme_mode'                     => $row['theme_mode'] ?? 'light',
                    'show_pageviews'                 => (int)($row['show_hits'] ?? 1),
                    'show_rss'                       => (int)($row['show_rss'] ?? 1),
                    'rss_content_type'               => $row['rss_content_type'] ?? 'summary',
                    'pagination_per_page'            => (int)($row['pagination_per_page'] ?? 16),
                    'timezone'                       => $row['timezone'] ?? 'America/New_York',
                    'show_featured_section'          => (int)($row['show_featured_section'] ?? 1),
                    'show_latest_posts'              => (int)($row['show_latest_posts'] ?? 1),
                    'pwa_status'                     => (int)($row['pwa_status'] ?? 1),
                    'registration_system'            => (int)($row['registration_system'] ?? 1),
                    'post_url_structure'             => $row['post_url_structure'] ?? 'slug',
                    'comment_system'                 => (int)($row['comment_system'] ?? 1),
                    'comment_approval_system'        => (int)($row['comment_approval_system'] ?? 1),
                    'show_post_author'               => (int)($row['show_post_author'] ?? 1),
                    'show_post_date'                 => (int)($row['show_post_date'] ?? 1),
                    'custom_header_codes'            => $row['custom_header_codes'] ?? '',
                    'custom_footer_codes'            => $row['custom_footer_codes'] ?? '',
                    'adsense_activation_code'        => $row['adsense_activation_code'] ?? '',
                    'emoji_reactions'                => (int)($row['emoji_reactions'] ?? 1),
                    'mail_contact_status'            => (int)($row['mail_contact_status'] ?? 1),
                    'mail_contact'                   => $row['mail_contact'] ?? '',
                    'content_cache_system'           => (int)($row['cache_system'] ?? 1),
                    'app_cache_system'               => (int)($row['static_cache_system'] ?? 1),
                    'content_cache_ttl'              => (int)($row['cache_refresh_time'] ?? 1800),
                    'auto_cache_refresh'             => (int)($row['refresh_cache_database_changes'] ?? 1),
                    'email_verification'             => (int)($row['email_verification'] ?? 1),
                    'file_manager_show_all_files'    => (int)($row['file_manager_show_files'] ?? 1),
                    'audio_download_button'          => (int)($row['audio_download_button'] ?? 1),
                    'require_approval_new_posts'     => (int)($row['approve_added_user_posts'] ?? 1),
                    'require_approval_edited_posts'  => (int)($row['approve_updated_user_posts'] ?? 1),
                    'show_home_link'                 => (int)($row['show_home_link'] ?? 1),
                    'show_user_email_on_profile'     => (int)($row['show_user_email_on_profile'] ?? 1),
                    'reward_system_status'           => (int)($row['reward_system_status'] ?? 1),
                    'reward_amount'                  => $row['reward_amount'] ?? 0,
                    'active_storage'                 => $row['storage'] ?? 'local',
                    'redirect_rss_posts_to_original' => (int)($row['redirect_rss_posts_to_original'] ?? 1),
                    'image_file_format'              => $row['image_file_format'] ?? 'JPG',
                    'delete_images_with_post'        => (int)($row['delete_images_with_post'] ?? 1),
                    'sticky_sidebar'                 => (int)($row['sticky_sidebar'] ?? 1),
                    'bulk_post_upload_for_authors'   => (int)($row['bulk_post_upload_for_authors'] ?? 1),
                    'human_verification'             => convertSerializedToJson($row['human_verification'] ?? ''),
                    'payout_methods'                 => convertSerializedToJson($row['payout_methods'] ?? '')
            ];

            // Logo Parsing
            $logoData = [];
            if (!empty($row['logo'])) $logoData['logo'] = $row['logo'];
            if (!empty($row['logo_email'])) $logoData['logo_png'] = $row['logo_email'];
            if (!empty($row['logo_footer'])) {
                $logoData['logo_dark'] = $row['logo_footer'];
                $logoData['logo_dark_png'] = $row['logo_footer'];
            }

            $width = 160;
            $height = 60;
            if (!empty($row['logo_size'])) {
                $dimensions = explode('x', strtolower(trim($row['logo_size'])));
                if (count($dimensions) === 2) {
                    $inputWidth = (int)$dimensions[0];
                    $inputHeight = (int)$dimensions[1];
                    $width = ($inputWidth >= 10 && $inputWidth <= 300) ? $inputWidth : 160;
                    $height = ($inputHeight >= 10 && $inputHeight <= 300) ? $inputHeight : 60;
                }
            }
            $logoData['width'] = $width;
            $logoData['height'] = $height;
            $data['logo'] = json_encode($logoData, JSON_UNESCAPED_SLASHES);

            // Email Settings
            $emailData = [
                    'mail_service'         => $row['mail_service'] ?? '',
                    'mail_protocol'        => $row['mail_protocol'] ?? '',
                    'mail_encryption'      => $row['mail_encryption'] ?? '',
                    'mail_host'            => $row['mail_host'] ?? '',
                    'mail_port'            => $row['mail_port'] ?? '',
                    'mail_username'        => $row['mail_username'] ?? '',
                    'mail_password'        => $row['mail_password'] ?? '',
                    'mail_reply_to'        => $row['mail_reply_to'] ?? '',
                    'mail_title'           => $row['mail_title'] ?? '',
                    'mailgun_api_key'      => '',
                    'mailgun_region'       => '',
                    'mailgun_domain'       => '',
                    'mailgun_sender_email' => '',
                    'brevo_api_key'        => '',
                    'email_template'       => 'pure_minimalist'
            ];
            $data['email_settings'] = json_encode($emailData);

            // Social Login Settings
            $socialLoginData = [
                    'g_client_id'     => $row['google_client_id'] ?? '',
                    'g_client_secret' => $row['google_client_secret'] ?? ''
            ];
            $data['social_login_settings'] = json_encode($socialLoginData);

            // Captcha Settings
            $captchaData = [
                    'status'       => !empty($row['recaptcha_secret_key']) ? 1 : 0,
                    'provider'     => 'google',
                    'g_site_key'   => $row['recaptcha_site_key'] ?? '',
                    'g_secret_key' => $row['recaptcha_secret_key'] ?? ''
            ];
            $data['captcha_settings'] = json_encode($captchaData);

            // Newsletter Settings
            $newsletterData = [
                    'status'       => (int)($row['newsletter_status'] ?? 1),
                    'popup_status' => (int)($row['newsletter_popup'] ?? 1),
                    'image'        => $row['newsletter_image'] ?? '',
                    'storage'      => 'local'
            ];
            $data['newsletter_settings'] = json_encode($newsletterData);

            // Maintenance Mode Settings
            $maintenanceData = [
                    'status'      => (int)($row['maintenance_mode_status'] ?? 1),
                    'title'       => $row['maintenance_mode_title'] ?? '',
                    'description' => $row['maintenance_mode_description'] ?? '',
                    'image'       => '',
                    'storage'     => 'local'
            ];
            $data['maintenance_mode_settings'] = json_encode($maintenanceData);

            // Storage Settings
            $storageData = [
                    'aws_key'    => $row['aws_key'] ?? '',
                    'aws_secret' => $row['aws_secret'] ?? '',
                    'aws_bucket' => $row['aws_bucket'] ?? '',
                    'aws_region' => $row['aws_region'] ?? ''
            ];
            $data['storage_settings'] = json_encode($storageData);

            // Auto Post Deletion Settings
            $postDeletionData = [
                    'status'          => (int)($row['auto_post_deletion'] ?? 0),
                    'days'            => (int)($row['auto_post_deletion_days'] ?? 30),
                    'deletion_method' => (int)($row['auto_post_deletion_delete_all'] ?? 0) === 1 ? 'all' : 'only_rss'
            ];
            $data['auto_post_deletion_settings'] = json_encode($postDeletionData);

            // File Extension Parser
            $extensionsRaw = $row['allowed_file_extensions'] ?? '';
            $extensionsArray = explode(',', $extensionsRaw);
            $extensionsArray = array_map('trim', $extensionsArray);
            $extensionsArray = array_filter($extensionsArray);
            $data['allowed_file_extensions'] = json_encode(array_values($extensionsArray), JSON_UNESCAPED_UNICODE);

            // AI Writer Parser
            $aiWriterOld = [];
            $aiWriterRaw = $row['ai_writer'] ?? '';
            if (!empty($aiWriterRaw)) {
                $unserialized = @unserialize($aiWriterRaw);
                if ($unserialized !== false) {
                    $aiWriterOld = (array)$unserialized;
                }
            }

            $aiData = [
                    'status'          => !empty($aiWriterOld['status']) ? 1 : 0,
                    'provider'        => 'chatgpt',
                    'chatgpt_api_key' => !empty($aiWriterOld['api_key']) ? $aiWriterOld['api_key'] : '',
                    'gemini_api_key'  => '',
                    'chatgpt_model'   => 'gpt-5.4-nano',
                    'gemini_model'    => 'gemini-3.1-flash-lite-preview',
            ];
            $data['ai_writer'] = json_encode($aiData);


            $updateFields = [];
            $bindValues = [];
            $bindTypes = "";
            foreach ($data as $column => $value) {
                if ($value === null || $value === 'null') {
                    $updateFields[] = "`$column` = NULL";
                } else {
                    $updateFields[] = "`$column` = ?";
                    $bindValues[] = (string)$value;
                    $bindTypes .= "s";
                }
            }

            $updateQuery = "UPDATE `config` SET " . implode(", ", $updateFields) . " WHERE id = 1";
            $stmt = $connection->prepare($updateQuery);
            if ($stmt) {
                if (!empty($bindValues)) {
                    $stmt->bind_param($bindTypes, ...$bindValues);
                }

                $stmt->execute();
                $stmt->close();
            }
        }

        return true;

    } catch (Exception $e) {
        return false;
    }
}

function migrateCategories($connection)
{
    try {
        $resultAll = $connection->query("SELECT `id`, `parent_id`, `slug` FROM `categories` ORDER BY `parent_id` ASC");

        if ($resultAll && $resultAll->num_rows > 0) {
            $grouped = [];
            $slugs = [];

            // Load entire hierarchy into memory for lightning-fast processing
            while ($row = $resultAll->fetch_assoc()) {
                $grouped[(int)$row['parent_id']][] = $row;
                $slugs[(int)$row['id']] = $row['slug'];
            }

            $stmtUpdateTree = $connection->prepare("UPDATE `categories` SET `path` = ?, `depth` = ?, `parent_slug` = ? WHERE `id` = ?");

            // Proceed only if the statement was successfully prepared (Fatal Error protection)
            if ($stmtUpdateTree) {
                // Keep track of visited IDs to prevent infinite loops (Circular References)
                $visited = [];

                $buildTree = function ($parentId, $parentPath, $parentDepth) use (&$buildTree, &$grouped, &$slugs, &$visited, $stmtUpdateTree) {
                    if (!isset($grouped[$parentId])) {
                        return;
                    }

                    foreach ($grouped[$parentId] as $cat) {
                        $id = (int)$cat['id'];

                        // If A points to B, and B points to A, this catches it and breaks the loop safely.
                        if (isset($visited[$id])) {
                            continue;
                        }
                        $visited[$id] = true;
                        $depth = $parentDepth + 1;
                        $path = ($parentId === 0) ? (string)$id : $parentPath . '/' . $id;
                        $parentSlug = ($parentId === 0) ? null : ($slugs[$parentId] ?? null);
                        $stmtUpdateTree->bind_param("sisi", $path, $depth, $parentSlug, $id);
                        $stmtUpdateTree->execute();
                        $buildTree($id, $path, $depth);
                    }
                };

                // Start the recursive engine from the absolute root
                $buildTree(0, '', -1);

                // Find categories that were never reached by the tree (because their parent_id points to a deleted category)
                foreach ($slugs as $catId => $catSlug) {
                    if (!isset($visited[$catId])) {
                        $path = (string)$catId;
                        $depth = 0;
                        $parentSlug = null;
                        $stmtUpdateTree->bind_param("sisi", $path, $depth, $parentSlug, $catId);
                        $stmtUpdateTree->execute();
                    }
                }

                $stmtUpdateTree->close();
            }
        }

        return true;

    } catch (\Throwable $e) {
        return true;
    }
}

function migrateAudios($connection)
{
    try {
        $resultAudios = $connection->query("SELECT * FROM `audios`");

        if ($resultAudios && $resultAudios->num_rows > 0) {
            $stmtMedia = $connection->prepare("INSERT INTO `media` (`media_type`, `file_name`, `file_path`, `storage`, `user_id`, `is_downloadable`) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtGetPostAudios = $connection->prepare("SELECT `post_id` FROM `post_audios` WHERE `audio_id` = ?");
            $stmtPostMedia = $connection->prepare("INSERT INTO `post_media` (`post_id`, `media_id`, `selection_type`) VALUES (?, ?, ?)");
            if ($stmtMedia && $stmtGetPostAudios && $stmtPostMedia) {
                while ($audio = $resultAudios->fetch_assoc()) {
                    $oldAudioId = (int)$audio['id'];
                    $mediaType = 'audio';
                    $fileName = $audio['audio_name'];
                    $filePath = $audio['audio_path'];
                    $storage = $audio['storage'];
                    $userId = (int)$audio['user_id'];
                    $isDownloadable = (int)$audio['download_button'];

                    $stmtMedia->bind_param("ssssii", $mediaType, $fileName, $filePath, $storage, $userId, $isDownloadable);
                    if ($stmtMedia->execute()) {
                        $newMediaId = $stmtMedia->insert_id;
                        $stmtGetPostAudios->bind_param("i", $oldAudioId);
                        if ($stmtGetPostAudios->execute()) {
                            $resultPostAudios = $stmtGetPostAudios->get_result();
                            while ($postAudio = $resultPostAudios->fetch_assoc()) {
                                $postId = (int)$postAudio['post_id'];
                                $selectionType = 'audio';
                                $stmtPostMedia->bind_param("iis", $postId, $newMediaId, $selectionType);
                                $stmtPostMedia->execute();
                            }
                        }
                    }
                }

                $stmtMedia->close();
                $stmtGetPostAudios->close();
                $stmtPostMedia->close();
            }
        }

        return true;

    } catch (\Throwable $e) {
        return true;
    }
}

function migrateVideos($connection)
{
    try {
        $resultVideos = $connection->query("SELECT * FROM `videos`");
        if ($resultVideos && $resultVideos->num_rows > 0) {
            $stmtMedia = $connection->prepare("INSERT INTO `media` (`media_type`, `file_name`, `file_path`, `storage`, `user_id`, `is_downloadable`) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmtMedia) {
                while ($video = $resultVideos->fetch_assoc()) {
                    $mediaType = 'video';
                    $fileName = $video['video_name'];
                    $filePath = $video['video_path'];
                    $storage = $video['storage'];
                    $userId = (int)$video['user_id'];
                    $isDownloadable = 0;
                    $stmtMedia->bind_param("ssssii", $mediaType, $fileName, $filePath, $storage, $userId, $isDownloadable);
                    $stmtMedia->execute();
                }
                $stmtMedia->close();
            }
        }
        $sqlUpdatePosts = "UPDATE `posts` p 
                           INNER JOIN `media` m ON p.`video_path` = m.`file_path` 
                           SET p.`video_id` = m.`id` 
                           WHERE m.`media_type` = 'video' 
                           AND p.`video_path` IS NOT NULL 
                           AND p.`video_path` != ''";

        $connection->query($sqlUpdatePosts);

        return true;

    } catch (\Throwable $e) {
        return true;
    }
}

function migrateFiles($connection)
{
    try {
        $resultFiles = $connection->query("SELECT * FROM `files`");

        if ($resultFiles && $resultFiles->num_rows > 0) {

            $stmtMedia = $connection->prepare("INSERT INTO `media` (`media_type`, `file_name`, `file_path`, `storage`, `user_id`, `is_downloadable`) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtGetPostFiles = $connection->prepare("SELECT `post_id` FROM `post_files` WHERE `file_id` = ?");
            $stmtPostMedia = $connection->prepare("INSERT INTO `post_media` (`post_id`, `media_id`, `selection_type`) VALUES (?, ?, ?)");

            if ($stmtMedia && $stmtGetPostFiles && $stmtPostMedia) {
                while ($file = $resultFiles->fetch_assoc()) {
                    $oldFileId = (int)$file['id'];
                    $mediaType = 'file';
                    $fileName = $file['file_name'];
                    $filePath = $file['file_path'];
                    $storage = $file['storage'];
                    $userId = (int)$file['user_id'];
                    $isDownloadable = 0;

                    $stmtMedia->bind_param("ssssii", $mediaType, $fileName, $filePath, $storage, $userId, $isDownloadable);
                    if ($stmtMedia->execute()) {
                        $newMediaId = $stmtMedia->insert_id;
                        $stmtGetPostFiles->bind_param("i", $oldFileId);

                        if ($stmtGetPostFiles->execute()) {
                            $resultPostFiles = $stmtGetPostFiles->get_result();
                            while ($postFile = $resultPostFiles->fetch_assoc()) {
                                $postId = (int)$postFile['post_id'];
                                $selectionType = 'attachment';
                                $stmtPostMedia->bind_param("iis", $postId, $newMediaId, $selectionType);
                                $stmtPostMedia->execute();
                            }
                        }
                    }
                }

                $stmtMedia->close();
                $stmtGetPostFiles->close();
                $stmtPostMedia->close();
            }
        }

        return true;

    } catch (\Throwable $e) {
        return true;
    }
}

function migrateReactions()
{
    try {
        $reactionsMap = [
                're_like'    => 'like',
                're_dislike' => 'dislike',
                're_love'    => 'love',
                're_funny'   => 'funny',
                're_angry'   => 'angry',
                're_sad'     => 'sad',
                're_wow'     => 'wow'
        ];

        foreach ($reactionsMap as $oldColumn => $newReactionName) {
            runQuery("INSERT INTO `reactions` (`post_id`, `reaction`, `total`)
                          SELECT `post_id`, '$newReactionName', `$oldColumn`
                          FROM `old_reactions`
                          WHERE `$oldColumn` IS NOT NULL AND `$oldColumn` > 0");
        }

        return true;

    } catch (Exception $e) {
        return false;
    }
}

function migratePolls($connection)
{
    try {
        $sqlMigratePolls = "INSERT IGNORE INTO `polls` (`id`, `lang_id`, `question`, `status`, `vote_permission`, `created_at`)
                            SELECT `id`, `lang_id`, `question`, `status`, `vote_permission`, `created_at` 
                            FROM `old_polls`";

        $connection->query($sqlMigratePolls);
        $resultOldPolls = $connection->query("SELECT * FROM `old_polls`");

        if ($resultOldPolls && $resultOldPolls->num_rows > 0) {
            $stmtInsertOption = $connection->prepare("INSERT INTO `poll_options` (`poll_id`, `option_text`, `votes`) VALUES (?, ?, 0)");
            $stmtMigrateVotes = $connection->prepare("INSERT INTO `poll_votes` (`poll_id`, `option_id`, `user_id`) 
                                                      SELECT `poll_id`, ?, `user_id` FROM `old_poll_votes` 
                                                      WHERE `poll_id` = ? AND (`vote` = ? OR `vote` = ?)");
            $stmtUpdateVoteCount = $connection->prepare("UPDATE `poll_options` SET `votes` = (SELECT COUNT(id) FROM `poll_votes` WHERE `option_id` = ?) WHERE `id` = ?");

            if ($stmtInsertOption && $stmtMigrateVotes && $stmtUpdateVoteCount) {
                while ($poll = $resultOldPolls->fetch_assoc()) {
                    $pollId = (int)$poll['id'];
                    for ($i = 1; $i <= 10; $i++) {
                        $colName = "option" . $i;
                        $optionText = trim($poll[$colName] ?? '');

                        if ($optionText !== '') {
                            $stmtInsertOption->bind_param("is", $pollId, $optionText);
                            if ($stmtInsertOption->execute()) {
                                $newOptionId = $stmtInsertOption->insert_id;
                                $stmtMigrateVotes->bind_param("iiss", $newOptionId, $pollId, $colName, $optionText);
                                $stmtMigrateVotes->execute();
                                $stmtUpdateVoteCount->bind_param("ii", $newOptionId, $newOptionId);
                                $stmtUpdateVoteCount->execute();
                            }
                        }
                    }
                }

                $stmtInsertOption->close();
                $stmtMigrateVotes->close();
                $stmtUpdateVoteCount->close();
            }
        }

        return true;

    } catch (\Throwable $e) {
        return false;
    }
}

function migrateRowsToNewStructure()
{
    try {
        runQuery("
            INSERT INTO `post_items` (`post_id`, `title`, `content`, `image_id`, `image_description`, `item_order`, `parent_link_num`)
            SELECT 
                `post_id`, 
                `title`, 
                `content`, 
                (SELECT `id` FROM `post_item_images` 
                 WHERE (
                     (`post_gallery_items`.`image_large` IS NOT NULL AND `post_gallery_items`.`image_large` != '' AND `image_default` = `post_gallery_items`.`image_large`) OR
                     (`post_gallery_items`.`image` IS NOT NULL AND `post_gallery_items`.`image` != '' AND `image_default` = `post_gallery_items`.`image`) OR
                     (`post_gallery_items`.`image` IS NOT NULL AND `post_gallery_items`.`image` != '' AND `image_small` = `post_gallery_items`.`image`)
                 ) LIMIT 1) AS `image_id`, 
                `image_description`, 
                `item_order`, 
                0 
            FROM `post_gallery_items`
        ");

        runQuery("
            INSERT INTO `post_items` (`post_id`, `title`, `content`, `image_id`, `image_description`, `item_order`, `parent_link_num`)
            SELECT 
                `post_id`, 
                `title`, 
                `content`, 
                (SELECT `id` FROM `post_item_images` 
                 WHERE (
                     (`post_list_items`.`image_large` IS NOT NULL AND `post_list_items`.`image_large` != '' AND `image_default` = `post_list_items`.`image_large`) OR
                     (`post_list_items`.`image` IS NOT NULL AND `post_list_items`.`image` != '' AND `image_default` = `post_list_items`.`image`) OR
                     (`post_list_items`.`image` IS NOT NULL AND `post_list_items`.`image` != '' AND `image_small` = `post_list_items`.`image`)
                 ) LIMIT 1) AS `image_id`, 
                `image_description`, 
                `item_order`, 
                `parent_link_num` 
            FROM `post_list_items`
        ");

        runQuery("
            INSERT INTO `post_additional_images` (`post_id`, `image_id`)
            SELECT `post_id`, `image_id` FROM (
                SELECT 
                    `post_id`, 
                    (SELECT `id` FROM `images` 
                     WHERE (
                         (`post_images`.`image_default` IS NOT NULL AND `post_images`.`image_default` != '' AND `images`.`image_default` = `post_images`.`image_default`) OR
                         (`post_images`.`image_big` IS NOT NULL AND `post_images`.`image_big` != '' AND `images`.`image_big` = `post_images`.`image_big`)
                     ) LIMIT 1) AS `image_id`
                FROM `post_images`
            ) AS temp_migration
            WHERE `image_id` IS NOT NULL
        ");

        runQuery("
            UPDATE `post_selections` ps
            INNER JOIN `posts` p ON ps.`post_id` = p.`id`
            SET ps.`lang_id` = p.`lang_id`
        ");

        runQuery("
            UPDATE `post_selections` 
            SET `selection_type` = 'breaking_news' 
            WHERE `selection_type` = 'breaking'
        ");

        runQuery("UPDATE quiz_answers
            JOIN post_item_images
            ON quiz_answers.image_path = post_item_images.image_small
            SET quiz_answers.image_id = post_item_images.id
            WHERE quiz_answers.image_path IS NOT NULL AND quiz_answers.image_path <> '';
        ");

        runQuery("UPDATE quiz_questions
            JOIN post_item_images
            ON quiz_questions.image_path = post_item_images.image_default
            SET quiz_questions.image_id = post_item_images.id
            WHERE quiz_questions.image_path IS NOT NULL AND quiz_questions.image_path <> '';
        ");

        runQuery("UPDATE quiz_results
            JOIN post_item_images
            ON quiz_results.image_path = post_item_images.image_default
            SET quiz_results.image_id = post_item_images.id
            WHERE quiz_results.image_path IS NOT NULL AND quiz_results.image_path <> '';
        ");

        runQuery("
            UPDATE `post_pageviews_month` pm
            INNER JOIN `posts` p ON pm.`post_id` = p.`id`
            SET pm.`lang_id` = p.`lang_id`
        ");

        // Set full-width column
        runQuery("
            UPDATE `posts` 
            SET `full_width_post` = 1 - `full_width_post` 
            WHERE `full_width_post` IN (0, 1)
        ");

        // Move recipe data to content column
        runQuery("
            UPDATE `posts` 
            SET `content` = `recipe_info` 
            WHERE `post_format` = 'recipe' AND `recipe_info` IS NOT NULL AND `recipe_info` != ''
        ");

        runQuery("
            UPDATE `post_tags` pt
            INNER JOIN `posts` p ON pt.`post_id` = p.`id`
            SET pt.`lang_id` = p.`lang_id`
        ");

        runQuery("
            UPDATE `settings` 
            SET `primary_font` = 13, `secondary_font` = 14, `content_font` = 7
        ");

        return true;

    } catch (Exception $e) {
        return false;
    }
}

function migrateSerializedColumnsToJson($connection)
{
    try {
        // Migrate Settings
        $sqlSelect = "SELECT `id`, `social_media_data` FROM `settings` WHERE `social_media_data` IS NOT NULL AND `social_media_data` != ''";
        $result = $connection->query($sqlSelect);
        if ($result && $result->num_rows > 0) {
            $stmtUpdate = $connection->prepare("UPDATE `settings` SET `social_media_data` = ? WHERE `id` = ?");
            if ($stmtUpdate) {
                while ($row = $result->fetch_assoc()) {
                    $id = (int)$row['id'];
                    $jsonData = convertSerializedToJson($row['social_media_data']);
                    if ($jsonData !== null) {
                        $stmtUpdate->bind_param("si", $jsonData, $id);
                        $stmtUpdate->execute();
                    }
                }
                $stmtUpdate->close();
            }
        }

        // Migrate Posts
        $sqlSelect = "SELECT `id`, `link_list_style`, `post_data`, `post_format` FROM `posts` 
              WHERE (`link_list_style` IS NOT NULL AND `link_list_style` != '' AND `post_format` = 'table_of_contents') 
                 OR (`post_data` IS NOT NULL AND `post_data` != '')";
        $result = $connection->query($sqlSelect);
        if ($result && $result->num_rows > 0) {
            $stmtUpdate = $connection->prepare("UPDATE `posts` SET `link_list_style` = ?, `post_data` = ? WHERE `id` = ?");
            if ($stmtUpdate) {
                while ($row = $result->fetch_assoc()) {
                    $id = (int)$row['id'];
                    $jsonPostData = convertSerializedToJson($row['post_data']);
                    $jsonLinkData = $row['link_list_style'];
                    if ($row['post_format'] === 'table_of_contents') {
                        $jsonLinkData = convertSerializedToJson($row['link_list_style']);
                    }
                    if ($jsonLinkData !== null && $jsonPostData !== null) {
                        $stmtUpdate->bind_param("ssi", $jsonLinkData, $jsonPostData, $id);
                        $stmtUpdate->execute();
                    }
                }
                $stmtUpdate->close();
            }
        }

        // Migrate Roles
        $sqlSelect = "SELECT `id`, `role_name` FROM `roles` WHERE `role_name` IS NOT NULL AND `role_name` != ''";
        $result = $connection->query($sqlSelect);
        if ($result && $result->num_rows > 0) {
            $stmtUpdate = $connection->prepare("UPDATE `roles` SET `role_name` = ? WHERE `id` = ?");
            if ($stmtUpdate) {
                while ($row = $result->fetch_assoc()) {
                    $id = (int)$row['id'];
                    $jsonData = convertSerializedToJson($row['role_name']);
                    if ($jsonData !== null) {
                        $stmtUpdate->bind_param("si", $jsonData, $id);
                        $stmtUpdate->execute();
                    }
                }
                $stmtUpdate->close();
            }
        }

        // Migrate Users
        $sqlSelect = "SELECT `id`, `payout_methods`, `social_media_data` FROM `users` 
              WHERE (`payout_methods` IS NOT NULL AND `payout_methods` != '') 
                 OR (`social_media_data` IS NOT NULL AND `social_media_data` != '')";
        $result = $connection->query($sqlSelect);
        if ($result && $result->num_rows > 0) {
            $stmtUpdate = $connection->prepare("UPDATE `users` SET `payout_methods` = ?, `social_media_data` = ? WHERE `id` = ?");
            if ($stmtUpdate) {
                while ($row = $result->fetch_assoc()) {
                    $id = (int)$row['id'];
                    $jsonPayout = convertSerializedToJson($row['payout_methods']);
                    $jsonSocial = convertSerializedToJson($row['social_media_data']);
                    if ($jsonPayout !== null && $jsonSocial !== null) {
                        $stmtUpdate->bind_param("ssi", $jsonPayout, $jsonSocial, $id);
                        $stmtUpdate->execute();
                    }
                }
                $stmtUpdate->close();
            }
        }

        return true;

    } catch (Exception $e) {
        return false;
    }
}

function migrateTranslations($connection)
{
    // Delete old translations
    $labelsToDelete = [
            "accept_cookies", "add_breaking", "add_link", "ai_content_creator", "approve_added_user_posts", "approve_updated_user_posts", "aws_key",
            "aws_secret", "aws_storage", "btn_goto_home", "btn_reply", "bulk_post_upload_exp", "category_ids_list", "change_favicon", "confirmed",
            "confirm_album", "confirm_answer", "confirm_category", "confirm_comments", "confirm_image", "confirm_item", "confirm_language",
            "confirm_link", "confirm_message", "confirm_messages", "confirm_page", "confirm_poll", "confirm_post", "confirm_posts", "confirm_question",
            "confirm_record", "confirm_result", "confirm_rss", "confirm_user", "confirm_widget", "confirm_your_email", "connect_with_facebook",
            "connect_with_google", "connect_with_vk", "cookie_prefix", "currency", "currency_format", "currency_name", "currency_symbol_format",
            "distribute_only_post_summary", "distribute_post_content", "download_database_backup", "drag_drop_files_here", "drag_drop_file_here",
            "edit_phrases", "email_reset_password", "enter_new_password", "enter_topic", "facebook_comments", "facebook_comments_code", "favicon",
            "footer_follow", "gallery_post_items", "general_settings", "generate_text", "generating_text", "google_indexing_api", "import_rss_feed",
            "info_about_recipe", "last_seen", "leave_reply", "leave_your_comment", "login", "login_error", "logout", "mailjet_email_address",
            "mailjet_email_address_exp", "mail_is_being_sent", "main_navigation", "main_post_image", "manage_tags", "message_email_unique_error",
            "message_newsletter_error", "message_newsletter_success", "message_register_error", "msg_comment_approved", "msg_confirmation_email",
            "msg_confirmed", "msg_confirmed_required", "msg_recaptcha", "msg_send_confirmation_email", "msg_unsubscribe", "msg_username_unique_error",
            "newsletter_desc", "newsletter_send_many_exp", "option_1", "option_10", "option_2", "option_3", "option_4", "option_5", "option_6",
            "option_7", "option_8", "option_9", "or_login_with_email", "or_register_with_email", "pagination_number_posts", "phrase", "phrases",
            "post_type", "region_code", "register", "remove_breaking", "resend_activation_email", "reset_password", "reset_password_error",
            "reset_password_success", "route_settings", "search_noresult", "send_email_subscriber", "send_email_subscribers", "show_cookies_warning",
            "show_latest_posts_on_featured", "show_latest_posts_on_slider", "show_news_ticker", "show_right_column", "social_login_settings",
            "social_media_settings", "sorted_list_items", "sort_featured_posts", "sort_slider_posts", "static_cache_system", "temperature_response_diversity",
            "tertiary_font", "the_operation_completed", "twitter", "txt_processing", "unconfirmed", "unsubscribe_successful", "update_rss_feed",
            "use_text", "vkontakte", "accept_cookies", "add_breaking", "add_link", "ai_content_creator", "approve_added_user_posts",
            "approve_updated_user_posts", "aws_key", "aws_secret", "aws_storage", "btn_goto_home", "btn_reply", "bulk_post_upload_exp",
            "category_ids_list", "change_favicon", "confirmed", "confirm_album", "confirm_answer", "confirm_category", "confirm_comments",
            "confirm_image", "confirm_item", "confirm_language", "confirm_link", "confirm_message", "confirm_messages", "confirm_page", "confirm_poll",
            "confirm_post", "confirm_posts", "confirm_question", "confirm_record", "confirm_result", "confirm_rss", "confirm_user", "confirm_widget",
            "confirm_your_email", "connect_with_facebook", "connect_with_google", "connect_with_vk", "cookie_prefix", "currency", "currency_format",
            "currency_name", "currency_symbol_format", "distribute_only_post_summary", "distribute_post_content", "download_database_backup",
            "drag_drop_files_here", "drag_drop_file_here", "edit_phrases", "email_reset_password", "enter_new_password", "enter_topic",
            "facebook_comments", "facebook_comments_code", "favicon", "footer_follow", "gallery_post_items", "general_settings", "generate_text",
            "generating_text", "google_indexing_api", "import_rss_feed", "info_about_recipe", "last_seen", "leave_reply", "leave_your_comment",
            "login", "login_error", "logout", "mailjet_email_address", "mailjet_email_address_exp", "mail_is_being_sent", "main_navigation",
            "main_post_image", "manage_tags", "message_email_unique_error", "message_newsletter_error", "message_newsletter_success",
            "message_register_error", "msg_comment_approved", "msg_confirmation_email", "msg_confirmed", "msg_confirmed_required", "msg_recaptcha",
            "msg_send_confirmation_email", "msg_unsubscribe", "msg_username_unique_error", "newsletter_desc", "newsletter_send_many_exp", "option_1",
            "option_10", "option_2", "option_3", "option_4", "option_5", "option_6", "option_7", "option_8", "option_9", "or_login_with_email",
            "or_register_with_email", "pagination_number_posts", "phrase", "phrases", "post_type", "region_code", "register", "remove_breaking",
            "resend_activation_email", "reset_password", "reset_password_error", "reset_password_success", "route_settings", "search_noresult",
            "send_email_subscriber", "send_email_subscribers", "show_cookies_warning", "show_latest_posts_on_featured", "show_latest_posts_on_slider",
            "show_news_ticker", "show_right_column", "social_login_settings", "social_media_settings", "sorted_list_items", "sort_featured_posts",
            "sort_slider_posts", "static_cache_system", "temperature_response_diversity", "tertiary_font", "the_operation_completed", "twitter",
            "txt_processing", "unconfirmed", "unsubscribe_successful", "update_rss_feed", "use_text", "vkontakte"
    ];


    try {
        if (!empty($labelsToDelete)) {
            $placeholders = implode(',', array_fill(0, count($labelsToDelete), '?'));
            $types = str_repeat('s', count($labelsToDelete));
            $sqlDelete = "DELETE FROM `language_translations` WHERE `label` IN ($placeholders)";

            $stmtDelete = $connection->prepare($sqlDelete);

            if ($stmtDelete) {
                $stmtDelete->bind_param($types, ...$labelsToDelete);
                $stmtDelete->execute();
                $stmtDelete->close();
            }
        }
    } catch (\Throwable $e) {
    }

    // Fetch all available language IDs from the database
    $languages = [];
    try {
        $langResult = $connection->query("SELECT `id` FROM `languages`");
        if ($langResult && $langResult->num_rows > 0) {
            while ($row = $langResult->fetch_assoc()) {
                $languages[] = (int)$row['id'];
            }
        }
    } catch (\Throwable $e) {
    }

    if (empty($languages)) {
        $languages = [1];
    }

    // Add new translations
    $labelsToAdd = [
            "about_event"                            => "About the Event",
            "accept_all"                             => "Accept All",
            "access_ends_on"                         => "Access Ends On",
            "access_key"                             => "Access Key",
            "access_level"                           => "Access Level",
            "account"                                => "Account",
            "account_settings"                       => "Account Settings",
            "active_provider"                        => "Active Provider",
            "active_storage"                         => "Active Storage",
            "activity"                               => "Activity",
            "additional_info"                        => "Additional Info",
            "add_badge"                              => "Add Badge",
            "add_breaking_news"                      => "Add to Breaking News",
            "add_custom_field"                       => "Add Custom Field",
            "add_email"                              => "Add Email",
            "add_event"                              => "Add Event",
            "add_feed"                               => "Add Feed",
            "add_menu_link"                          => "Add Menu Link",
            "add_new_plan"                           => "Add New Plan",
            "add_nofollow_seo_safe"                  => "Add \"Nofollow\" (SEO Safe)",
            "add_option"                             => "Add Option",
            "add_plan"                               => "Add Plan",
            "ad_free_experience"                     => "Ad-Free Experience",
            "ai_content_generator"                   => "AI Content Generator",
            "ai_writer_api_error"                    => "Generation failed. The API returned no content, possibly due to insufficient credits. Please check your API account.",
            "allow_single_content_sales"             => "Allow Single Content Sales",
            "all_website_content"                    => "All Website Content",
            "all_website_content_exp"                => "Force all posts on the website to be premium.",
            "alt_tag"                                => "Alt Tag (Accessibility)",
            "analytics"                              => "Analytics",
            "analytics_exp"                          => "Help us improve by collecting anonymous usage data.",
            "answer"                                 => "Answer",
            "apple_music"                            => "Apple Music",
            "application_icon"                       => "Application Icon",
            "application_icon_exp"                   => "This icon will be used as the application icon, favicon, and PWA icon across the platform.",
            "application_key"                        => "Application Key",
            "apply"                                  => "Apply",
            "apply_setting_to_subcategories"         => "Apply this setting to all subcategories",
            "apply_to_all"                           => "Apply to All",
            "approved"                               => "Approved",
            "app_cache"                              => "Application Cache",
            "app_cache_exp"                          => "Caches core application data such as settings, languages, categories, static pages, and other related entities. Persisted until explicitly updated when data changes.",
            "app_store"                              => "App Store",
            "assign_subscription"                    => "Assign Subscription",
            "attachments"                            => "Attachments",
            "author_earnings"                        => "Author Earnings",
            "back_to_billing"                        => "Back to Billing",
            "back_to_gallery"                        => "Back to Gallery",
            "badge"                                  => "Badge",
            "badges"                                 => "Badges",
            "basic_information"                      => "Basic Information",
            "behance"                                => "Behance",
            "billed_by"                              => "Billed By",
            "billed_to"                              => "Billed To",
            "billing_address"                        => "Billing Address",
            "billing_and_payments"                   => "Billing & Payments",
            "billing_cycle"                          => "Billing Cycle",
            "billing_details"                        => "Billing Details",
            "billing_details_exp"                    => "Manage your billing address and tax information for a seamless checkout experience.",
            "billing_information"                    => "Billing Information",
            "billing_order_summary"                  => "Billing & Order Summary",
            "blacklisted_emails_exp"                 => "Blacklisted emails are restricted from all email-based actions, including logging in, posting comments, and sending contact messages.",
            "block_style"                            => "Block Style",
            "bluesky"                                => "Bluesky",
            "bot_verification_failed"                => "Verification failed. Please verify you are not a robot!",
            "btn_subscribe"                          => "Subscribe",
            "bulk_actions"                           => "Bulk Actions",
            "bulk_vat_update"                        => "Bulk VAT Update",
            "button_text"                            => "Button Text",
            "button_visibility"                      => "Button Visibility",
            "cancelled"                              => "Cancelled",
            "cancel_subscription"                    => "Cancel Subscription",
            "cancel_subscription_exp"                => "Are you sure you want to cancel your subscription? You will still have access to all premium features until the end of your current billing cycle.",
            "capacity_limit"                         => "Capacity Limit",
            "captcha"                                => "Captcha",
            "captcha_provider"                       => "Captcha Provider",
            "captcha_provider_warning"               => "We strongly recommend using Cloudflare Turnstile as it offers a privacy-focused, cookie-free solution (GDPR compliant). Google reCAPTCHA may collect user data and track visitors, which requires you to obtain explicit user consent via a Cookie Banner. Ensuring compliance with data privacy laws is the sole responsibility of the site administrator.",
            "captcha_settings"                       => "Captcha Settings",
            "category_id_finder"                     => "Category Id Finder",
            "category_id_finder_exp"                 => "You can use this section to find out the Id of a category",
            "change_password_exp"                    => "Update your credentials with a strong and unique password to keep your account secure.",
            "change_plan"                            => "Change Plan",
            "checkout"                               => "Checkout",
            "checkout_one_time_payment_success"      => "Thank you for your purchase! Your payment has been completed successfully. You now have full access to the purchased content.",
            "checkout_subscription_success"          => "Your subscription has been successfully activated and you now have unlimited access to all our premium content.",
            "checkout_success_email_support_note"    => "A confirmation email with your purchase details has been sent to your billing email address. If you encounter any issues, you can contact us here:",
            "choose_content_hiding_method"           => "Choose Content Hiding Method",
            "choose_post_format_exp"                 => "Choose the type of content you want to create",
            "click_copy_icon_copy_url"               => "Click copy icon to copy URL",
            "cloudflare_turnstile"                   => "Cloudflare Turnstile",
            "comma"                                  => "Comma",
            "comma_separated_options"                => "Comma separated options (e.g., Meat, Vegan, None)",
            "community"                              => "Community",
            "company_invoice_details"                => "Company & Invoice Details",
            "company_invoice_details_exp"            => "Set your company name, address, and tax information for invoices.",
            "company_name"                           => "Company Name",
            "complete_payment"                       => "Complete Payment",
            "complete_payment_button_exp"            => "Please click the button below to securely complete your payment.",
            "complete_payment_legal_exp"             => "By completing the payment, you agree to our policies:",
            "complete_registration"                  => "Complete Registration",
            "complexity_requirement"                 => "Complexity Requirement",
            "confirm_action"                         => "Are you sure you want to perform this action?",
            "confirm_and_pay"                        => "Confirm and Pay",
            "confirm_delete"                         => "Are you sure you want to delete this item?",
            "confirm_delete_file"                    => "Are you sure you want to delete this file?",
            "confirm_delete_selected_files"          => "Are you sure you want to delete selected files?",
            "content_cache"                          => "Content Cache",
            "content_cache_exp"                      => "Caches dynamic content such as posts and comments. Automatically refreshed at set intervals to keep data current and minimize database queries.",
            "content_font"                           => "Content Font (Post & Page Text)",
            "content_images"                         => "Content Images",
            "content_images_upload_exp"              => "Content images are associated with specific content types, therefore general uploads are not supported in this section. Images must be uploaded through the relevant content creation form.",
            "content_management"                     => "Content Management",
            "content_settings"                       => "Content Settings",
            "content_source"                         => "Content Source",
            "continue_with_google"                   => "Continue with Google",
            "copy"                                   => "Copy",
            "country_specific_rates"                 => "Country Specific Rates",
            "create_account_exp"                     => "Join for unlimited access to premium journalism",
            "cron_job_token"                         => "Cron Job Token",
            "cron_job_token_delete_warning"          => "Are you sure? Deleting this token will immediately STOP all automated cron jobs.",
            "cron_job_token_exp"                     => "This secure token is required to execute automated tasks. Any changes (Generate or Revoke) are saved immediately.",
            "csv_file"                               => "CSV File",
            "currency_settings"                      => "Currency Settings",
            "currency_settings_exp"                  => "Manage your site\'s default currency and formatting options",
            "current_billing_cycle"                  => "Current Billing Cycle",
            "customer_details"                       => "Customer Details",
            "customer_ip_address"                    => "Customer IP Address",
            "customize"                              => "Customize",
            "custom_code_insertion"                  => "Custom Code Insertion",
            "custom_code_insertion_exp"              => "Add custom HTML, CSS, or JavaScript snippets to your website’s header or footer. Useful for analytics, tracking, or third-party integrations.",
            "custom_form_fields"                     => "Custom Form Fields",
            "custom_form_fields_exp"                 => "Full Name and Email fields are always required by default. Add any additional questions you want to ask attendees.",
            "dark"                                   => "Dark",
            "database_backup"                        => "Database Backup",
            "date_range"                             => "Date Range",
            "decimal_separator"                      => "Decimal Separator",
            "default_content_price"                  => "Default Content Price",
            "default_content_price_exp"              => "This will be the default price when you mark a post as \'Exclusive Paid Post\'. You can override this price on the post creation page.",
            "default_currency"                       => "Default Currency",
            "default_price"                          => "Default Price",
            "default_quantity"                       => "Default Quantity",
            "default_theme_mode"                     => "Default Theme Mode",
            "delete_account_exp"                     => "Permanently remove your account, personal data, and active subscriptions from the platform.",
            "delete_selected"                        => "Delete Selected",
            "detailed_report"                        => "Detailed Report",
            "details"                                => "Details",
            "discover"                               => "Discover",
            "display_duration"                       => "Display Duration",
            "display_limit"                          => "Display Limit",
            "display_type"                           => "Display Type",
            "domain"                                 => "Domain",
            "done"                                   => "Done",
            "dont_have_account"                      => "Don\'t have an account?",
            "dot"                                    => "Dot",
            "downloading_rss_images_exp"             => "Some images may be protected by copyright. Before downloading and hosting images on your own server, please make sure you have the appropriate usage rights. To reduce potential legal risks, using images via their remote URL is often a safer approach.",
            "downloads"                              => "Downloads",
            "dribbble"                               => "Dribbble",
            "dropdown"                               => "Dropdown",
            "drop_csv_file_here_or_upload"           => "Drop CSV file here or click to upload",
            "drop_files_or_click_upload"             => "Drop files here or click to upload",
            "duration"                               => "Duration",
            "earnings_and_stats"                     => "Earnings & Stats",
            "earnings_overview"                      => "Earnings Overview",
            "earnings_overview_exp"                  => "Summary of your earnings & stats",
            "edit_badge"                             => "Edit Badge",
            "edit_plan"                              => "Edit Plan",
            "edit_profile"                           => "Edit Profile",
            "edit_profile_exp"                       => "Update your personal information, avatar, and how you appear to the community.",
            "emails_sent_successfully"               => "All emails have been sent successfully!",
            "email_address"                          => "Email Address",
            "email_blacklist"                        => "Email Blacklist",
            "email_content_purchase_body"            => "Thank you for your purchase! You now have lifetime access to your premium content. Dive in and enjoy it anytime.",
            "email_content_purchase_btn"             => "Access Content",
            "email_content_purchase_subject"         => "Your Purchase is Ready",
            "email_content_purchase_title"           => "Purchase Successful",
            "email_expiring_soon_body"               => "Your subscription is set to expire soon. Renew now to continue enjoying premium features without interruption.",
            "email_expiring_soon_btn"                => "Renew Subscription",
            "email_expiring_soon_subject"            => "Your Premium Subscription is Expiring Soon",
            "email_expiring_soon_title"              => "Expiring Soon",
            "email_expiring_today_body"              => "Your premium access ends today. Renew now to continue enjoying all premium benefits without interruption.",
            "email_expiring_today_btn"               => "Renew Now",
            "email_expiring_today_subject"           => "Your Subscription Expires Today",
            "email_expiring_today_title"             => "Expiring Today",
            "email_new_subscription_body"            => "Thanks for subscribing! Your subscription has been successfully activated. You now have full access to all premium features and exclusive content.",
            "email_new_subscription_btn"             => "Start Exploring",
            "email_new_subscription_subject"         => "Your Premium Subscription is Now Active",
            "email_new_subscription_title"           => "Welcome to Premium!",
            "email_not_found_message"                => "We could not find an account associated with that email address.",
            "email_payment_failed_body"              => "We were unable to process your payment. Please update your payment method to avoid any interruption to your premium access.",
            "email_payment_failed_btn"               => "Update Payment Method",
            "email_payment_failed_subject"           => "Payment Failed – Action Required",
            "email_payment_failed_title"             => "Payment Failed",
            "email_subscription_cancelled_body"      => "Your subscription has been successfully cancelled. You will not be charged again, and you can continue using premium features until the end of your current billing period.",
            "email_subscription_cancelled_btn"       => "Account Settings",
            "email_subscription_cancelled_subject"   => "Your Subscription Has Been Cancelled",
            "email_subscription_cancelled_title"     => "Subscription Cancelled",
            "email_subscription_expired_body"        => "Your premium subscription has expired and your account is now on the standard plan. Upgrade anytime to regain full access to premium features.",
            "email_subscription_expired_btn"         => "Pricing Plans",
            "email_subscription_expired_subject"     => "Your Premium Access Has Ended",
            "email_subscription_expired_title"       => "Subscription Expired",
            "email_subscription_renewed_body"        => "Your payment was successful and your subscription has been renewed. Your premium access continues without interruption.",
            "email_subscription_renewed_btn"         => "View Billing History",
            "email_subscription_renewed_subject"     => "Your Subscription Has Been Renewed",
            "email_subscription_renewed_title"       => "Subscription Renewed",
            "email_templates"                        => "Email Templates",
            "email_verification_body1"               => "Thanks for signing up! We are excited to have you on board.",
            "email_verification_body2"               => "To get started, please verify your email address. This ensures that we can keep your account secure.",
            "email_verification_button"              => "Verify Email",
            "email_verification_required"            => "Please verify your email address",
            "email_verification_sent_message"        => "A verification email has been sent to your email address. Please follow the instructions in the email to activate your account.",
            "email_verification_subject"             => "Verify your email address",
            "email_verification_title"               => "Verify Your Email",
            "email_verify_success_message"           => "Your email address has been successfully verified. You can now access all the features of our platform.",
            "email_verify_success_title"             => "Email Verified!",
            "embed_code"                             => "Embed Code",
            "enabled"                                => "Enabled",
            "end"                                    => "End",
            "endpoint_url"                           => "Endpoint URL",
            "end_date"                               => "End Date",
            "environment_mode"                       => "Environment (Mode)",
            "error"                                  => "Error",
            "error_gateway_cancellation_failed"      => "We couldn\'t reach the payment provider to cancel your subscription right now. Please try again later.",
            "error_invalid_csv_file"                 => "Invalid CSV file or processing error. Please check your file.",
            "event"                                  => "Event",
            "events"                                 => "Events",
            "events_calendar"                        => "Events Calendar",
            "event_already_registered_email"         => "You have already registered for this event with this email address.",
            "event_details"                          => "Event Details",
            "event_exp"                              => "Scheduled events with location and map details",
            "event_external_link_label"              => "External Link (e.g., Eventbrite, Google Forms)",
            "event_highlights"                       => "Event Highlights",
            "event_highlight_ex"                     => "Example: Age limit - 15+ required",
            "event_images"                           => "Event Images",
            "event_registration"                     => "Event Registration",
            "event_registration_and_tickets"         => "Event Registration & Tickets",
            "event_registration_exp"                 => "Please fill out the form below to secure your spot.",
            "event_schedule"                         => "Event Schedule",
            "event_schedule_ex"                      => "Example: 09:00-09:30 - Opening & Registration - Opening speech",
            "event_you_are_registered"               => "You are registered!",
            "exclude_slider_posts"                   => "Exclude Slider Posts",
            "exclude_slider_posts_exp"               => "Enable this option to prevent posts that are already in the slider from appearing in the featured posts area.",
            "exclusive"                              => "Exclusive",
            "exclusive_category"                     => "Exclusive Category",
            "exclusive_category_exp"                 => "If enabled, all posts under this category will require a separate one-time purchase.",
            "exclusive_content"                      => "Exclusive Content",
            "exclusive_content_exp"                  => "Requires a separate one-time purchase.",
            "expired"                                => "Expired",
            "expired_on"                             => "Expired On",
            "expires_on"                             => "Expires On",
            "explore_subscription_plans"             => "Explore Subscription Plans",
            "external_image_url"                     => "External Image URL",
            "fade_out_effect"                        => "Fade Out Effect",
            "fade_out_effect_exp"                    => "Shows the beginning of the text clearly, then smoothly fades out towards the bottom.",
            "featured_content_settings"              => "Featured Content Settings",
            "feature_detail"                         => "Feature detail",
            "feed_link_generator"                    => "Feed Link Generator",
            "feed_post_limit"                        => "Feed Post Limit",
            "feed_post_limit_exp"                    => "The number of posts to be shown in the RSS feed. (Default: 50, Max: 100)",
            "field_label"                            => "Field Label",
            "file_name"                              => "File Name",
            "fill_all_required_fields"               => "Please fill in all required fields.",
            "filter_options"                         => "Filter Options",
            "font_file"                              => "Font File",
            "font_size"                              => "Font Size",
            "font_type"                              => "Font Type",
            "form_validation_alpha_dash"             => "The {field} field may only contain alphanumeric characters, underscores, and dashes.",
            "form_validation_alpha_numeric_space"    => "The {field} field may only contain alphanumeric characters and spaces.",
            "form_validation_valid_email"            => "The {field} field must contain a valid email address.",
            "free"                                   => "Free",
            "frequently_asked_questions"             => "Frequently Asked Questions",
            "friday"                                 => "Friday",
            "full_width_post"                        => "Full-Width Post",
            "gallery_items"                          => "Gallery Items",
            "gateway_transaction_id"                 => "Gateway Transaction ID",
            "generated_feed_url"                     => "Generated Feed URL",
            "generate_content"                       => "Generate Content",
            "generate_new_token_warning"             => "Generating a new token will invalidate the existing one. Continue?",
            "generate_with_ai"                       => "Generate with AI",
            "generating_content_dots"                => "Generating Content...",
            "generation_type"                        => "Generation Type",
            "generation_type_1"                      => "Complete Article (Title, SEO & Content)",
            "generation_type_2"                      => "Content Only (Body Text)",
            "generation_type_3"                      => "Content and Title",
            "github"                                 => "Github",
            "gitlab"                                 => "GitLab",
            "global_settings"                        => "Global Settings",
            "google_fonts"                           => "Google Fonts",
            "google_maps"                            => "Google Maps",
            "google_maps_api_key"                    => "Google Maps API Key",
            "google_news_publication_name"           => "Google News Publication Name",
            "google_news_rss_content_exp"            => "Determines how your news appears inside the Google News App. ",
            "google_play"                            => "Google Play",
            "go_back_to_content"                     => "Go Back to Content",
            "go_to_homepage"                         => "Go to the Homepage",
            "guest"                                  => "Guest",
            "hard_paywall"                           => "Hard Paywall",
            "hard_paywall_exp"                       => "Hides the content completely immediately after the post title and image.",
            "highlights"                             => "Highlights",
            "home_page_link"                         => "Home Page Link",
            "icon"                                   => "Icon",
            "import"                                 => "Import",
            "import_completed"                       => "Import completed!",
            "import_completed_skipped_rows"          => "Import completed. Skipped rows due to errors:",
            "import_posts"                           => "Import Posts",
            "info"                                   => "Info",
            "integration_endpoints"                  => "Integration Endpoints",
            "integration_endpoints_exp"              => "The following links are generated for you to submit to Google services.",
            "internal_registration"                  => "Internal Registration",
            "invalid_attempt"                        => "Invalid attempt!",
            "invoice"                                => "Invoice",
            "invoice_footer_note"                    => "Invoice Footer Note",
            "invoice_prefix"                         => "Invoice Prefix",
            "item_description"                       => "Item Description",
            "item_id"                                => "Item ID",
            "item_type"                              => "Item Type",
            "iyzico"                                 => "Iyzico",
            "join"                                   => "Join",
            "join_this_event"                        => "Join This Event",
            "join_this_event_exp"                    => "Secure your spot before tickets run out.",
            "join_to_newsletter"                     => "Join to our newsletter to stay updated.",
            "keep_original_file_format"              => "Keep Original File Format",
            "key_id"                                 => "Key ID",
            "last_seen_user"                         => "Last seen",
            "latest_transactions"                    => "Latest Transactions",
            "latest_transactions_exp"                => "Overview of recent financial activities",
            "latitude"                               => "Latitude",
            "layout_special"                         => "Layout & Special",
            "lifetime"                               => "Lifetime",
            "lifetime_access"                        => "Lifetime Access",
            "lifetime_plan"                          => "Lifetime plan",
            "light"                                  => "Light",
            "list"                                   => "List",
            "list_items"                             => "List Items",
            "loading"                                => "Loading...",
            "load_map"                               => "Load Map",
            "localized_settings"                     => "Localized Settings",
            "location_map_hidden"                    => "Location Map Hidden",
            "location_map_hidden_exp"                => "To protect your privacy, Google Maps is hidden. By loading the map, you consent to sharing your IP address and agree to the Google Privacy Policy:",
            "lockout_time"                           => "Lockout Time",
            "lockout_time_exp"                       => "Wait time after exceeding login limits.",
            "login_admin_exp"                        => "Your central hub for managing news and magazine content, all in one place.",
            "login_security"                         => "Login Security",
            "log_in"                                 => "Log In",
            "log_in_admin_exp"                       => "Please log in to access the admin panel",
            "log_in_error"                           => "Wrong username or password!",
            "log_in_exp"                             => "Welcome back! Please enter your details.",
            "log_out"                                => "Log Out",
            "longitude"                              => "Longitude",
            "main"                                   => "Main",
            "main_image"                             => "Main Image",
            "main_images"                            => "Main Images",
            "main_slider"                            => "Main Slider",
            "manage_my_subscription"                 => "Manage My Subscription",
            "manage_subscription"                    => "Manage Subscription",
            "manage_subscription_exp"                => "Control your premium membership, manage renewals, or adjust your current plan.",
            "manual_selection_only"                  => "Manual Selection Only",
            "map_drag_maker_exp"                     => "Drag the marker or enter coordinates to update.",
            "mastodon"                               => "Mastodon",
            "max_audio_file_size"                    => "Maximum Audio File Size",
            "max_file_size"                          => "Maximum File Size",
            "max_image_file_size"                    => "Maximum Image File Size",
            "max_login_attempts"                     => "Max Login Attempts",
            "max_login_attempts_exp"                 => "Lock account after this many failed attempts.",
            "max_posts_per_feed"                     => "Max Posts per Feed",
            "max_posts_per_feed_exp"                 => "Number of posts to display in an RSS feed.",
            "max_video_file_size"                    => "Maximum Video File Size",
            "measurement_id"                         => "Measurement ID",
            "media"                                  => "Media",
            "member"                                 => "Member",
            "membership"                             => "Membership",
            "membership_plan_details"                => "Membership Plan Details",
            "membership_plan_details_exp"            => "Configure pricing, duration, and user perks.",
            "mercado_pago"                           => "Mercado Pago",
            "meta_description"                       => "Meta Description",
            "meta_keywords"                          => "Meta Keywords",
            "meta_keywords_exp"                      => "Enter keywords separated by commas (e.g., technology, ai, future)",
            "meta_options"                           => "Meta Options",
            "meta_title"                             => "Meta Title",
            "min_password_length"                    => "Min Password Length",
            "min_password_length_exp"                => "Minimum characters required for new passwords.",
            "monday"                                 => "Monday",
            "monetization"                           => "Monetization",
            "most_popular"                           => "Most Popular",
            "msg_bot_verification_failed"            => "Bot verification failed. Please try again.",
            "msg_bulk_approve"                       => "Are you sure you want to approve the selected items?",
            "msg_bulk_delete"                        => "Are you sure you want to delete the selected items?",
            "msg_cancel_email_sending"               => "Are you sure you want to cancel the sending process?",
            "msg_content_generated"                  => "Content generated successfully!",
            "msg_error_email_blacklisted"            => "Your request could not be processed at this time. Please try again later.",
            "msg_newsletter_error"                   => "Your email address is already registered!",
            "msg_newsletter_remove"                  => "We’ve successfully removed your email from our mailing list. You will no longer receive our news, updates, or special offers. We’re sad to see you go, but you can join us again whenever you like!",
            "msg_newsletter_success"                 => "You have successfully joined!",
            "msg_no_purchase_yet"                    => "You haven\'t made a purchase yet.",
            "msg_select_category"                    => "Please select a category.",
            "msg_select_csv_file"                    => "Please select a CSV file.",
            "msg_select_date"                        => "Please select a date.",
            "msg_sign_up_success"                    => "Your account has been successfully created!",
            "msg_sign_up_success_email_verify"       => "Your account has been successfully created! Please check your email to verify your account.",
            "msg_sitemap_cron_job"                   => "By creating a cron job on your server, you can automatically update your sitemap using this URL",
            "msg_site_under_construction"            => "Site under construction! Please try again later.",
            "msg_upload_file_size_error"             => "The selected file exceeds the allowed file size. Allowed size:",
            "must_agree_to_continue"                 => "You must agree to continue.",
            "never_expires"                          => "Never expires",
            "newest_first"                           => "Newest First",
            "newsletter_join_desc"                   => "Get the latest news and curated updates straight to your inbox. Sign up for our newsletter.",
            "newsletter_remove_successful"           => "You’ve left our mailing list.",
            "news_sitemap_for_seo"                   => "News Sitemap (for SEO & Bots)",
            "news_sitemap_for_seo_exp"               => "This file includes only articles from the last 48 hours. Copy this URL and submit it once via Google Search Console > Sitemaps.",
            "new_question"                           => "New Question",
            "next_payment"                           => "Next Payment",
            "no_action_allow"                        => "No Action (Allow)",
            "no_active_subscription"                 => "No Active Subscription",
            "no_active_subscription_exp"             => "You are not currently subscribed to any premium plan. Upgrade your account today to unlock exclusive features, remove limitations, and enjoy our premium services.",
            "no_description"                         => "No description",
            "no_files_found"                         => "No files found",
            "no_files_found_exp"                     => "Upload your first file to get started.",
            "no_registration_required"               => "No Registration Required",
            "no_results_found"                       => "No results found.",
            "number_of_images"                       => "Number of Images",
            "number_of_posts"                        => "Number of Posts",
            "occupancy_rate"                         => "Occupancy Rate",
            "oldest_first"                           => "Oldest First",
            "one_time_payment"                       => "One time payment",
            "only_csv_files_allowed"                 => "Only .csv files are allowed",
            "operation_completed"                    => "Operation completed successfully.",
            "option"                                 => "Option",
            "optional_url_exp"                       => "External link for a button on the post page.",
            "order_id"                               => "Order ID",
            "order_summary"                          => "Order Summary",
            "order_token"                            => "Order Token",
            "organizer"                              => "Organizer",
            "original"                               => "Original",
            "or_log_in_with_email"                   => "Or log in with email",
            "or_sign_up_with_email"                  => "Or sign up with email",
            "page_settings"                          => "Page Settings",
            "pagination_per_page"                    => "Pagination (Number of posts per page)",
            "paid"                                   => "Paid",
            "participants"                           => "Participants",
            "password_complexity_special_error"      => "Password must contain at least one number and one special character.",
            "password_reset_body"                    => "To set a new password, please click the button below. For your security, this link will expire in 2 hours.",
            "password_reset_button"                  => "Reset Password",
            "password_reset_description"             => "Create your new password",
            "password_reset_sent_message"            => "A password reset link has been sent to your email address. Please follow the instructions in the email to reset your password.",
            "password_reset_subject"                 => "Reset your password",
            "password_reset_success"                 => "Your password has been successfully updated. You can now log in with your new credentials.",
            "password_reset_title"                   => "Reset Password",
            "password_security"                      => "Password Security",
            "patreon"                                => "Patreon",
            "payment"                                => "Payment",
            "payment_details"                        => "Payment Details",
            "payment_history"                        => "Payment History",
            "payment_history_exp"                    => "Review your past transactions, track your spending, and download your invoices.",
            "payment_method"                         => "Payment Method",
            "payment_method_exp"                     => "Select your preferred payment provider. You will finalize the payment in the next step.",
            "payment_option_load_error"              => "The selected payment method is currently unavailable. Please choose a different method or try again later.",
            "payment_redirect_exp"                   => "You will be redirected securely to complete the payment.",
            "payment_settings"                       => "Payment Settings",
            "payment_successful"                     => "Payment Successful!",
            "paytabs"                                => "Paytabs",
            "paywall_ad_free"                        => "Ad-free, uninterrupted browsing experience",
            "paywall_already_member_log_in"          => "Already a member? Log in",
            "paywall_already_purchased_log_in"       => "Already purchased? Log in",
            "paywall_appearance"                     => "Paywall Appearance",
            "paywall_elevate_your_experience"        => "Elevate Your Experience",
            "paywall_exclusive_content"              => "Exclusive Content",
            "paywall_exclusive_desc"                 => "This is exclusive content. Purchase access to unlock full access and explore the complete experience.",
            "paywall_interactive_features"           => "Interactive features and community access",
            "paywall_lifetime_access"                => "Lifetime access to this specific content",
            "paywall_one_time_payment"               => "One-time payment, no subscription required",
            "paywall_premium_desc"                   => "This is premium content. Upgrade your account to unlock full access and explore the complete experience.",
            "paywall_premium_subscription"           => "Premium Subscription",
            "paywall_subscribe_now"                  => "Subscribe Now",
            "paywall_unlimited_access_to_contents"   => "Unlimited access to all premium contents",
            "paywall_unlock_access"                  => "Unlock Access",
            "paywall_unlock_full_content_premium"    => "Unlock the Full Content with Premium",
            "paywall_unlock_this_exclusive_content"  => "Unlock This Exclusive Content",
            "pay_per_content"                        => "Pay-per-content",
            "pending_comments_exp"                   => "Comments awaiting administrative approval.",
            "pending_payouts"                        => "Pending Payouts",
            "pending_posts_exp"                      => "Posts awaiting administrative approval.",
            "permalink"                              => "Permalink",
            "permission_denied"                      => "Permission denied. Your account doesn’t have the required privileges!",
            "photo_gallery"                          => "Photo Gallery",
            "photo_gallery_exp"                      => "Explore our complete collection of visual stories, capturing moments of elegance, style, and raw emotion.",
            "plans"                                  => "Plans",
            "plan_features"                          => "Plan Features",
            "plan_name"                              => "Plan Name",
            "png_format"                             => "PNG Format",
            "png_logo_format_exp"                    => "PNG logo version required for email clients and social media sharing.",
            "popular"                                => "Popular",
            "popular_posts_limit"                    => "Popular Posts Limit",
            "post_content"                           => "Post Content",
            "post_format"                            => "Post Format",
            "post_reply"                             => "Post Reply",
            "premium"                                => "Premium",
            "premium_category"                       => "Premium Category",
            "premium_category_exp"                   => "If enabled, all posts under this category will require a premium membership to be viewed.",
            "premium_content"                        => "Premium Content",
            "premium_content_exp"                    => "Restrict access to premium members only.",
            "premium_content_mode"                   => "Premium Content Mode",
            "premium_membership"                     => "Premium Membership",
            "premium_users"                          => "Premium Users",
            "price"                                  => "Price",
            "print"                                  => "Print",
            "privacy_policy_url"                     => "Privacy Policy URL",
            "privacy_preference_center"              => "Privacy Preference Center",
            "privacy_preference_center_exp"          => "Manage your consent preferences.",
            "proceed_to_payment"                     => "Proceed to Payment",
            "proceed_to_payment_exp"                 => "You can verify your order and taxes on the next page before any payment is processed.",
            "processing"                             => "Processing...",
            "production"                             => "Production",
            "profile_id"                             => "Profile ID",
            "profile_url"                            => "Profile URL",
            "publication_date"                       => "Publication Date",
            "public_interaction_content"             => "Public Interaction Content (Comments, user profiles)",
            "public_key"                             => "Public Key",
            "public_open_to_everyone"                => "Public (Open to everyone)",
            "public_url"                             => "Public URL",
            "publishable_key"                        => "Publishable Key",
            "publisher_center_feed_app"              => "Publisher Center Feed (for App)",
            "publisher_center_feed_app_exp"          => "This is your primary feed containing all latest news. Use this URL in Google Publisher Center. To create separate sections (e.g., specific Categories or Languages), please use the Feed Link Generator tool above.",
            "publish_directly"                       => "Publish Directly",
            "publish_status"                         => "Publish Status",
            "purchased"                              => "Purchased",
            "purchased_content"                      => "Purchased Content",
            "purchased_content_exp"                  => "Access and explore all the premium content you have purchased.",
            "quora"                                  => "Quora",
            "randomly"                               => "Randomly",
            "razorpay"                               => "Razorpay",
            "reading_list_feature"                   => "Reading List Feature",
            "recipe_images"                          => "Recipe Images",
            "recipe_video_title"                     => "Watch How to Make It",
            "recipient"                              => "Recipient",
            "recipients"                             => "Recipients",
            "reddit"                                 => "Reddit",
            "redirect_url"                           => "Redirect URL",
            "reference"                              => "Reference",
            "region"                                 => "Region",
            "registered_users_only"                  => "Registered Users Only",
            "register_now"                           => "Register Now",
            "registration_closed"                    => "Registration Closed",
            "registration_deadline"                  => "Registration Deadline",
            "registration_rate"                      => "Registration Date",
            "registration_rules"                     => "Registration Rules",
            "registration_successful"                => "Your registration has been successfully completed!",
            "registration_type"                      => "Registration Type",
            "reject"                                 => "Reject",
            "related_posts_limit"                    => "Related Posts Limit",
            "remove_breaking_news"                   => "Remove from Breaking News",
            "remove_links_keep_text"                 => "Remove Links (Keep Text)",
            "reply"                                  => "Reply",
            "require_admin_approval_edited_posts"    => "Require Admin Approval for Edited Posts",
            "require_admin_approval_new_posts"       => "Require Admin Approval for New Posts",
            "require_numbers_special_characters"     => "Require numbers and special characters",
            "require_numbers_special_characters_exp" => "Users must use at least one number and special character if enabled.",
            "routes"                                 => "Routes",
            "sandbox"                                => "Sandbox",
            "saturday"                               => "Saturday",
            "save_preferences"                       => "Save Preferences",
            "scheduled"                              => "Scheduled",
            "section_title"                          => "Section Title",
            "secure_checkout"                        => "Secure Checkout",
            "security"                               => "Security",
            "security_check"                         => "Security Check",
            "security_check_exp"                     => "Please complete the captcha to verify you are human.",
            "selected_content_only"                  => "Selected Content Only",
            "selected_content_only_exp"              => "Apply premium rules only to specifically selected categories or individual posts.",
            "selected_file_s"                        => "Selected File(s):",
            "select_at_least_one_recipient"          => "Please select at least one recipient to send the email.",
            "select_date"                            => "Select date",
            "select_date_range"                      => "Select date range",
            "select_plan"                            => "Select Plan",
            "select_your_subscription_plan"          => "Select Your Subscription Plan",
            "select_your_subscription_plan_exp"      => "Gain full access to all premium content. Choose the plan that suits you best to enjoy an uninterrupted, ad-free reading experience across all your devices.",
            "sender_email_address"                   => "Sender Email Address",
            "sending"                                => "Sending",
            "send_verification_email"                => "Send Verification Email",
            "show_full_content"                      => "Show Full Content",
            "show_sidebar"                           => "Show Sidebar",
            "show_summary_only"                      => "Show Summary Only",
            "sign_up"                                => "Sign Up",
            "single_content_sales"                   => "Single Content Sales",
            "single_content_sales_exp"               => "Allows you to sell specific posts individually, even to users who are not subscribed.",
            "snapchat"                               => "Snapchat",
            "social_accounts_exp"                    => "Link your social media profiles to display them publicly.",
            "social_login"                           => "Social Login",
            "social_media"                           => "Social Media",
            "sorting_logic"                          => "Sorting Logic",
            "sort_order"                             => "Sort Order",
            "soundcloud"                             => "SoundCloud",
            "space_separator"                        => "Space",
            "spam_protection"                        => "Spam Protection",
            "spam_protection_exp"                    => "Manage how external links are processed to protect your SEO score and prevent spam.",
            "speakers_guests"                        => "Speakers & Guests",
            "start"                                  => "Start",
            "start_date"                             => "Start Date",
            "start_exploring"                        => "Start Exploring",
            "start_import"                           => "Start Import",
            "stay_updated"                           => "Stay Updated",
            "steam"                                  => "Steam",
            "strictly_necessary"                     => "Strictly Necessary",
            "strictly_necessary_exp"                 => "Required for security and basic functions. Cannot be disabled.",
            "strict_paywall"                         => "Strict Paywall",
            "strict_paywall_exp"                     => "Blocks access to the post page completely. Not recommended for SEO.",
            "stripe"                                 => "Stripe",
            "submitting"                             => "Submitting...",
            "subscribe_button"                       => "Subscribe Button",
            "subscription"                           => "Subscription",
            "subscription_already_subscribed"        => "You are already subscribed to this plan.",
            "subscription_badges"                    => "Subscription Badges",
            "subscription_button_text_change"        => "The button text can be changed in the language settings section by translating the \"btn_subscribe\" key.",
            "subscription_cancelled_successfully"    => "Your subscription has been successfully cancelled. You will continue to have access to all premium features until the end of your current billing period.",
            "subscription_free_plan_activated"       => "Great! Your free plan has been successfully activated. You can view your subscription details below and start exploring the platform right away.",
            "subscription_free_plan_used"            => "Free plan used",
            "subscription_plan"                      => "Subscription Plan",
            "subscription_plans"                     => "Subscription Plans",
            "subscription_system_status_exp"         => "Turn on to enable subscription plans and paywalls across your website.",
            "subtotal"                               => "Subtotal",
            "succeeded"                              => "Succeeded",
            "success"                                => "Success",
            "success_message"                        => "Success Message",
            "success_message_exp"                    => "Message shown after submit",
            "sunday"                                 => "Sunday",
            "symbol_direction"                       => "Symbol Direction",
            "system"                                 => "System",
            "system_tools"                           => "System Tools",
            "tax"                                    => "Tax",
            "tax_configuration"                      => "Tax Configuration",
            "tax_configuration_exp"                  => "Manage global VAT and country-specific overrides.",
            "tax_vat_number"                         => "Tax / VAT Number",
            "text_input"                             => "Text Input",
            "theme_color"                            => "Theme Color",
            "thousand_separator"                     => "Thousand Separator",
            "threads"                                => "Threads",
            "thursday"                               => "Thursday",
            "time"                                   => "Time",
            "titles"                                 => "Titles",
            "title_multiline_error"                  => "Please correct the following errors:",
            "today"                                  => "Today",
            "token"                                  => "Token",
            "tone_neutral"                           => "Neutral",
            "too_many_login_attempts"                => "Too many login attempts. Please try again after the wait time.",
            "topic_ai_exp"                           => "Briefly describe what you want the article to be about.",
            "topic_ai_placeholder"                   => "e.g. Benefits of drinking green tea...",
            "total"                                  => "Total",
            "total_earnings"                         => "Total Earnings",
            "total_paid"                             => "Total Paid",
            "total_payouts"                          => "Total Payouts",
            "total_registered_users"                 => "Total registered users",
            "total_submitted_posts"                  => "Total Submitted Posts",
            "transactions"                           => "Transactions",
            "transaction_details"                    => "Transaction Details",
            "transaction_fee"                        => "Transaction Fee",
            "transaction_fee_rate"                   => "Transaction Fee Rate",
            "transaction_id"                         => "Transaction ID",
            "transaction_type"                       => "Transaction Type",
            "tuesday"                                => "Tuesday",
            "tumblr"                                 => "Tumblr",
            "type_location_name"                     => "Type a location name...",
            "unique_pageviews"                       => "Unique Pageviews",
            "unverified"                             => "Unverified",
            "upcoming_events"                        => "Upcoming Events",
            "upcoming_events_exp"                    => "Overview of the upcoming schedule",
            "update_event"                           => "Update Event",
            "update_feed"                            => "Update Feed",
            "upload_up_to_10_files"                  => "Upload up to 10 files",
            "users_and_permissions"                  => "Users & Permissions",
            "users_permissions"                      => "Users & Permissions",
            "user_details"                           => "User Details",
            "user_profile_badge"                     => "User Profile Badge",
            "user_profile_options"                   => "User Profile Options",
            "user_profile_options_exp"               => "Select platforms allowed for users.",
            "use_content"                            => "Use Content",
            "vat"                                    => "VAT",
            "venue_name"                             => "Venue name",
            "verified"                               => "Verified",
            "verify_post_comment"                    => "Verify & Post Comment",
            "version"                                => "Version",
            "via_url"                                => "Via URL",
            "view"                                   => "View",
            "view_content"                           => "View Content",
            "view_details"                           => "View Details",
            "view_google_privacy_policy"             => "View Google Privacy Policy",
            "view_more_replies"                      => "View more replies",
            "view_my_purchases"                      => "View My Purchases",
            "vimeo"                                  => "Vimeo",
            "visit_hash"                             => "Visit Hash",
            "wait_time"                              => "Wait time",
            "warning_email_button_click"             => "Having trouble clicking the button? Copy and paste the URL below into your web browser:",
            "warning_invalid_email_request"          => "If you didn\'t make this request, you can safely ignore this email. The link will expire in 24 hours.",
            "webhook_secret_id"                      => "Webhook Secret/ID",
            "website"                                => "Website",
            "website_social_links"                   => "Website Social Links",
            "website_social_links_exp"               => "Enter URL to activate. Leave empty to disable.",
            "wednesday"                              => "Wednesday",
            "week"                                   => "Week",
            "welcome"                                => "Welcome",
            "welcome_exp_mobile"                     => "Unlock your personalized experience.",
            "where_to_display_exp"                   => "Only categories with Block Style 2, 3, or 4 can be selected. Other categories will not be shown as options.",
            "write_a_comment"                        => "Write a comment...",
            "write_a_reply"                          => "Write a reply...",
            "x_twitter"                              => "X (Twitter)",
            "yesterday"                              => "Yesterday",
            "you"                                    => "You",
            "your_subscription_currently_active"     => "Your subscription is currently active.",
            "you_dont_have_active_subscription"      => "You don\'t have an active subscription at the moment.",
            "zip_postal_code"                        => "Zip / Postal Code",
    ];


    try {
        if (!empty($labelsToAdd)) {
            $placeholders = [];
            $types = str_repeat('iss', count($labelsToAdd));

            foreach ($labelsToAdd as $label => $translation) {
                $placeholders[] = '(?, ?, ?)';
            }

            $sqlInsert = "INSERT IGNORE INTO `language_translations` (`lang_id`, `label`, `translation`) 
                          VALUES " . implode(', ', $placeholders);

            $stmtInsert = $connection->prepare($sqlInsert);
            if ($stmtInsert) {
                foreach ($languages as $langId) {
                    $flatValues = [];
                    foreach ($labelsToAdd as $label => $translation) {
                        $flatValues[] = $langId;
                        $flatValues[] = $label;
                        $flatValues[] = $translation;
                    }
                    $stmtInsert->bind_param($types, ...$flatValues);
                    $stmtInsert->execute();
                }
                $stmtInsert->close();
            }
        }
    } catch (\Throwable $e) {
    }

    return true;
}

function addTranslations($translations)
{
    global $connection;

    $languages = runQuery("SELECT * FROM languages;");
    if (!empty($languages->num_rows)) {
        while ($language = mysqli_fetch_array($languages)) {
            foreach ($translations as $key => $value) {
                $trans = runQuery("SELECT * FROM language_translations WHERE label ='" . $key . "' AND lang_id = " . $language['id']);
                if (empty($trans->num_rows)) {
                    $stmt = $connection->prepare("INSERT INTO language_translations (`lang_id`, `label`, `translation`) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $language['id'], $key, $value);
                    $stmt->execute();
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Varient - Update Wizard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #19bc9c;
            --primary-soft: #d1f2eb;
            --btn-dark: #1e293b;
            --btn-dark-hover: #0f172a;
            --bg: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            background-image: radial-gradient(var(--primary-soft) 1px, transparent 1px);
            background-size: 24px 24px;
            color: var(--text-main);
            min-height: 100vh;
            font-size: 16px;
        }

        .logo-cnt {
            text-align: center;
            padding: 60px 0 40px 0;
        }

        .logo-cnt h1 {
            font-size: 42px;
            font-weight: 800;
            color: var(--text-main);
            margin: 0;
            letter-spacing: -1px;
        }

        .logo-cnt p {
            font-size: 18px;
            color: var(--text-muted);
            margin-top: 5px;
        }

        .install-box {
            background: #ffffff;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
            margin-bottom: 100px;
            border: 1px solid var(--border);
        }

        .title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 0;
            text-align: center;
            color: var(--text-main);
        }

        .alert {
            border-radius: 12px;
            border: none;
        }

        .alert-success {
            background-color: #f0fdf4;
            color: #166534;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .form-control {
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid var(--border);
            transition: all 0.2s;
        }

        .form-control:focus {
            box-shadow: none;
            outline: none;
        }

        .btn-custom {
            background-color: var(--btn-dark) !important;
            border: none !important;
            color: #fff !important;
            font-weight: 600;
            height: 54px;
            border-radius: 12px;
            width: 100%;
            transition: all 0.2s;
            margin-top: 10px;
        }

        .btn-custom:hover {
            background-color: var(--btn-dark-hover) !important;
            transform: translateY(-1px);
        }

        .btn-custom:disabled {
            opacity: 0.7;
            transform: none;
        }

        .form-control::placeholder,
        .form-select::placeholder {
            color: #9ca3af !important;
            opacity: 1 !important;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-sm-12">
            <div class="row">
                <div class="col-sm-12 logo-cnt">
                    <h1>Varient</h1>
                    <p>Welcome to the Update Wizard</p>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="install-box">
                        <h2 class="title">Update from v2.1.x to v3.0</h2>
                        <br><br>

                        <div class="messages">
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger mb-4"><strong style="font-weight: 600;"><?= $error; ?></strong></div>
                            <?php endif; ?>
                            <?php if (!empty($success)): ?>
                                <div class="alert alert-success mb-4"><strong style="font-weight: 600;"><?= $success; ?></strong></div>
                            <?php endif; ?>
                        </div>

                        <div class="step-contents">
                            <?php if (empty($success)): ?>
                                <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" id="updateForm">
                                    <div class="alert alert-primary p-4 mb-5" style="text-align: left">
                                        <p class="text-center">
                                            <strong>Read Before You Begin</strong>
                                        </p>

                                        <p class="mb-2"><strong>Database Backup:</strong> We strongly recommend taking a complete backup of your database. You can export it as a .sql file using the "Export" feature in phpMyAdmin.</p>
                                        <p class="mb-2"><strong>Safe Recovery:</strong> If an unexpected error occurs or the process is interrupted, simply restore your database backup and try again.</p>
                                        <p class="mb-2"><strong>Update Duration:</strong> The duration depends on your database size. For large databases (e.g., over 20,000 posts), increasing the <strong>max_execution_time</strong> in your PHP configuration is recommended to prevent potential timeouts.</p>
                                        <p class="mb-0"><strong>No Refresh:</strong> Do not close this tab or refresh the page until the update is completed.</p>
                                    </div>

                                    <p class="text-center mb-3" style="font-weight: 500;">Enter your database credentials to update the database.</p>

                                    <div class="mb-3">
                                        <label class="form-label">Host</label>
                                        <input type="text" class="form-control" name="db_host" value="<?= !empty($data['db_host']) ? htmlspecialchars($data['db_host']) : 'localhost'; ?>" placeholder="Host" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Database Name</label>
                                        <input type="text" class="form-control" name="db_name" value="<?= !empty($data['db_name']) ? htmlspecialchars($data['db_name']) : ''; ?>" placeholder="Database Name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" class="form-control" name="db_user" value="<?= !empty($data['db_user']) ? htmlspecialchars($data['db_user']) : ''; ?>" placeholder="Database Username" required>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label">Password</label>
                                        <input type="text" class="form-control" name="db_password" value="<?= !empty($data['db_password']) ? htmlspecialchars($data['db_password']) : ''; ?>" placeholder="Database Password">
                                    </div>

                                    <button type="submit" name="btnUpdate" id="btnUpdate" class="btn btn-custom">Update My Database</button>
                                </form>

                                <div class="text-center mt-4">
                                    <p class="text-muted" style="font-size: 13px;">
                                        <strong>Tip:</strong> You can quickly find your current credentials in the <code>app/Config/Database.php</code> file.
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('updateForm')?.addEventListener('submit', function () {
        const btn = document.getElementById('btnUpdate');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating Database...';
    });
</script>
</body>
</html>