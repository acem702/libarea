<main class="col-two">
  <?= Tpl::insert('/content/user/setting/nav', ['data' => $data]); ?>

  <div class="bg-white box">
    <form method="POST" action="<?= getUrlByName('setting.avatar.edit'); ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>

      <div class="file-upload mb10" id="file-drag">
        <div class="flex">
          <?= Html::image($data['user']['avatar'], $data['user']['login'], 'img-xl', 'avatar', 'max'); ?>
          <img id="file-image" src="/assets/images/1px.jpg" alt="" class="img-xl">
          <div id="start" class="mt15">
            <input id="file-upload" type="file" name="images" accept="image/*" />
            <div id="notimage" class="none"><?= __('select.image');?></div>
          </div>
        </div>
        <div id="response" class="hidden">
          <div id="messages"></div>
        </div>
      </div>

      <div class="clear gray mb10">
        <div class="mb5 text-sm"><?= __('recommended.size'); ?>: 240x240px (jpg, jpeg, png)</div>
        <?= Html::sumbit(__('download')); ?>
      </div>

      <div class="file-upload mt20 mb10" id="file-drag">
        <div class="flex">
          <?php if ($data['user']['cover_art'] != 'cover_art.jpeg') : ?>
            <div class="relative mr15">
              <img class="block br-gray max-w-100" src="<?= Html::coverUrl($data['user']['cover_art'], 'user'); ?>">
              <a class="right text-sm" href="<?= getUrlByName('delete.cover', ['login' => $user['login']]); ?>">
                <?= __('remove'); ?>
              </a>
            </div>
          <?php else : ?>
            <div class="block br-gray max-w-100 text-sm gray p20 mr15">
              <?= __('no.cover'); ?>...
            </div>
          <?php endif; ?>
          <div id="start">
            <img id="file-image bi bi-cloud-download" src="/assets/images/1px.jpg" alt="" class="h94">

            <input id="file-upload" type="file" name="cover" accept="image/*" />
            <div id="notimage" class="none">Please select an image</div>
          </div>
        </div>
        <div id="response" class="hidden">
          <div id="messages"></div>
        </div>
      </div>

      <div class="clear gray mb10">
        <div class="mb5 text-sm"><?= __('recommended.size'); ?>: 1920x240px (jpg, jpeg, png)</div>
        <?= Html::sumbit(__('download')); ?>
      </div>
    </form>
  </div>
</main>
<aside>
  <div class="box bg-violet text-sm">
    <?= __('avatar.info'); ?>
  </div>
</aside>