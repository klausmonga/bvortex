<?php

/*
 * @Author:    Klaus Monga
 *  Gitgub:    https://github.com/4b6555s
 */
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Shipping extends VENDOR_Controller
{

    private $num_rows = 20;

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Orders_model', 'Products_model'));
        $this->load->model(array('Shipping_model', 'Shipping_model'));
    }

    public function index($page = 0)
    {

        $data = array();
        $head = array();
        $head['title'] = lang('vendor_orders');
        $head['description'] = lang('vendor_orders');
        $head['keywords'] = '';
        $rowscount = $this->Orders_model->ordersCount($this->vendor_id);
        $data['vendor_id'] = $this->vendor_id;
        $data['orders'] = $this->Orders_model->orders($this->num_rows, $page, $this->vendor_id);
        $data['shippings'] = $this->Shipping_model->getShippingByIdVendorAnd($this->vendor_id);
        $data['shipper'] = $this->Shipping_model->getShipper($this->vendor_id);
        $this->load->view('_parts/header', $head);
        $this->load->view('shipping', $data);
        $this->load->view('_parts/footer');
    }
    public function validShipping(){
        echo json_encode( $this->Shipping_model->upShippingStatus($this->vendor_id,$_POST['ref'],1));
     }
    public function setDefaultShipper(){
       echo $this->Shipping_model->addShipper( array('id_vendor' => $this->vendor_id,'cout' =>0,'delais' => "delais de livraison"));
    }
    public function removeShipper(){
        echo json_encode($this->Shipping_model->upShipperStatus( $this->vendor_id,0));
    }
    public function activeShipper(){
        echo json_encode($this->Shipping_model->upShipperStatus( $this->vendor_id,1));
    }
    public function setShippingParams(){
        echo $this->Shipping_model->upShipper( $this->vendor_id,$_POST['cout'],$_POST['delais']);
    }
    public function getProductInfo($product_id, $vendor_id)
    {
        return $this->Products_model->getOneProduct($product_id, $vendor_id);
    }

    public function changeOrdersOrderStatus()
    {
        $result = $this->Orders_model->changeOrderStatus($_POST['the_id'], $_POST['to_status']);
        if ($result == false) {
            echo '0';
        } else {
            echo '1';
        }
    }

}
