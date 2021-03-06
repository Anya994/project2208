<?php

require_once '../db.php';

class Order 
{
    public $id;
    public $status;
    public $address;
    public $user_id;
    public $total;

    public function __construct($id)
    {
        global $mysqli;
        
        $query = "SELECT order_id, status, address, user_id, total FROM orders WHERE order_id = $id";
        $result = $mysqli->query($query);

        $data = $result->fetch_assoc();

        $this->id = $data['order_id'];
        $this->status = $data['status'];
        $this->address = $data['address'];
        $this->user_id = $data['user_id'];
        $this->total = $data['total'];

    }

    public function getList($id)
    {
        global $mysqli;
        
        $query = "SELECT product_id, size_id, price, count FROM order_products WHERE order_id = $id";
        $result = $mysqli->query($query);

        $data = [];
        while ($data_item = $result->fetch_assoc()) {
            $data[] = $data_item;
        }
        
        return($data);
        // $this->status = $data['status'];
        // $this->address = $data['address'];
        // $this->user_id = $data['user_id'];
        // $this->total = $data['total'];

    }

    public static function getAll($status = false, $user_id = false, $page = 1)
    {
        global $mysqli; 
        $page -= 1;
        $count_items = 5;
        $condition = "";

        if ($status != false) {
            $condition .= " AND status = $status";
        } 
        
        if ($user_id != false) {
            $condition .= " AND user_id = $user_id";
        }

        $query = "SELECT COUNT(*) as count FROM orders";
        $result = $mysqli->query($query);
        $count = $result->fetch_assoc();

        if ($count['count'] < $page * $count_items) {
            return false;
        } 

        $limit = ' LIMIT ' . ($page * $count_items) . ', ' . $count_items;

        $query = "SELECT order_id FROM orders WHERE 1 $condition $limit";
        $result = $mysqli->query($query);

        $orders = [];
        while ($order_data = $result->fetch_assoc()) {
            $orders[] = new Order($order_data['order_id']);
        }

        return [
            'orders' => $orders, 
            'count' => $count['count']
        ];
    }

    public static function create($status, $adress, $user_id, $products)
    {
        global $mysqli;

        $total = 0;

        foreach ($products as $product) {
            $total = $total + $product['price']*$product['count'];
        }

        $query = "INSERT INTO orders SET 
                    status=$status, 
                    address='$adress', 
                    user_id=$user_id,
                    total=$total
        ";
        $result = $mysqli->query($query);

        $insert_id = $mysqli->insert_id;

        foreach ($products as $product) {
            $query = "INSERT INTO order_products SET 
                        order_id=$insert_id, 
                        product_id={$product['product_id']}, 
                        size_id={$product['size_id']},
                        price={$product['price']},
                        count={$product['count']}
            ";
            $result = $mysqli->query($query);
        }

        return $insert_id;
    }

    public function update($status, $adress, $user_id, $products, $order_id)
    {
        global $mysqli;

        $total = 0;

        foreach ($products as $product) {
            $total = $total + $product['price']*$product['count'];
        }

        $query = "UPDATE orders SET 
                    status=$status, 
                    address='$adress', 
                    user_id=$user_id,
                    total=$total
                   WHERE order_id=".$this->id;
        
        $result = $mysqli->query($query);

        foreach ($products as $product) {
            $query = "UPDATE order_products SET 
                        product_id={$product['product_id']}, 
                        size_id={$product['size_id']},
                        price={$product['price']},
                        count={$product['count']}
                      WHERE order_id=".$order_id;

        $result = $mysqli->query($query);
        
        }

        return $mysqli->affected_rows;
    }

}

// $order_col = Order::getAll(0,0);
// var_dump($order_col);



// $orders = Order::getAll(0, 1);
// foreach ($orders as $order) {
//     echo '<h1>Статус заказа: '.$order->status.'</h1>';    
//     echo '<p>Номер заказа: '.$order->id.'</p>';
//     echo '<p>Адрес: '.$order->address.'</p>';
//     echo '<p>Номер пользователя: '.$order->user_id.'</p> <hr>';
// }
// if ($orders == false) {
//     echo '<h1>Такого заказа не существует</h1>';    
// }
