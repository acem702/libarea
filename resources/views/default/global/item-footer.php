<footer class="footer box-shadow-top bg-lightgray">
  <div class="m10">
    <?= config('meta.name'); ?> &copy; <?= date('Y'); ?> — <span class="lowercase"><?= __('web.main_title'); ?></span>
  </div>
</footer>

<?= insert('/scripts'); ?>