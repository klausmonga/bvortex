<?php
use yetupay\api\ProcessPayment;
defined('BASEPATH') OR exit('No direct script access allowed');

class Checkout extends MY_Controller
{

    private $orderId;
    private $orderId_auto_gene;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin/Orders_model');
        $this->load->model('vendor/Shipping_model');
        $this->load->model('Api_model');
    }

    public function index()
    {
        $data = array();
        $head = array();
        $arrSeo = $this->Public_model->getSeo('checkout');
        $head['title'] = @$arrSeo['title'];
        $head['description'] = @$arrSeo['description'];
        $head['keywords'] = str_replace(" ", ",", $head['title']);

        if (isset($_POST['payment_type'])) {
            $errors = $this->userInfoValidate($_POST);
            if (!empty($errors)) {
                $this->session->set_flashdata('submit_error', $errors);
            } else {
                $_POST['referrer'] = $this->session->userdata('referrer');
                $_POST['clean_referrer'] = cleanReferral($_POST['referrer']);
                $_POST['user_id'] = isset($_SESSION['logged_user']) ? $_SESSION['logged_user'] : 0;
                $orderId = $this->Public_model->setOrder($_POST);
                if ($orderId != false) {
                    /*
                     * Save product orders in vendors profiles
                     */
                    $this->setVendorOrders();
                    $this->orderId = $orderId;
                    $this->orderId_auto_gene = $this->Orders_model->getOrderByOrderId($orderId)['id'];
                    $this->goToDestination($this->setActivationLink());
                    $this->sendNotifications();
                } else {
                    log_message('error', 'Cant save order!! ' . implode('::', $_POST));
                    $this->session->set_flashdata('order_error', true);
                    redirect(LANG_URL . '/checkout/order-error');
                }
            }
        }
        
        $data['bank_account'] = $this->Orders_model->getBankAccountSettings();
        $data['cashondelivery_visibility'] = $this->Home_admin_model->getValueStore('cashondelivery_visibility');
        $data['paypal_email'] = $this->Home_admin_model->getValueStore('paypal_email');
        $data['bestSellers'] = $this->Public_model->getbestSellers();
        $this->render('checkout', $head, $data);
    }

    private function setVendorOrders()
    {
        $this->Public_model->setVendorOrder($_POST);
    }

    /*
     * Send notifications to users that have nofify=1 in /admin/adminusers
     */

    private function sendNotifications()
    {
        $users = $this->Public_model->getNotifyUsers();
        $myDomain = $this->config->item('base_url');
        if (!empty($users)) {
            foreach ($users as $user) {
                $this->sendmail->sendTo($user, 'Admin', 'New order in ' . $myDomain, 'Hello, you have new order. Can check it in /admin/orders');
            }
        }
    }

    private function setActivationLink()
    {
        if ($this->config->item('send_confirm_link') === true) {
            $link = md5($this->orderId . time());
            $result = $this->Public_model->setActivationLink($link, $this->orderId);
            if ($result == true) {
                $url = parse_url(base_url());
                $msg = lang('please_confirm') . base_url('confirm/' . $link);
                $this->sendmail->sendTo($_POST['email'], $_POST['first_name'] . ' ' . $_POST['last_name'], lang('confirm_order_subj') . $url['host'], $msg);
            }
        }
        return $link;
    }

    private function goToDestination($md5)
    {
        if ($_POST['payment_type'] == 'cashOnDelivery' || $_POST['payment_type'] == 'Bank') {
            $this->shoppingcart->clearShoppingCart();
            $this->session->set_flashdata('success_order', true);
        }
        if ($_POST['payment_type'] == 'Bank') {
            $_SESSION['order_id'] = $this->orderId;
            $_SESSION['final_amount'] = $_POST['final_amount'] . $_POST['amount_currency'];
            redirect(LANG_URL . '/checkout/successbank');
        }
        if ($_POST['payment_type'] == 'cashOnDelivery') {
            $pp = new ProcessPayment();
            $pp->addDev("3905d28f8827b57a944e36891abe6cff924ac54f940c9e53aca884b222e0d7cc",
                        "3905d28f8827b57a944e36891abe6cff924ac54f940c9e53aca884b222e0d7cc",
                        "0852093279",
                        "bvortex11");
            $pp->AddBill_to($_POST['phone']);
            $a = 0;
            $this->load->library('../controllers/Api/products');
            $obj = new $this->products();
            $this->load->library('../controllers/home');
            $home = new $this->home();
            $vendors = array();
            while ($a < sizeof($_POST['id'])) {
                $product = $obj->one_get("en",$_POST['id'][$a],true);
                $vendor = $this->Api_model->getVendorFromId($product['vendor_id']);
                $vendors[$a]=json_encode($vendor);
                $pp->addProduct($vendor['url'],$product['price'],$_POST['quantity'][$a],$product['title'],$product['title']);
                $a=$a+1;   
            }
            $vendors = array_unique($vendors);
            
            $a = 0;
            
            while ($a < sizeof($vendors)) {
                $shipper = $this->Shipping_model->getShipper(json_decode($vendors[$a])->{'id'});
                if(isset($_POST['is_for_delevery'])){
                    if ($shipper['state']!=0 ) {
                        $shipping_ids_tab[$a]=$this->Shipping_model->addShipping(array("cout"=>$shipper['cout'], "id_user"=>$_POST['user_id'], "id_shipper"=>$shipper['id'], "id_vendor"=>json_decode($vendors[$a])->{'id'}, "ref"=>time(), "state"=>0, "id_order"=>$this->orderId_auto_gene));
                        $pp->addProduct(json_decode($vendors[$a])->{'url'},$shipper['cout'],1,'livraison',json_decode($vendors[$a])->{'name'}.' livraison');    
                    }else{   
                        $shipping_ids_tab[$a]=$this->Shipping_model->addShipping(array("cout"=>$shipper['cout'], "id_user"=>$_POST['user_id'], "id_shipper"=>$shipper['id'], "id_vendor"=>json_decode($vendors[$a])->{'id'}, "ref"=>time(), "state"=>2, "id_order"=>$this->orderId_auto_gene));
                        $bvortex_shipper = $this->Shipping_model->getShipper(0);
                        $vendor = $this->Api_model->getVendorFromId($bvortex_shipper['id_vendor']);
                        $this->Shipping_model->addShipping(array("cout"=>$bvortex_shipper['cout'], "id_user"=>$_POST['user_id'], "id_shipper"=>$bvortex_shipper['id'], "id_vendor"=>$vendor['id'], "ref"=>time(), "state"=>0, "id_order"=>$this->orderId_auto_gene));
                        $pp->addProduct($vendor['url'],$bvortex_shipper['cout'],1,'livraison',$vendor['name'].' livraison');
                    }
                }else {
                        $shipping_ids_tab[$a]=$this->Shipping_model->addShipping(array("cout"=>$shipper['cout'], "id_user"=>$_POST['user_id'], "id_shipper"=>$shipper['id'], "id_vendor"=>json_decode($vendors[$a])->{'id'}, "ref"=>time(), "state"=>2, "id_order"=>$this->orderId_auto_gene));
                        $bvortex_shipper = $this->Shipping_model->getShipper(0);
                        $vendor = $this->Api_model->getVendorFromId($bvortex_shipper['id_vendor']);
                        $this->Shipping_model->addShipping(array("cout"=>0, "id_user"=>$_POST['user_id'], "id_shipper"=>$bvortex_shipper['id'], "id_vendor"=>$vendor['id'], "ref"=>time(), "state"=>3, "id_order"=>$this->orderId_auto_gene));
                }
                $a=$a+1;
            }
            
            
            $pp->addP_info("usd",5);
            $pp->addRun_env("json");
            
            $response = json_decode($pp->commit(),true);
            var_dump($response);
            if (!is_array($response) || array_key_exists("error",$response) || array_key_exists("context",$response)) {
                foreach ($shipping_ids_tab as $key => $value) {
                    $this->Shipping_model->upShippingStatusById($value,-1);
                } 
                redirect(LANG_URL . '/checkout/faildcash'); 
            }else {
                $home->confirmLink($md5);
                $a = 0;
                while ($a < sizeof($vendors)) {
                    $this->sendsms(json_decode($vendors[$a])->{'name'},json_decode($vendors[$a])->{'url'});
                    $a=$a+1;
                }
                redirect(LANG_URL . '/checkout/successcash');           
            }
            
            
        }
        if ($_POST['payment_type'] == 'PayPal') {
            @set_cookie('paypal', $this->orderId, 2678400);
            $_SESSION['discountAmount'] = $_POST['discountAmount'];
            redirect(LANG_URL . '/checkout/paypalpayment');
        }
    }
     public function sendsms($name,$num){
        $new_num=$num;
        $new_num[0]=" ";

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.sms.to/sms/send",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS =>"{\n    \"message\": \"Nouvelle commande pour la boutique ".$name."\",\n    \"to\": \"".'+243'.((strcmp($num[0],"0")==0)?trim($new_num):$num)."\",\n    \"sender_id\": \"Bvortex\",\n    \"callback_url\": \"https://yetupay.com/callback/handler\"\n}",
          CURLOPT_HTTPHEADER => array(
              "Content-Type: application/json",
              "Accept: application/json",
              "Authorization: Bearer Q9208TdphrBMH4tWURggSMouV7cqumd0"
            ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        //echo $response;
    }

    private function userInfoValidate($post)
    {
        $errors = array();
        if (mb_strlen(trim($post['first_name'])) == 0) {
            $errors[] = lang('first_name_empty');
        }
        if (mb_strlen(trim($post['last_name'])) == 0) {
            $errors[] = lang('last_name_empty');
        }
        if (!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = lang('invalid_email');
        }
        $post['phone'] = preg_replace("/[^0-9]/", '', $post['phone']);
        if (mb_strlen(trim($post['phone'])) == 0) {
            $errors[] = lang('invalid_phone');
        }
        if (mb_strlen(trim($post['address'])) == 0) {
            $errors[] = lang('address_empty');
        }
        if (mb_strlen(trim($post['city'])) == 0) {
            $errors[] = lang('invalid_city');
        }
        return $errors;
    }

    public function orderError()
    {
        if ($this->session->flashdata('order_error')) {
            $data = array();
            $head = array();
            $arrSeo = $this->Public_model->getSeo('checkout');
            $head['title'] = @$arrSeo['title'];
            $head['description'] = @$arrSeo['description'];
            $head['keywords'] = str_replace(" ", ",", $head['title']);
            $this->render('checkout_parts/order_error', $head, $data);
        } else {
            redirect(LANG_URL . '/checkout');
        }
    }

    public function paypalPayment()
    {
        $data = array();
        $head = array();
        $arrSeo = $this->Public_model->getSeo('checkout');
        $head['title'] = @$arrSeo['title'];
        $head['description'] = @$arrSeo['description'];
        $head['keywords'] = str_replace(" ", ",", $head['title']);
        $data['paypal_sandbox'] = $this->Home_admin_model->getValueStore('paypal_sandbox');
        $data['paypal_email'] = $this->Home_admin_model->getValueStore('paypal_email');
        $this->render('checkout_parts/paypal_payment', $head, $data);
    }

    public function successPaymentCashOnD()
    {
        if ($this->session->flashdata('success_order')) {
            $data = array();
            $head = array();
            $arrSeo = $this->Public_model->getSeo('checkout');
            $head['title'] = @$arrSeo['title'];
            $head['description'] = @$arrSeo['description'];
            $head['keywords'] = str_replace(" ", ",", $head['title']);
            $this->render('checkout_parts/payment_success_cash', $head, $data);
        } else {
            redirect(LANG_URL . '/checkout');
        }
    }
    public function faildPaymentCashOnD()
    {
        if ($this->session->flashdata('success_order')) {
            $data = array();
            $head = array();
            $arrSeo = $this->Public_model->getSeo('checkout');
            $head['title'] = @$arrSeo['title'];
            $head['description'] = @$arrSeo['description'];
            $head['keywords'] = str_replace(" ", ",", $head['title']);
            $this->render('checkout_parts/order_error', $head, $data);
        } else {
            redirect(LANG_URL . '/checkout');
        }
    }

    public function successPaymentBank()
    {
        if ($this->session->flashdata('success_order')) {
            $data = array();
            $head = array();
            $arrSeo = $this->Public_model->getSeo('checkout');
            $head['title'] = @$arrSeo['title'];
            $head['description'] = @$arrSeo['description'];
            $head['keywords'] = str_replace(" ", ",", $head['title']);
            $data['bank_account'] = $this->Orders_model->getBankAccountSettings();
            $this->render('checkout_parts/payment_success_bank', $head, $data);
        } else {
            redirect(LANG_URL . '/checkout');
        }
    }

    public function paypal_cancel()
    {
        if (get_cookie('paypal') == null) {
            redirect(base_url());
        }
        @delete_cookie('paypal');
        $orderId = get_cookie('paypal');
        $this->Public_model->changePaypalOrderStatus($orderId, 'canceled');
        $data = array();
        $head = array();
        $head['title'] = '';
        $head['description'] = '';
        $head['keywords'] = '';
        $this->render('checkout_parts/paypal_cancel', $head, $data);
    }

    public function paypal_success()
    {
        if (get_cookie('paypal') == null) {
            redirect(base_url());
        }
        @delete_cookie('paypal');
        $this->shoppingcart->clearShoppingCart();
        $orderId = get_cookie('paypal');
        $this->Public_model->changePaypalOrderStatus($orderId, 'payed');
        $data = array();
        $head = array();
        $head['title'] = '';
        $head['description'] = '';
        $head['keywords'] = '';
        $this->render('checkout_parts/paypal_success', $head, $data);
    }

}
