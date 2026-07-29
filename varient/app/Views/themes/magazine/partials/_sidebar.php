<div class="col-sidebar sticky-lg-top">
    <div class="row">
        <div class="col-12">
            <?php $i = 0;
            if (!empty($widgets)):
                foreach ($widgets as $widget):
                    if ($i == 1) {
                        echo loadCommonView('partials/_ad_spaces', ['adSpace' => 'sidebar_1', 'class' => 'mb-5']);
                    }
                    echo loadView('partials/_load_widget', ['widget' => $widget]);
                    $i++;
                endforeach;
            endif;
            echo loadCommonView('partials/_ad_spaces', ['adSpace' => 'sidebar_2', 'class' => 'mb-4']); ?>
        </div>
    </div>
</div>