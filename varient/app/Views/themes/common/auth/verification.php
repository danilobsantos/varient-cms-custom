<?= $this->extend($viewsPath . '/layout'); ?>

<?= $this->section('styles'); ?>
    <style>
        .page-verification{width:500px;max-width:100%;height:auto;margin:0 auto;text-align:center;padding-top:120px;max-width:100%}.circle-loader,.error-circle{margin-bottom:3.5em;position:relative;vertical-align:top;width:7em;height:7em}.page-verification .title{font-size:2rem;font-weight:700;margin-bottom:1rem}.page-verification .description{font-size:1rem;margin-bottom:2rem;color:var(--vr-gray-600)}.circle-loader{border:1px solid rgba(0,0,0,.2);border-left-color:#5cb85c;animation:1.2s linear infinite loader-spin;display:inline-block;border-radius:50%}.load-complete{-webkit-animation:none;animation:none;border-color:#5cb85c;transition:border .5s ease-out}.checkmark{display:none}.checkmark.draw:after{animation-duration:.8s;animation-timing-function:ease;animation-name:checkmark;transform:scaleX(-1) rotate(135deg)}.checkmark:after{opacity:1;height:3.5em;width:1.75em;transform-origin:left top;border-right:3px solid #5cb85c;border-top:3px solid #5cb85c;content:'';left:1.75em;top:3.5em;position:absolute}@keyframes loader-spin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}@keyframes checkmark{0%{height:0;width:0;opacity:1}20%{height:0;width:1.75em;opacity:1}100%,40%{height:3.5em;width:1.75em;opacity:1}}@keyframes rotate{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}.rotate{animation:1s linear infinite rotate}.error-circle{border:1px solid #dc3545;display:inline-block;border-radius:50%;line-height:7em;color:#dc3545}@keyframes slidein{from{margin-left:100%;width:300%}to{margin-left:0;width:100%}}
    </style>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

    <section class="section section-page">
        <div class="container-xl">
            <div class="row">
                <div class="page-verification">

                    <?php if($pageType === 'email_verification'): ?>

                    <?php if ((int)$status === 1): ?>
                        <div class="circle-loader">
                            <div class="checkmark draw"></div>
                        </div>

                        <h1 class="title">
                            <?= trans("email_verify_success_title"); ?>
                        </h1>

                        <p class="description">
                            <?= trans("email_verify_success_message"); ?>
                        </p>
                    <?php else: ?>
                        <div class="error-circle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                            </svg>
                        </div>

                        <h1 class="title">
                            <?= trans("error"); ?>!
                        </h1>

                        <p class="description">
                            <?= trans("msg_error"); ?>
                        </p>
                    <?php endif; ?>

                    <?php elseif ($pageType === 'newsletter_remove'): ?>

                        <div class="circle-loader">
                            <div class="checkmark draw"></div>
                        </div>
                        <h1 class="title">
                            <?= trans("newsletter_remove_successful"); ?>
                        </h1>

                        <p class="description">
                            <?= trans("msg_newsletter_remove"); ?>
                        </p>

                    <?php endif; ?>

                    <a href="<?= langBaseUrl(); ?>" class="btn btn-custom d-inline-flex px-5"><?= trans("go_to_homepage"); ?></a>

                </div>

            </div>
        </div>
    </section>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
    <script>
        $(document).ready(function () {
            $('.circle-loader').toggleClass('load-complete');
            $('.checkmark').toggle();
        });
    </script>
<?= $this->endSection(); ?>