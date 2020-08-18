<link rel="stylesheet" href="<?= base_url('assets/bootstrap-select-1.12.1/bootstrap-select.min.css') ?>">
<div class="content orders-page" >
<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Validation de la livraison</h4>
      </div>
      <div class="modal-body">
            Colis livré avec succès        
      </div>
    </div>
  </div>
</div>
<div class="responsive-table">
<div class="row">
  <div class="col-md-8">
    <table class="table">
            <thead class="blue-grey lighten-4">
                <tr>
                    <th>#</th>
                    
                    <th> date de la commande</th>
                    <th>date de livraison</th>
                    <th>Numéro YetuPay</th>
                    <th><?= lang('status') ?></th>
                    <th class="text-right"><i class="fa fa-list" aria-hidden="true"></i></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 0;
                // var_dump($shippings);
                foreach ($shippings as $shipping) {
                    // var_dump($shipping);
                    ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><?= $shipping['creat_date'] ?></td>
                        <td><?= $shipping['delivery_date'] ?></td>
                        <td><?= $shipping['phone'] ?></td>
                        <td>
                            <?php if($shipping['state']==1) echo "Livré"; else if($shipping['state']==2) echo "A livrer à Bvortex";else if($shipping['state']==-1) echo "paiement echoué";else if($shipping['state']==3) echo "A récupérer à Bvortex"; else echo "A livrer à domicile"?></option>
                        </td>
                        <td class="text-right">
                            <a href="javascript:void(0);" class="btn btn-sm btn-green show-more" data-show-tr="<?= $i ?>">
                                <i class="fa fa-chevron-down" aria-hidden="true"></i>
                                <i class="fa fa-chevron-up" aria-hidden="true"></i>
                            </a>
                        </td>
                    </tr>
                    <tr class="tr-more" data-tr="<?= $i ?>">
                        <td colspan="6">
                            <div class="row">
                                <div class="col-sm-6">
                                    <ul>
                                        <li>
                                            <b><?= lang('first_name') ?></b> <span><?= $shipping['first_name'] ?></span>
                                        </li>
                                        <li>
                                            <b><?= lang('last_name') ?></b> <span><?= $shipping['last_name'] ?></span>
                                        </li>
                                        <li>
                                            <b><?= lang('email') ?></b> <span><?= $shipping['email'] ?></span>
                                        </li>
                                        <li>
                                            <b><?= lang('phone') ?></b> <span><?= $shipping['phone'] ?></span>
                                        </li>
                                        <li>
                                            <b><?= lang('address') ?></b> <span><?= $shipping['address'] ?></span>
                                        </li>
                                        <li>
                                            <b><?= lang('city') ?></b> <span><?= $shipping['city'] ?></span>
                                        </li>
                                        <li>
                                            <b><?= lang('post_code') ?></b> <span><?= $shipping['post_code'] ?></span>
                                        </li>
                                        <li>
                                            <b><?= lang('notes') ?></b> <span><?= $shipping['notes'] ?></span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-sm-6">
                                    <?php
                                    $product = unserialize($shipping['products']);
                                    foreach ($product as $prod_id => $prod_qua) {
                                        $productInfo = modules::run('vendor/orders/getProductInfo', $prod_id, $shipping['id_vendor']);
                                        if($prod_qua['product_info']['vendor_id']==$shipping['id_vendor']){
                                        ?>
                                        <div class="product">
                                            <a href="" target="_blank">
                                                <img src="<?= base_url('/attachments/shop_images/' . $prod_qua['product_info']['image']) ?>" alt="">
                                                <div class="info">
                                                    <span class="qiantity">
                                                        <b><?= lang('quantity') ?></b> <?= $prod_qua['product_quantity'] ?>
                                                    </span>
                                                </div>
                                                <div class="clearfix"></div>
                                            </a>
                                        </div>
                                    <?php }} ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php
                    $i++;
                }
                ?>
            </tbody>
        </table>
  </div>
  <div class="col-md-4">
  <div class="panel panel-primary">
                        <div class="panel-heading">
                           <div class="custom-control custom-switch">
                                
                                <input type="checkbox" <?php if(json_encode($shipper) != "null")echo "checked" ?> name="is_for_delevery" id="customSwitch1" onclick="show_onchecked()">
                                <label class="custom-control-label" for="customSwitch1">Assurer la livraison à domicile</label>
                            </div>
                        </div>
                        <div class="panel-body" id="panelbody" style="display:none">
                        
                        <div class="input-group">
                            <span class="input-group-addon">$</span>
                            <input class="form-control" id="cout" type="text" name="cout" aria-label="Amount (to the nearest dollar)" value=<?php if(json_encode($shipper) != "null")echo $shipper['cout']; else echo "Frais Livraison"?> placeholder="Frais Livraison">
                            <span class="input-group-addon">.00</span>
                        </div>
                        <br><input class="form-control" id="delais" type="text" name="delais" value=<?php if(json_encode($shipper) != "null")echo '"'.$shipper['delais'].'"'; else echo "Delais de la livraison"?> placeholder="Delais de la livraison"><br>
                            <input class="btn btn-primary" id="setparamsbutton" type="submit" name="login"  value="Valider" data-loading-text="Loading..." autocomplete="off">
                            <span id ="labelresponse" class="label label-success">Avec Succès</span>
                        <h1>Validation d'une livraison</h1>
                        <!-- <form> -->
                        <div class="input-group">
                        
                            <input type="text" id ="ref" class="form-control" placeholder="Référence de la livraison">
                            <span class="input-group-btn">
                            <input class="btn btn-primary" id="validlivraison" type="submit" name="login"  value="Valider!" data-loading-text="Loading..." autocomplete="off">
                        
                            </span>
                            
                            </div>
                            <!-- </form>  -->
                        </div>
                    </div>
  </div>
