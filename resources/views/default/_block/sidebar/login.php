<?php
$form = new Forms();
$form->html_form($user['trust_level'], Config::get('form/auth.login'));
?>
<div class="box-white text-sm">
  <form action="<?= getUrlByName('login'); ?>" method="post">
    <?php csrf_field(); ?>
    <?= $form->build_form(); ?>
    <fieldset>
      <?= Html::sumbit(__('sign.in')); ?>
    </fieldset>
    <fieldset class="gray-600 center">
      <?= __('login.use.condition'); ?>
      <a href="<?= getUrlByName('recover'); ?>"><?= __('forgot.password'); ?>?</a>
    </fieldset>
  </form>
</div>