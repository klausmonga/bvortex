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
        <div class="col-sm-4">
            <div class="loginmodal-container">
                <h1> Mon profil</h1><br>
                <form method="POST" action="">
                    <input class="form-control" type="text" name="name" placeholder="<?= $userInfo['name'] ?>" ><br>
                    <input class="form-control" type="text" name="phone" placeholder="<?= $userInfo['phone'] ?>" ><br>
                    <input class="form-control" type="text" name="email"  placeholder="<?= $userInfo['email'] ?>" ><br>
                    <input class="form-control" type="password" name="pass" value="Password (leave blank if no change)"><br> 
                    <input class="btn btn-primary" type="submit" name="update" class="login loginmodal-submit" value="<?= lang('update') ?>">
                    <a href="<?= LANG_URL . '/logout' ?>" class="login loginmodal-submit text-center"><?= lang('logout') ?></a>
                </form>
            </div>
        </div>
        <div class="col-sm-8">
            <?= lang('user_order_history') ?>
            <div class="table-responsive">
                <table class="table table-condensed table-bordered table-striped">
                    <thead>
                        <tr>
                            <th> Réf de la livraison</th>
                            <th><?= lang('usr_order_date') ?></th>
                            <th><?= lang('usr_order_address') ?></th>
                            <th><?= lang('usr_order_phone') ?></th>
                            <th><?= lang('user_order_products') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // var_dump($orders_history);
                        if (!empty($orders_history)) {
                            foreach ($orders_history as $order) {
                                ?>
                                <tr>
                                    <td><h1 id ="labelresponse" class="label label-success"><?= $order['ref'] ?></h1></td>
                                    <td><?= date('d.m.Y', $order['date']) ?></td>
                                    <td><?= $order['address'] ?></td>
                                    <td><?= $order['phone'] ?></td>
                                    <td>    
                                        <?php
                                        $arr_products = unserialize($order['products']);
                                        foreach ($arr_products as $product_id => $product_quantity) {
                                            $productInfo = modules::run('admin/ecommerce/products/getProductInfo', $product_id, true);
                                            ?>
                                            <div style="word-break: break-all;">
                                                <div>
                                                    <img src="<?= base_url('attachments/shop_images/' . $product_quantity['product_info']['image']) ?>" alt="Product" style="width:100px; margin-right:10px;" class="img-responsive">
                                                </div>
                                                <a target="_blank" href="<?= base_url($product_quantity['product_info']['url']) ?>">
                                                    <?= base_url($productInfo['url']) ?> 
                                                </a> 
                                                <div style=" background-color: #f1f1f1; border-radius: 2px; padding: 2px 5px;"><b><?= lang('user_order_quantity') ?></b> <?= $product_quantity['product_quantity'] ?></div>
                                                <div class="clearfix"></div>
                                            </div>
                                            <hr>
                                        <?php }
                                        ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="5"><?= lang('usr_no_orders') ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?= $links_pagination ?>
            </div>
        </div>
    </div>
</div>