</div>
</div>
<script src="<?= base_url('assets/bootstrap-select-1.12.1/js/bootstrap-select.min.js') ?>"></script>
<script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>

<script>
    $('#validlivraison').click(function(){
        var $btn = $(this).button('loading')
        var span_resp = document.getElementById("labelresponse");
        $.post("http://localhost/bvortex/vendor/shipping/validShipping",
                    {
                        ref: $('#ref').val(),
                    },
                    function(data, status){
                       
                        if (JSON.parse(data).state == '1') {
                          
                            $('#myModal').modal();    
                        }
                        
                        $btn.button('reset');
                    });
    });
    $('#setparamsbutton').click(function(){
        var $btn = $(this).button('loading')
        var span_resp = document.getElementById("labelresponse");
        $.post("http://localhost/bvortex/vendor/shipping/setShippingParams",
                    {
                        cout: $('#cout').val(),
                        delais: $('#delais').val()
                    },
                    function(data, status){
                        console.log(data);
                        span_resp.style.display = "inline"
                        $btn.button('reset')
                    });
    });
    function show_onchecked(){
            var checkBox = document.getElementById("customSwitch1");
            var text = document.getElementById("panelbody");
            
            var shipper = '<?= json_encode($shipper)?>';
            if (checkBox.checked==true) {
                $.post("http://localhost/bvortex/vendor/shipping/activeShipper",
                    {
                        key: "oxc555"
                    },
                    function(data, status){
                       
                        if (JSON.parse(data).state == '1') {
                            text.style.display = "block";
                        }else{
                            alert("Pas de connexion")
                        }
                            
                    });        
            }else {
                $.post("http://localhost/bvortex/vendor/shipping/removeShipper",
                    {
                        key: "oxc555"
                    },
                    function(data, status){
                       
                        if (JSON.parse(data).state == '0') {
                            text.style.display = "none";
                        }else{
                            alert("supp faild")
                        }
                            
                    });
            }
    }
        
    function show_onload(){ 
            var checkBox = document.getElementById("customSwitch1");
            var span_resp = document.getElementById("labelresponse");
            span_resp.style.display = "none"
            var text = document.getElementById("panelbody");
            
            var shipper = '<?= json_encode($shipper)?>';
            if ( JSON.parse(shipper).state == '0') {
                
                checkBox.checked = false;
                text.style.display = "none";
            } else {
                    checkBox.checked = true;
                    text.style.display = "block";
            }
        
        }






</script>