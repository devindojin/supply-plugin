<?php

/*
  Plugin Name: Neto Supply
  Description: Neto Supply Used by Staff for suplier notification after check stock per item.
  Version: 0.0.1
  Author: Automattic
  Author URI: #
  License: GPLv2 or later
  Text Domain: Neto Supply
 */
$path = wp_upload_dir();
$path = $path['basedir'];
$alerts;
define('SHOW_MSG', '');
define('NETO_DIR', $path . "/neto-supply");
define('NETO_VERSION', '0.0.1');
define('NETOAPI_URL', 'https://www.empoweredautoparts.com.au/do/WS/NetoAPI');


add_action('admin_enqueue_scripts', 'neto_enqueue_scripts');

function neto_enqueue_scripts() {
    wp_enqueue_style('style-name', plugin_dir_url(__FILE__));
    wp_enqueue_style('s', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
    wp_enqueue_script('neto_supply', plugin_dir_url(__FILE__) . '/js/neto_supply.js');
    wp_enqueue_script('bootstrap_js', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js');
    wp_enqueue_script('sweetalert_js', 'https://unpkg.com/sweetalert/dist/sweetalert.min.js');
}

add_action('admin_menu', 'headercode_admin_actions');

function headercode_admin_actions() {
    add_menu_page('Neto Staff', 'Neto Staff', 'manage_options', 'neto_order');
    add_submenu_page('neto_order', 'List Order', 'List Order', 'manage_options', 'neto_order', 'neto_list_order', 'dashicons-update');
    add_submenu_page(null, 'Process Order', 'Process Order', 'manage_options', 'process_Order', 'neto_restrict_admin', 'dashicons-update');
}

function neto_restrict_admin() {
    global $alerts;

    if (!current_user_can('manage_options') && (!wp_doing_ajax() )) {
        wp_die(__('You are not allowed to access this part of the site'));
    }

    if (isset($_GET['NetoId']) && $_GET['NetoId'] != '') {
        $oid = $_GET['NetoId'];
        $data = array(
            "Filter" => array(
                "OrderID" => $oid,
                "OutputSelector" => array(
                    "ShippingOption",
                    "DeliveryInstruction",
                    "Username",
                    "Email",
                    "ShipAddress",
                    "BillAddress",
                    "CustomerRef1",
                    "CustomerRef2",
                    "CustomerRef3",
                    "CustomerRef4",
                    "SalesChannel",
                    "GrandTotal",
                    "ShippingTotal",
                    "ShippingDiscount",
                    "OrderType",
                    "OrderStatus",
                    "OrderPayment",
                    "OrderPayment.PaymentType",
                    "OrderPayment.DatePaid",
                    "DatePlaced",
                    "DateRequired",
                    "DateInvoiced",
                    "DatePaid",
                    "OrderLine",
                    "OrderLine.ProductName",
                    "OrderLine.PickQuantity",
                    "OrderLine.BackorderQuantity",
                    "OrderLine.UnitPrice",
                    "OrderLine.WarehouseID",
                    "OrderLine.WarehouseName",
                    "OrderLine.WarehouseReference",
                    "OrderLine.Quantity",
                    "OrderLine.PercentDiscount",
                    "OrderLine.ProductDiscount",
                    "OrderLine.CostPrice",
                    "OrderLine.ShippingMethod",
                    "OrderLine.ShippingTracking",
                    "ShippingSignature",
                    "eBay.eBayUsername",
                    "eBay.eBayStoreName",
                    "OrderLine.eBay.eBayTransactionID",
                    "OrderLine.eBay.eBayAuctionID",
                    "OrderLine.eBay.ListingType",
                    "OrderLine.eBay.DateCreated",
                    "OrderLine.eBay.DatePaid"
                ),
                "UpdateResults" => array("ExportStatus" => "Exported")
        ));


        $action = 'GetOrder';
        $orders = neto_curl($data, $action);
        $orders_got = neto_vlidate_response($orders);


        if ($orders_got['status'] === 'success') {

            $this_items = $orders_got['data'][0]['OrderLine'];

            if (count($this_items) > 0) {
                $sic = $sku = '';
                foreach ($this_items as $item) {
                    $sku_to_get = $item['SKU'];
                    $Qty_to_get = $item['Quantity'];
                    $wareh_to_get = $item['WarehouseID'];
                    $data = array(
                        "Filter" => array(
                            "SKU" => $sku_to_get,
                            "OutputSelector" => "SupplierItemCode"
                        )
                    );

                    $action = 'GetItem';

                    $line_item = neto_curl($data, $action);

                    if ($line_item['status'] === 'success') {
                        $sdata = json_decode($line_item['data'], true);
                        $sic .= $sdata['Item'][0]['SupplierItemCode'] . ' ';
                        $sku .= $sdata['Item'][0]['SKU'] . ' ';
                    }
                }
            }
            include plugin_dir_path(__FILE__) . 'views/Neto_order_form.php';
        } else {
            echo $orders_got['msg'];
        }
    }

    if (isset($_POST['sub_order'])) {
        //*******Setp 1 ********/
        if (!empty($_FILES['file'])) {
            //if file is attached
            if (isset($_FILES['file']) && ($_FILES['file']['error'] == 0) && $_FILES['file']['name'] != ' ') {//if no error in file
                $file = neto_file_upload($_FILES['file']);
                if ($file['status'] == 'success') {
                    $ship_doc_url = $file['url'];
                    $ship_doc_path = $file['file'];
                    $ship_doc_name = $_FILES['file']['name'];
                } else {
                    echo'<h3>Some error occurred while uploading file</h3><p>' . $file['error'] . '</p>';
                }
            }
        } else {
            $ship_doc_url = 'No file found';
            $ship_doc_path = null;
            $$ship_doc_name = null;
        }

        /* ------------- Step 2 update orders status ------- */
        $redirect = '';
        $order_id_to_update = $_POST['order_id'];
        /* ---------Step 3 Create CSV file --------- */
        $csv_info = neto_create_csv($ship_doc_url);
        $csv_path = $csv_info['path'];
        $csv_name = $csv_info['name'];
        echo $copy_pdf = neto_copy_csv_sftp($csv_path, $csv_name);
         echo  $copy_cs = neto_copy_csv_sftp($ship_doc_url, $ship_doc_name);
        $tok = false;
        if ($copy_pdf) {
            $tok = true;
            echo '$copy_pdf';
            
        } else {
            $msg = $copy_pdf;
            $title = 'Error !';
            $text = 'Status of Order ID ' . $orderId . ' can not be changed ( ' . $msg . ')';
            $icon = 'error';
            $tok = false;
            exit;
        }
        if ($copy_cs) {
            $tok = true;
             echo '$copy_cs';
        } else {
            $msg = $copy_cs;
            $title = 'Error !';
            $text = 'Status of Order ID ' . $orderId . ' can not be changed ( ' . $msg . ')';
            $icon = 'error';
            $tok = false;
            exit;
        }
        die;
        
        if ($tok) {
            $OrderStatus = 'Pack';
            $updated_order_arr = neto_update_order($_POST['order_id'], $OrderStatus);
            $mail_message = neto_sent_mail($ship_doc_path, $csv_path);

            if ($updated_order_arr['status'] === 'success') {
                if ($mail_message === true) {
                    $title = 'Mail sent successfully !';
                    $text = 'Status of Order ID ' . $orderId . ' changed to ' . $OrderStatus . ' successfully.';
                    $icon = 'success';
                } else {
                    $title = 'Mail not sent due to technical issue !';
                    $text = 'Status of Order ID ' . $orderId . ' changed to ' . $OrderStatus . ' successfully.';
                    $icon = 'success';
                }
            } else {
                $title = 'Error !';
                $text = 'Status of Order ID ' . $orderId . ' can not be changed ( ' . $msg . ')';
                $icon = 'error';
            }
            $redirect = 'window.location = "?page=neto_order";';
        }
        echo <<<JQUERY
                <script>
                    swal({
                    title: "$title",
                    text: "$text",
                    icon: "$icon",
                    button: "Ok",
                  }).then(function() {
                    $redirect;
                  });                 
                </script>
JQUERY;
    }
}

function net_update_order_status() {
    
}

function neto_copy_csv_sftp($local_csv_path, $local_csv_name) {
    $host = "203.221.1.213";
    $port = 11122;
    $username = "emap";
    $password = "4DNjpccCUuXT";
//$host = "wattleparkmackay.com.au";
//$port = 21212;
//$username = "wattlepa";
//$password = "dgGj3i391";
//$remote_file_path = "/home/wattlepa/public_html/wp-content/uploads/".$local_csv_name;
    $connection = NULL;

    $remote_file_path = "/cygdrive/c/program files (x86)/icw/home/emap/Upload/" . $local_csv_name;

//echo $remote_file_path.'<br>';
//$f = plugin_dir_path(__DIR__).'/y.txt';
//$t = "https://www.wattleparkmackay.com.au/home/wattlepa/public_html/wp-content/plugins/rj.txt";

    try {
        $connection = ssh2_connect($host, $port);
        if (!$connection) {
            throw new \Exception("Could not connect to $host on port $port");
        }
        $auth = ssh2_auth_password($connection, $username, $password);
        if (!$auth) {
            throw new \Exception("Could not authenticate with username $username and password ");
        }
        $sftp = ssh2_sftp($connection);
        if (!$sftp) {
            throw new \Exception("Could not initialize SFTP subsystem.");
        }
        /*         * *******************        Open file       **************************** */
        $stream = fopen("ssh2.sftp://" . intval($sftp) . $remote_file_path, 'w');

        $file = file_get_contents($local_csv_path);
        $result = fwrite($stream, $file);
        if (!$result) {
            throw new \Exception('could not copy file');
        }
        fclose($stream);

        $stream = fopen("ssh2.sftp://" . $sftp . $remote_file_path, 'r');
        if (!$stream) {
            throw new \Exception("Could not open file: ");
        }
        $contents = stream_get_contents($stream);
        //echo "<pre>"; print_r($contents); echo "</pre>";

        @fclose($stream);
        $connection = NULL;
    } catch (Exception $e) {
        echo "Error due to :" . $e->getMessage();
    } finally {
        return $stream;
    }
}

function neto_sent_mail($ship_doc_path, $csv_path) {

    $to = $_POST['mail_to'];
    $user = wp_get_current_user();
    $user_email = $user->data->user_email;
//   $from = $user_email; 
    $from = 'admin@empoweredautoparts.com.au';
    $name = get_bloginfo();
    $orderId = $_POST['order_id'];
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: ' . $name . ' <' . strip_tags($from) . '>' . "\r\n";
    $headers .= 'Reply-To: ' . $name . ' <' . strip_tags($from) . '>' . "\r\n";
    $subject = 'Order No. ' . $orderId;
    $msg = "
    <html>
    <head>
    <title>HTML email</title>
    </head>
    <body>
    <p>Hi, <br>New order from Empowered Auto Parts (<b>Order No. $orderId </b>)</p>
    </body>
    </html>
    ";
    if ($ship_doc_path !== null) {
        $attachments = [$ship_doc_path, $csv_path];
    } else {
        $attachments = [$csv_path];
    }
    if (wp_mail($to, $subject, $msg, $headers, $attachments)) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function neto_create_csv($ship_doc_url) {

    $doc_path = $file['url'];
    $row = $_POST['order_id'] . ',' . $_POST['name'] . ',' . $_POST['ShipStreetLine1'] . ',' . $_POST['ShipStreetLine2'] .
            ',' . $_POST['Suburb'] . ',' . $_POST['state'] . ',' . $_POST['Postcode'] . ',' . $_POST['Phone'] .
            ',' . $_POST['Delivery'] . ',' . $_POST['Ship_from'] . ',' . $_POST['Instructions'] . ',' .
            $_POST['Comments'] . ',' . $_POST['mail_to'];
    $list = array(
        "PO NUMBER,NAME,ADDRESS,ADDRESS,SUBURB,STATE,POSTCODE,PHONE,CUSTOMER CODE EMAP,"
        . "SHIP FROM, SPECIAL INSTRUCTIONS,COMMENTS,CONFIRMATION EMAIL",
        $row,
    );

    $partno = $_POST['part_number'];
    $qty = $_POST['qty'];

    for ($i = 0; $i < count($partno); $i++) {
        if ($partno[$i] != '') {
            array_push($list, $partno[$i] . ',' . $qty[$i]);
        }
    }
    if (!is_dir(NETO_DIR)) {
        wp_mkdir_p(NETO_DIR);
    }
    $csv_name = $_POST['order_id'] . ".csv";
    $csv_path = NETO_DIR . "/" . $csv_name;
    if (file_exists($csv_path)) {
        if (!unlink($csv_path)) {
            $csv_name = $_POST['order_id'] . '-' . time() . ".csv";
            $csv_path = NETO_DIR . "/" . $csv_name;
        }
    }
    $file = fopen($csv_path, "w");
    foreach ($list as $line) {
        fputcsv($file, explode(',', $line));
    }
    fclose($file);
    $csv_info['name'] = $csv_name;
    $csv_info['path'] = $csv_path;
    return $csv_info;
}

function neto_file_upload($file) {

    if (!function_exists('wp_handle_upload')) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
    }

    $upload_overrides = array('test_form' => false);
    $movefile = wp_handle_upload($file, $upload_overrides);

    if ($movefile && !isset($movefile['error'])) {
        $movefile['status'] = 'success';
        return $movefile;
    } else {
        $movefile['status'] = 'error';
        return $movefile;
    }
}

function neto_list_order() {
    global $alerts;
    $data = array(
        "Filter" => array(
            "OrderStatus" => array(
                "Pick"
            ),
            "OutputSelector" => array(
                "ShippingOption",
                "DeliveryInstruction",
                "Username",
                "Email",
                "ShipAddress",
                "BillAddress",
                "CustomerRef1",
                "CustomerRef2",
                "CustomerRef3",
                "CustomerRef4",
                "SalesChannel",
                "GrandTotal",
                "ShippingTotal",
                "ShippingDiscount",
                "OrderType",
                "OrderStatus",
                "OrderPayment",
                "OrderPayment.PaymentType",
                "OrderPayment.DatePaid",
                "DatePlaced",
                "DateRequired",
                "DateInvoiced",
                "DatePaid",
                "OrderLine",
                "OrderLine.ProductName",
                "OrderLine.PickQuantity",
                "OrderLine.BackorderQuantity",
                "OrderLine.UnitPrice",
                "OrderLine.WarehouseID",
                "OrderLine.WarehouseName",
                "OrderLine.WarehouseReference",
                "OrderLine.Quantity",
                "OrderLine.PercentDiscount",
                "OrderLine.ProductDiscount",
                "OrderLine.CostPrice",
                "OrderLine.ShippingMethod",
                "OrderLine.ShippingTracking",
                "ShippingSignature",
                "eBay.eBayUsername",
                "eBay.eBayStoreName",
                "OrderLine.eBay.eBayTransactionID",
                "OrderLine.eBay.eBayAuctionID",
                "OrderLine.eBay.ListingType",
                "OrderLine.eBay.DateCreated",
                "OrderLine.eBay.DatePaid"
            ),
            "UpdateResults" => array("ExportStatus" => "Exported")
    ));


    $action = 'GetOrder';
    $orders = neto_curl($data, $action);

    $orders_got = neto_vlidate_response($orders);

    if ($orders_got['status'] !== 'success') {
        $alerts[] = neto_show_msg('danger', 'Sorry !', $orders_got['msg'] . '<br>Please Sync to get latest order.');
    }
    include plugin_dir_path(__FILE__) . 'views/Neto_order_list.php';
}

function neto_show_msg($type, $strong, $rest) {
    return '<div class="alert alert-' . $type . ' alert-dismissible" ><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><strong>' . $strong . '</strong> ' . $rest . '</div>';
}

function neto_update_order($orderId, $status) {
    $data = array(
        "Order" => array(
            "OrderID" => $orderId,
            "OrderStatus" => $status
        )
    );
    $action = 'UpdateOrder';
    $update_res = neto_curl($data, $action);
    return neto_vlidate_response($update_res, $update = '');
}

function neto_vlidate_response($value_to_vaildate, $update = '') {

    $return = array();

    if ($value_to_vaildate['status'] === 'success') {
        if ($val = json_decode($value_to_vaildate['data'], true)) {
            if ($val['Ack'] === 'Success') {
                if ($val['Order']) {
                    $return['status'] = 'success';
                    $return['data'] = $val['Order'];
                } else {
                    $return['status'] = 'error';
                    $return['msg'] = "No Order Found.";
                }
            } else if ($val['Ack'] === 'Error') {
                $err = deep_search($val, 'Message');
                if ($err) {
                    $return['status'] = 'error';
                    $return['msg'] = $err;
                } else {
                    $return['status'] = 'error';
                    $return['msg'] = 'Status Error.';
                }
            } else if ($val['Ack'] === 'Warning') {
                $err = deep_search($val, 'Message');
                if ($err) {
                    $return['status'] = 'error';
                    $return['msg'] = $err;
                } else {
                    $return['status'] = 'error';
                    $return['msg'] = 'Status Error.';
                }
            } else {
                $return['status'] = 'error';
                $return['msg'] = 'Sorry ! Some error Occured while parsing data.';
            }
        } else {
            $return['status'] = 'error';
            $return['msg'] = $value_to_vaildate['data'];
        }
    } else {
        $return['status'] = 'error';
        $return['msg'] = $value_to_vaildate['data']['msg'];
    }
    return $return;
}

function neto_curl($data, $action) {


    $neto_returns = array();
    $error = array();

    try {
        $headers = [
            "accept:application/json",
            "content-type:application/json",
            "NETOAPI_ACTION: $action",
            "NETOAPI_USERNAME:brentj",
            "NETOAPI_KEY:zGPXzZqhHwgGGmZHrp9Kv5dY3qSgbojL"
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, NETOAPI_URL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if ($response) {
            $neto_returns['status'] = 'success';
            $neto_returns['data'] = $response;
        } else {
            $error['msg'] = curl_error($ch);
            $error['no'] = curl_errno($ch);

            $neto_returns['status'] = 'fail';
            $neto_returns ['data'] = $error;
        }
    } catch (Exception $e) {

        $error['msg'] = $e->getMessage();
        $neto_returns['status'] = 'fail';
        $neto_returns ['data'] = $error;
    } finally {
        curl_close($ch);
    }

    return $neto_returns;
}

function deep_search($arr, $key) {
    foreach ($arr as $k => $v) {
        if ($k === $key) {
            $result = $v;
            break;
        } else {

            if (is_array($v) && !empty($v)) {
                $rec_result = deep_search($v, $key);
                if ($rec_result) {
                    return $rec_result;
                }
            }
        }
    }
    return $result;
}

function neto_email_notification($pa, $pt, $pc, $plink, $tps_auth_id, $tb_ID) {

    if (!empty($pa) && !empty($tps_auth_id)) {

//Get memorial Owner email address 
        $mpost_author_id_data = get_userdata($pa);
        $mpost_author_email = $mpost_author_id_data->user_email;

//Get Tributor details
        $tcpost_author_id_data = get_userdata($tps_auth_id);


        $tcpost_author_name = $tcpost_author_id_data->display_name;
        $tcpost_author_email = $tcpost_author_id_data->user_email;
        $tcpost_author_image = get_avatar_url($tcpost_author_email, 32, array('height' => null, 'width' => null));
        $memorial_post_link = $plink;


        if (!empty($mpost_author_email)) {

            $to1 = $mpost_author_email;
        }

        $subject = 'Notification For New Tributes & Condolences For ';

        $headers = 'From: ' . um_get_option('mail_from') . ' <' . um_get_option('mail_from_addr') . '>' . "\r\n" .
                "Reply-To:info@eternalmemorial.co\r\n" .
                "Content-type: text/html; charset=UTF-8 \r\n";
        $headers .= "MIME-version: 1.0\n";
        $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";

        $body = '<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Eternalmemorial</title>
</head>

<body>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td align="center" valign="top" bgcolor="#838383" style="background-color:black;"><br>
<br>
<table width="600" border="0" cellspacing="0" cellpadding="0">
<tr>
<td align="left" valign="top"><img src="' . home_url() . '/wp-content/uploads/top.png" width="600" height="177" style="display:block;"></td>
</tr>
<tr>
<td align="center" valign="top" bgcolor="#d3be6c" style="background-color:#FFF; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000000; padding:0px 15px 10px 15px;">
<div><img src="images/divider.png" width="517" height="10"></div>
<div style="font-size:48px; color:#838383;"><b>Eternal Memorial</b></div>
<div><img src="images/divider.png" width="517" height="10"></div>
<div style="font-size:18px; color:#555100;"><br>
A lot has happened on Eternalmemorial since you last logged in. Here are some notifications you have missed from your memorials
</div>

<div>
<h3>A Member Created new Tribute on ' . $pt . ' memorial.</h3>
<img src="' . $tcpost_author_image . '" width="100" height="100" style="border:0" class="CToWUd">
<center>' . $tcpost_author_name . '</center
<br>
<p>Member Tributes & Condolences :<br><b>' . $pc . '</b></p>
<p>Tributer Email : ' . $tcpost_author_email . '</p>
<p>Time: ' . get_post_time(get_option('time_format'), false, $tb_ID, true) . '</p>

<br>
<br>
<br>
<b>Eternalmemorial.com</b><br>
Level 36, Governor Phillip Tower
1 Farrer Place
NSW Australia 2000<br>
Phone: +61 2 9051 0502 <br>
<a href="' . home_url() . '" target="_blank" style="color:#000000; text-decoration:none;">Eternalmemorial.com</a></div></td>
</tr>
<tr>
<td align="left" valign="top"><img src="' . home_url() . '/wp-content/uploads/bot.png" width="600" height="18" style="display:block;">
<div style="font-family:Helvetica Neue,Helvetica,Lucida Grande,tahoma,verdana,arial,sans-serif;font-size:11px;color:#aaaaaa;line-height:16px">This message is sent by <a href="mailto:info@eternalmemorial.co" style="color:#3b5998;text-decoration:none" target="_blank">info@eternalmemorial.co</a>. If you do not want to receive these emails from Eternalmemorial in the future, please <a href="" style="color:#3b5998;text-decoration:none" target="_blank" data-saferedirecturl="https://www.google.com/url?hl=en&amp;q=">unsubscribe</a>.<br>Eternalmemorial Support.</div>
</td>
</tr>
</table>
<br>
<br></td>
</tr>
</table>
</body>
</html>
';

        $mail1 = wp_mail($to1, $subject . 'Memorial Creator', $body, $headers);

        if ($mail1 == 'true') {
            return 'true';
        } else {
            return 'false';
        }
    }
}

function neto_plugin_activate() {
    if (!is_dir(NETO_DIR)) {
        wp_mkdir_p(NETO_DIR);
    }
}

register_activation_hook(__FILE__, 'neto_plugin_activate');
?> 