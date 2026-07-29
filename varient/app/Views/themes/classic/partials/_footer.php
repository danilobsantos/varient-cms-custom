<?php if (authCheck()): ?>
<?= loadCommonView('post/_add_post_modal'); ?>
<?php endif; ?>

<?= loadCommonView("partials/_newsletter_popup"); ?>

<footer id="footer">
<div class="footer-inner">
<div class="container">
<div class="row justify-content-between">
<div class="col-sm-12 col-md-6 col-lg-4 footer-widget footer-widget-about">
<div class="footer-logo">
<img src="<?= getLogo('dark'); ?>" alt="logo" class="logo" width="<?= getLogoSize()->width; ?>" height="<?= getLogoSize()->height; ?>">
</div>
<div class="footer-about">
<?= esc($baseSettings->about_footer); ?>
</div>
<div class="footer-social-links">
<?= view('themes/common/partials/_social_media_links', ['rssHide' => false]); ?>
</div>
</div>
<div class="col-sm-12 col-md-6 col-lg-4 footer-widget">
<h4 class="widget-title"><?= trans("trending_posts"); ?></h4>
<div class="footer-posts">
<?= view_cell('App\Cells\TrendingPosts', ['latestCategoryPosts' => $latestCategoryPosts]); ?>
</div>
</div>
<div class="col-sm-12 col-md-6 col-lg-4 footer-widget">
<?php if ((int)$config->newsletter_settings->status === 1): ?>
<h4 class="widget-title"><?= trans("newsletter"); ?></h4>
<div class="newsletter">
<p class="description"><?= trans("newsletter_join_desc"); ?></p>
<form id="form_newsletter_footer" class="form-newsletter">
<div class="newsletter-inputs">
<input type="email" name="email" class="form-control form-input newsletter-input" maxlength="199" placeholder="<?= trans("email"); ?>">
<button type="submit" name="submit" value="form" class="btn btn-custom newsletter-button"><?= trans("join"); ?></button>
</div>
<input type="text" name="url">
</form>
</div>
<?php endif; ?>
</div>
</div>
</div>
</div>
<div class="footer-copyright">
<div class="container">
<div class="row align-items-center">
<div class="col-sm-12 col-md-6">
<div class="copyright text-start">
<?= esc($baseSettings->copyright); ?>
</div>
</div>
<div class="col-sm-12 col-md-6">
<div class="nav-footer text-end">
<ul>
<?php if (!empty($menuLinks)):
foreach ($menuLinks as $item):
if ($item->location == "footer"):?>
<li><a href="<?= generateNavItemUrl($item); ?>"><?= esc($item->title); ?> </a></li>
<?php endif;
endforeach;
endif; ?>
</ul>
</div>
</div>
</div>
</div>
</div>
</footer>

<button type="button" class="btn-scroll-up" aria-label="Scroll back to top of the page">
<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
<path d="m18 15-6-6-6 6"/>
</svg>
</button>

<?php if ((int)$config->g_analytics_status === 1):
echo loadCommonView("partials/_google_analytics");
endif; ?>

<script src="<?= base_url('assets/common/js/plugins.js'); ?>"></script>
<script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
<script src="<?= base_url('assets/common/js/main.min.js'); ?>"></script>

<?php if ((int)$config->pwa_status === 1):
echo loadCommonView("partials/_pwa");
endif; ?>

<?= $config->custom_footer_codes; ?>

<?= $this->renderSection('scripts'); ?>

</body>
</html>