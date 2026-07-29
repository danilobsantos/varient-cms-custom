<div class="row mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= langBaseUrl(); ?>"><?= trans("home"); ?></a></li>
            <li class="breadcrumb-item"><a href="<?= generateURL('account-settings'); ?>"><?= trans("account_settings"); ?></a></li>
            <li class="breadcrumb-item active"><?= esc($title); ?></li>
        </ol>
    </nav>

    <h1 class="page-title"><?= trans("account_settings"); ?></h1>
</div>