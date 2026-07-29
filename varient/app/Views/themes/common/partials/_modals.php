<?php
if (!authCheck() && $config->registration_system == 1):
    echo loadCommonView('auth/_login_modal');
endif;

if (!empty($isPostPage) && !empty($post) && $premiumMembership->status):
    echo loadCommonView('premium/_modals');
endif;

?>