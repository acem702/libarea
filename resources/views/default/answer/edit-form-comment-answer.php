<div class="answer_addentry">
  <?php if ($data['user_id'] > 0) : ?>
    <form id="add_answ" class="new_answer" action="/answer/edit" accept-charset="UTF-8" method="post">
      <?= csrf_field() ?>
      <textarea rows="5" name="answer" id="answer"><?= $data['answer_content']; ?></textarea>
      <div>
        <input type="hidden" name="post_id" id="post_id" value="<?= $data['post_id']; ?>">
        <input type="hidden" name="answer_id" id="answer_id" value="<?= $data['answer_id']; ?>">
        <div class="boxline">
          <input type="submit" class="button block br-rd5 white" name="answit" value="<?= lang('edit'); ?>">
          <input id="cancel_answ" class="cancel gray" type="button" value="<?= lang('cancel'); ?>">
        </div>
      </div>
    </form>
  <?php endif; ?>
</div>