<div class="answer_addentry">
  <?php if ($data['id'] > 0) : ?>
    <form id="add_answ" class="new_answer" action="/answer/edit" accept-charset="UTF-8" method="post">
      <?= csrf_field() ?>
      <textarea rows="5" name="answer" id="answer"><?= $data['answer_content']; ?></textarea>
      <div>
        <input type="hidden" name="post_id" id="post_id" value="<?= $data['post_id']; ?>">
        <input type="hidden" name="answer_id" id="answer_id" value="<?= $data['answer_id']; ?>">
        <div class="mt5 mb20">
          <?= Html::sumbit(__('edit')); ?>
          <span id="cancel_answ" class="text-sm inline ml5 gray"><?= __('cancel'); ?></span>
        </div>
      </div>
    </form>
  <?php endif; ?>
</div>