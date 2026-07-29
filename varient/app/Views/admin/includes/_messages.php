<?php $session = session();
if ($session->getFlashdata('errors')):
    $errors = $session->getFlashdata('errors'); ?>
    <div class="alert alert-dismissible bg-light-danger d-flex flex-column flex-sm-row p-5 mb-10">
        <div class="d-flex flex-column pe-0 pe-sm-10">
            <ul class="mb-0">
                <?php foreach ($errors as $error) : ?>
                    <li class="fs-5 text-danger"><?= esc($error) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
        <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon w-auto h-auto ms-sm-auto" data-bs-dismiss="alert">
            <i class="ki-duotone ki-cross fs-1 text-danger"><span class="path1"></span><span class="path2"></span></i>
        </button>
    </div>
<?php endif;
if ($session->getFlashdata('error')): ?>
    <div class="alert alert-dismissible bg-light-danger d-flex flex-column flex-sm-row align-items-center p-5 mb-10">
        <i class="ki-duotone ki-information-5 fs-2hx text-danger me-4 mb-5 mb-sm-0"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
        <div class="d-flex flex-column pe-0 pe-sm-10">
            <h4 class="fw-semibold text-danger fs-4 mb-0"><?= esc($session->getFlashdata('error')); ?></h4>
        </div>
        <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon w-auto h-auto ms-sm-auto" data-bs-dismiss="alert">
            <i class="ki-duotone ki-cross fs-1 text-danger"><span class="path1"></span><span class="path2"></span></i>
        </button>
    </div>
<?php elseif ($session->getFlashdata('success')): ?>
    <div class="alert alert-dismissible bg-light-success d-flex flex-column flex-sm-row align-items-center p-5 mb-10">
        <i class="ki-duotone ki-check-square fs-2hx text-success me-4 mb-5 mb-sm-0"><span class="path1"></span><span class="path2"></span></i>
        <div class="d-flex flex-column pe-0 pe-sm-10">
            <h4 class="fw-semibold text-success fs-4 mb-0"><?= esc($session->getFlashdata('success')); ?></h4>
        </div>
        <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon w-auto h-auto ms-sm-auto" data-bs-dismiss="alert">
            <i class="ki-duotone ki-cross fs-1 text-success"><span class="path1"></span><span class="path2"></span></i>
        </button>
    </div>
<?php endif; ?>