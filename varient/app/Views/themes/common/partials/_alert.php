<?php
$session = session();

$uiSuccessMessage = $alertSuccess ?? $session->getFlashdata('success');
$uiSingleError = $alertError ?? $session->getFlashdata('error');
$uiErrorList = $alertErrors ?? $errors ?? $session->getFlashdata('errors') ?? [];

$finalDisplayError = '';
$isSingleErrorMode = false;
$finalErrorList = [];

if (!empty($uiSingleError)) {
    $isSingleErrorMode = true;
    $finalDisplayError = $uiSingleError;
} elseif (!empty($uiErrorList)) {
    if (count($uiErrorList) === 1) {
        $isSingleErrorMode = true;
        $finalDisplayError = array_values($uiErrorList)[0];
    } else {
        $isSingleErrorMode = false;
        $finalErrorList = $uiErrorList;
    }
}
?>

<?php if ($isSingleErrorMode): ?>
    <div class="mb-4">
        <div class="alert alert-custom alert-danger-custom fade show" role="alert">
            <div class="icon-box">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" x2="12" y1="8" y2="12"/>
                    <line x1="12" x2="12.01" y1="16" y2="16"/>
                </svg>
            </div>
            <div class="alert-content">
                <?= esc($finalDisplayError); ?>
            </div>
            <button type="button" class="btn-close-custom" data-bs-dismiss="alert" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    </div>
<?php elseif (!empty($finalErrorList)): ?>
    <div class="mb-4">
        <div class="alert alert-custom alert-danger-custom alert-multiline fade show" role="alert">
            <div class="alert-content">
                <h5 class="alert-heading mb-2"><?= trans("title_multiline_error"); ?></h5>
                <ul class="m-0">
                    <?php foreach ($finalErrorList as $err): ?>
                        <li><?= esc($err) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
            <button type="button" class="btn-close-custom" data-bs-dismiss="alert" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    </div>
<?php endif; ?>

<?php if ($uiSuccessMessage): ?>
    <div class="mb-4">
        <div class="alert alert-custom alert-success-custom fade show" role="alert">
            <div class="icon-box">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="alert-content">
                <?= esc($uiSuccessMessage); ?>
            </div>
            <button type="button" class="btn-close-custom" data-bs-dismiss="alert" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    </div>
<?php endif; ?>

<?php
$session->remove('success');
$session->remove('error');
$session->remove('errors');
?>
