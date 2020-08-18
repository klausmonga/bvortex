<?php

class Shipping_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function ordersCount($vendor_id)
    {
        $this->db->where('vendor_id', $vendor_id);
        return $this->db->count_all_results('vendors_orders');
    }

    public function orders($limit, $page, $vendor_id)
    {
        $this->db->where('vendor_id', $vendor_id);
        $this->db->order_by('id', 'DESC');
        $this->db->select('vendors_orders.*, vendors_orders_clients.first_name,'
                . ' vendors_orders_clients.last_name, vendors_orders_clients.email, vendors_orders_clients.phone, '
                . 'vendors_orders_clients.address, vendors_orders_clients.city, vendors_orders_clients.post_code,'
                . ' vendors_orders_clients.notes, discount_codes.type as discount_type, discount_codes.amount as discount_amount');
        $this->db->join('vendors_orders_clients', 'vendors_orders_clients.for_id = vendors_orders.id', 'inner');
        $this->db->join('discount_codes', 'discount_codes.code = vendors_orders.discount_code', 'left');
        $result = $this->db->get('vendors_orders', $limit, $page);
        return $result->result_array();
    }

    public function changeOrderStatus($id, $to_status)
    {
        $this->db->where('id', $id);
        $this->db->select('processed');
        $result1 = $this->db->get('vendors_orders');
        $res = $result1->row_array();

        if ($res['processed'] != $to_status) {
            $this->db->where('id', $id);
            $result = $this->db->update('vendors_orders', array('processed' => $to_status, 'viewed' => '1'));
            return $result;
        }
    }
    public function addShipper($shipper){
        return $this ->db-> insert("shipper",$shipper);
    }
    public function addShipping($shipping){
        $this ->db-> insert("shipping",$shipping);
        return $this->getShipping($shipping['id_vendor'],$shipping['ref'])['id'];
    }
    public function getShippingByIdVendorAnd($vendor_id){
        $this->db->where('id_vendor', $vendor_id);
        $this->db->select();
        $this->db->join('orders','orders.id=shipping.id_order');
        $this->db->join('orders_clients','orders_clients.for_id=orders.id');
        $result1 = $this->db->get('shipping');
        $res = $result1->result_array();
        return $res;
    }
    public function getShipping($vendor_id,$ref){
        $this->db->where('id_vendor', $vendor_id);
        $this->db->where('ref', $ref);
        $this->db->select();
        $result1 = $this->db->get('shipping');
        $res = $result1->row_array();
        return $res;
    }
    public function getShipper($vendor_id){
        $this->db->where('id_vendor', $vendor_id);
        $this->db->select();
        $result1 = $this->db->get('shipper');
        $res = $result1->row_array();
        return $res;
    }
    public function upShipper($vendor_id,$cout,$delais){
        $this->db->where('id_vendor', $vendor_id);
        $result = $this->db->update('shipper', array('cout' => $cout, 'delais' => $delais));
        return $result;
    }
    public function upShipperStatus($vendor_id,$state){
        $this->db->where('id_vendor', $vendor_id);
        $result = $this->db->update('shipper', array('state' => $state));
        return $this->getShipper($vendor_id);
    }
    public function upShippingStatus($vendor_id,$ref, $state){
        $this->db->where('id_vendor', $vendor_id);

        $this->db->where('ref', $ref);
        $result = $this->db->update('shipping', array('state' => $state,'delivery_date'=> $this->db->query("SELECT NOW();")->row_array()['NOW()']));
        return $this->getShipping($vendor_id,$ref);
    }
    public function upShippingStatusById($id,$state){
        $this->db->where('id', $id);
        $result = $this->db->update('shipping', array('state' => $state,'delivery_date'=> $this->db->query("SELECT NOW();")->row_array()['NOW()']));
        return $result;
    }
    public function delShipper($vendor_id){
        $this->db->where('id_vendor', $vendor_id);
       return  $this->db->delete('shipper');
    }

}
