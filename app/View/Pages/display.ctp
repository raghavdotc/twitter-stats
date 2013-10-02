<div>
    <?php if (!$this->Session->read('user_id')): ?>
        You are only welcome, if you'd login with twitter!
    <?php else : ?>
        Welcome to my app
    <?php endif; ?>

</div>