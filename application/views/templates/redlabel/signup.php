<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
 <div class="inner-nav">
    <div class="container">
        <a href="<?= LANG_URL ?>"><?= lang('home') ?></a> <span class="active"> > <?= lang('user_login') ?></span>
    </div>
</div>
<div class="container user-page">
<div class="row">
<div class="col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4">
<div class="loginmodal-container">
<div class="list-group">
    <h1 class="list-group-item active">Inscription</h1><br>
        <form method="POST" action="">
        <input class="form-control" type="text" name="name" placeholder="Name"><br>
        <input class="form-control" type="text" name="phone" placeholder="Phone"><br>
        <input class="form-control" type="text" name="email" placeholder="Email"><br>
        <input class="form-control" type="password" name="pass" placeholder="Password"><br>
        <input class="form-control" type="password" name="pass_repeat" placeholder="Password repeat"><br>
        <input class="btn btn-primary" type="submit" name="signup" class="login loginmodal-submit" value="<?= lang('register_me') ?>">
        </form>
</div>
</div>
</div>
</div>
</div>