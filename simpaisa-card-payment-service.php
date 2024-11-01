<?php

/*
 * Plugin Name: Simpaisa Credit/Debit Card Payment Service
 * Plugin URI: https://www.simpaisa.com/pay-in.html
 * Description: Providing Easy To Integrate Digital Payment Services
 * Author: Simpaisa Pvt Ltd
 * Author URI: http://simpaisa.com
 * Version: 1.1.5
 */

header("Access-Control-Allow-Origin: *");


add_filter('woocommerce_payment_gateways', 'simpaisa_checkout_add_gateway_class');

function simpaisa_checkout_add_gateway_class($gateways)
{
    $gateways[] = 'WC_Simpaisa_Checkout_Gateway';

    return $gateways;
}


add_action('plugins_loaded', 'simpaisa_checkout_init_gateway_class');
function simpaisa_checkout_init_gateway_class()
{

 

    class WC_Simpaisa_Checkout_Gateway extends WC_Payment_Gateway
    {

        public function __construct()
        {

            $this->id = 'simpaisa_credit_debit_card_cko';
            $this->icon = '';
            $this->has_fields = true;
            $this->method_title = 'Simpaisa Credit and Debit Payment';
            $this->method_description = 'Pay With Your Credit and Debit Card via Simpaisa Payment Services';

            $this->supports = array(
                'products',
            );

            $this->init_form_fields();

            $this->init_settings(); //for custom settings fields
            $this->title = $this->get_option('cko_title');
            $this->description = $this->get_option('cko_description');
            $this->enabled = $this->get_option('cko_enabled');
            $this->base_url = $this->get_option('cko_base_url');
            $this->merchant_id = $this->get_option('cko_merchant_id');
            $this->public_key = $this->get_option('cko_public_key');
            $this->is_items = $this->get_option('cko_is_items');

            add_action('wp_enqueue_scripts', array($this, 'simpaisa_cko_stylesheet'));

            add_action('wp_enqueue_scripts', array($this, 'simpaisa_checkout_cdn_script'));

            add_action('wp_enqueue_scripts', array($this, 'simpaisa_javascript_script'));

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(
                $this,
                'process_admin_options',
            ));
            add_action('woocommerce_api_simpaisa_cko_redirect', array(
                $this,
                'simpaisa_cko_redirect',
            ));
            //Callback for Simpaisa System
            add_action('woocommerce_api_simpaisa_notify', array(
                $this,
                'simpaisa_notify',
            ));
        }




        public function payment_fields()
        {

            if (!session_id()) {
                session_start();
            }

            $sp_cko_mobile = '';
            if (isset($_SESSION['sp_cko_mobile'])) {
                $sp_cko_mobile = $this->sanitize_input($_SESSION['sp_cko_mobile']);
            }

            do_action('simpaisa_cko_form_start', $this->id);

            printf('<fieldset id="wc-' . esc_attr($this->id) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">');

            printf('<p class="cko-p">Enter your Card Details</p>
                    <div class="form-row row-cko">
                        <div class="simpaisa_cko_loader"> </div>

                        <label>Card Details <span class="required">*</span></label>
                        <div class="card-frame"></div>
                        <div class="card-number"></div>
                        <span class="sp-error-card"></span>

                        <input name="sp_cko_checkbox" class="sp_cko_checkbox"  type="checkbox" style="margin: 0;">
                        <label  class="sp_cko_checkbox" style="display:inline;font-size: 12px;font-weight: 300;font-style: italic;">Please confirm that the provided details are correct
</label>
                         
                        <span class="sp-error"></span>
                        <input type="hidden" class="sp_cko_token" name="sp_cko_token" />
                        <input type="hidden" class="sp_cko_bin" name="sp_cko_bin" />
                </div>');
            printf('<div class="clear"></div></fieldset><script> Frames.init("' . esc_attr($this->public_key) . '")</script>');
            do_action('simpaisa_cko_form_end', $this->id);
        }

        public function process_payment($order_id)
        {

            if (!session_id()) {
                session_start();
            }

            if ($this->base_url == "" || $this->merchant_id == "") {
                wc_add_notice(__('<strong>Payment Base URL and Merchant Id are required</strong>, please check your Simpaisa Payment Configuration.'), 'error');
                return false;
            } elseif ($this->sanitize_input($_POST['sp_cko_token']) == "") {
                wc_add_notice(__('Card token request has been failed, try again'), 'error');
                return false;
            }


            $merchant_id = $this->merchant_id;
            $baseUrl = rtrim($this->base_url, '/') . "/";
            $baseUrl = str_replace('/index.php', '', $baseUrl) . 'api/';
            $paymentUrl = $baseUrl . 'checkout-backend-api.php';

            $order = wc_get_order($order_id);

            $order_data = $order->get_data(); // The Order data
            $phone = "";
            $first_name = "";
            $last_name = "";

            if ($order_data['billing']['first_name']) {
                $first_name = $order_data['billing']['first_name'];
            } else {
                $first_name = $order_data['shipping']['first_name'];
            }

            if ($order_data['billing']['last_name']) {
                $last_name = $order_data['billing']['last_name'];
            } else {
                $last_name = $order_data['shipping']['last_name'];
            }

            if ($order_data['billing']['email']) {
                $email = $order_data['billing']['email'];
            } else {
                $email = $order_data['shipping']['email'];
            }

            if ($order_data['billing']['phone']) {
                $phone = $order_data['billing']['phone'];
            } else {
                $phone = $order_data['shipping']['phone'];
            }

            $name =  $first_name . ' ' . $last_name;


            $_SESSION['sp_cko_mobile'] = $this->sanitize_input($phone);
            // $_SESSION['sp_cko_token'] =  $this->sanitize_input($_POST['sp_cko_token']);
            $sp_cko_bin = $this->sanitize_input($_POST['sp_cko_bin']);


            $msisdn =  $this->sanitize_input($phone);
            $sp_cko_token   = $this->sanitize_input($_POST['sp_cko_token']);
            $_sp_orderId = substr(md5(uniqid(rand(), true)), 0, 6);
            $_sp_orderId = $order_id . '-' . $_sp_orderId;
            $_payment_method_title = 'Simpaisa Card Payment, Order # ' . $_sp_orderId . '';
            update_post_meta($order_id, '_sp_orderId', $_sp_orderId);
            update_post_meta($order_id, '_sp_bin_number', $sp_cko_bin);
            update_post_meta($order_id, '_payment_method_title', $_payment_method_title);
            update_post_meta($order_id, '_sp_payment_method', 'CKO');
            $amountSimpaisa = $order->get_total();


            $currency =  strtoupper(get_woocommerce_currency());
            if ($currency != 'PKR') {
                $currencyUrl = $baseUrl . "currency-converter.php?from=$currency&to=PKR&amount=$amountSimpaisa";

                $response = wp_remote_get($currencyUrl);

                if (wp_remote_retrieve_response_code($response) != 200) {
                    $error_message = wp_remote_retrieve_response_code($response);
                    wc_add_notice(__(" Error: HTTP Response " . $error_message . ", <strong>Curreny converter error</strong> , please try again."), "error");
                    return false;
                } elseif (is_wp_error($response) && count($response->get_error_messages()) > 0) {
                    $error_message = '';
                    foreach ($response->get_error_messages() as $error) {
                        $error_message .= $error . '<br/>';
                    }

                    wc_add_notice(__(" Error: " . $error_message . " , <strong>Curreny converter error</strong> , please try again."), "error");
                    return false;
                }

                $currencyResponse     = json_decode(wp_remote_retrieve_body($response), true);



                if ($currencyResponse['success'] == 1 || $currencyResponse['success'] == true) {
                    $amountSimpaisa =  ceil($currencyResponse['result']);
                } else {
                    echo 'Something is wrong ! Curreny converter error';
                    exit();
                }
            } else {
                $amountSimpaisa = $order->get_total();
            }



            $note = __("Simpaisa Card Payment initiate, Order # '$_sp_orderId'");
            $order->add_order_note($note);
            $authorization = 'Basic ' . base64_encode($_sp_orderId . ':' . $msisdn);

            $redirectUrl = rtrim(site_url(), '/') . "/";
            $redirectUrl = str_replace('/index.php', '', $redirectUrl) . 'index.php/wc-api/simpaisa_cko_redirect/?token=' . base64_encode($_sp_orderId . ':' . $msisdn);


            $payload = array(
                'body' => [
                    'name' => $name,
                    'email' => $email,
                    'merchantId' => $merchant_id,
                    'msisdn' => $msisdn,
                    'amount' => $amountSimpaisa,
                    'userKey' => $_sp_orderId,
                    'token' => $sp_cko_token,
                    'redirectUrl' => $redirectUrl,
                    'method' => 'initiate'
                ],
                'timeout'     => 30,
                'redirection' => 5,  // added
                'httpversion' => '1.0',
                'method' => 'POST',
                'headers' => array('Authorization' => $authorization)
            );


            $response = wp_remote_post($paymentUrl, $payload);

            if (wp_remote_retrieve_response_code($response) != 200 && wp_remote_retrieve_response_code($response) != 201) {
                $error_message = wp_remote_retrieve_response_code($response);
                wc_add_notice(__(" Error: HTTP Response " . $error_message . ", <strong>Order payment transaction has been failed</strong> , please try again."), "error");
                return false;
            } elseif (is_wp_error($response) && count($response->get_error_messages()) > 0) {
                $error_message = '';
                foreach ($response->get_error_messages() as $error) {
                    $error_message .= $error . '<br/>';
                }

                wc_add_notice(__(" Error: " . $error_message . " , <strong>Order payment transaction has been failed</strong> , please try again."), "error");
                return false;
            }

            $__response     = json_decode(wp_remote_retrieve_body($response), true);

            if ($__response['status'] == "0037") {
                $order_status = $order->get_status();
                $_simpaisa_transactionId = $__response['transactionId'];
                update_post_meta($order_id, '_sp_transactionId', $_simpaisa_transactionId);
                $note = __("Simpaisa Card Payment - Trans Id : '$_simpaisa_transactionId' , Order Status : $order_status");
                $order->add_order_note($note);
                return array(
                    'result' => 'success',
                    'redirect' => $__response['redirectUrl']
                );
            } else if ($__response['status'] == "0000") {

                $order_status = $order->get_status();
                $_simpaisa_transactionId = $__response['transactionId'];

                $order->payment_complete();
                wc_reduce_stock_levels($_sp_orderId);

                $order_key = get_post_meta($_sp_orderId, '_order_key', true);
                $note = __("Simpaisa Card Response - Trans Id : '$_simpaisa_transactionId' , CB Status : $order_status");
                $order->add_order_note($note);

                $returnURL = site_url() . '/index.php/checkout/order-received/' . $_sp_orderId . '/?key=' . $order_key . '&pay_for_order=false';
                wp_redirect($returnURL);
                exit();
            } else {
                $err = $__response['message'];

                $note = __("Simpaisa Card Payment failed, Error '$err'");
                $order->add_order_note($note);

                wc_add_notice(__(' Error: ' . $__response['message'] . ' , <strong>Order payment transaction has been failed</strong> , please try again.'), 'error');

                return array(
                    'result' => 'success',
                    'redirect' => wc_get_checkout_url()
                );
            }

            wp_die();
            exit();
            //
            return;
        }

        public function simpaisa_cko_stylesheet()
        {
            wp_register_style('simpaisa_credit_debit_card_stylesheet', plugins_url('assets/css/style.css', __FILE__));
            wp_enqueue_style('simpaisa_credit_debit_card_stylesheet');
        }

        public function simpaisa_checkout_cdn_script()
        {
            wp_register_script('simpaisa_checkout_script', "https://cdn.checkout.com/js/framesv2.min.js");
            wp_enqueue_script('simpaisa_checkout_script');
        }

        public function simpaisa_javascript_script()
        {
            wp_register_script('simpaisa_javascript_script', plugins_url('assets/js/main.js', __FILE__));
            wp_enqueue_script('simpaisa_javascript_script');
        }


        public function init_form_fields()
        {

            $this->form_fields = array(
                'cko_enabled' => array(
                    'title' => 'Enable/Disable',
                    'label' => 'Enable Simpaisa',
                    'type' => 'checkbox',
                    'description' => '',
                    'default' => 'no',
                ),
                'cko_is_items' => array(
                    'title' => 'Enable/Disable',
                    'label' => 'Enable Items',
                    'type' => 'checkbox',
                    'description' => '',
                    'default' => 'yes',
                ),
                'cko_title' => array(
                    'title' => 'Title',
                    'type' => 'text',
                    'description' => 'This controls the title which the user sees during checkout.',
                    'default' => 'Credit/Debit',
                    'desc_tip' => true,
                ),
                'cko_description' => array(
                    'title' => 'Description',
                    'type' => 'textarea',
                    'description' => 'This controls the description which the user sees during checkout.',
                    'default' => 'Pay With Your Visa Card via Simpaisa Payment Services',
                    'desc_tip' => true,
                ),
                'cko_base_url' => array(
                    'title' => 'Payment Base Url',
                    'type' => 'text'
                ),

                'cko_public_key' => array(
                    'title' => 'CKO Public Key',
                    'type' => 'text',
                ),
                'cko_merchant_id' => array(
                    'title' => 'Merchant Id',
                    'type' => 'text'
                ),
                'cko_webhookUrl' => array(
                    'css' => 'pointer-events:none;background:#00000024;font-size:12px;',
                    'title' => 'Webhook Url',
                    'description' => 'This is the notification URL. Simpaisa sends notification of each transactions on the provided URL.',
                    'default' => rtrim(site_url(), '/') . "/index.php/wc-api/simpaisa_notify"
                )
            );
        }

        public function simpaisa_notify()
        {
            global $woocommerce;
            $json = $this->sanitize_input(file_get_contents("php://input"));


            error_log('Simpaisa log :: CKO - Postback Data ' . $json);

            if (strpos($json, '=') !== false) {
                $data = [];
                $json =  str_replace('{', '', $json);
                $json =  str_replace('}', '', $json);
                foreach (explode(",", $json) as $value) {
                    $data[trim(explode("=", $value)[0])] = trim(explode("=", $value)[1]);
                }
            } else {
                $data = json_decode($json, true);
            }


            $transactionId = $this->sanitize_input($data["userKey"]);
            $status = $this->sanitize_input($data["status"]);
            $merchantId = $this->sanitize_input($data["merchantId"]);
            if (!isset($data['transactionId'])) {
                $sp_transactionId = 'Null';
            } else {
                $sp_transactionId = $this->sanitize_input($data['transactionId']);
            }
            $_orderID = explode('-', $transactionId)[0];


            if (get_post_meta($_orderID, '_sp_payment_method', true) == 'CKO') {

                error_log('Simpaisa log :: Postback Order No # ' . $_orderID . ' status ' . $status . ' merchantId ' . $merchantId);

                if (isset($merchantId) && isset($status) && isset($_orderID)) {
                    $order = wc_get_order($_orderID);

                    $order_status = $order->get_status();

                    if ($order_status == "pending" || $order_status == "failed") {
                        if ($status == "0000") {
                            $order->payment_complete();
                            wc_reduce_stock_levels($_orderID);
                            $order_status = $order->get_status();
                            update_post_meta($_orderID, '_sp_transactionId', $sp_transactionId);
                            $note = __("Simpaisa Postback - Order Id : '$transactionId' , Trans Id : '$sp_transactionId' , CB Status : $status , Order Status : $order_status");
                            $order->add_order_note($note);

                            echo json_encode(["respose_code" => "0000", "order_status" => $order_status, "status" => $status, "message" => "Order status has been updated",]);
                        } elseif ($status == "0037") {

                            // $order->update_status("failed");
                            // $order_status = $order->get_status();

                            $note = __("Simpaisa Postback - Order Id : '$transactionId' , Trans Id : '$sp_transactionId' , CB Status : $status , Order Status : Pending");
                            $order->add_order_note($note);

                            echo json_encode(["respose_code" => "0000", "order_status" => $order_status, "status" => $status, "message" => "Order status has been updated",]);
                        } else {
                            $order->update_status("failed");
                            $order_status = $order->get_status();

                            $note = __("Simpaisa Postback - Order Id : '$transactionId' , Trans Id : '$sp_transactionId' , CB Status : $status , Order Status : $order_status");
                            $order->add_order_note($note);

                            echo json_encode(["respose_code" => "0000", "order_status" => $order_status, "status" => $status, "message" => "Order status has been updated",]);
                        }
                    } else {
                        $note = __("Simpaisa Postback - Order Id : '$transactionId' , Trans Id : '$sp_transactionId'  , CB Status : $status , Order Status : $order_status");
                        $order->add_order_note($note);

                        echo json_encode(["respose_code" => "1003", "order_status" => $order_status, "status" => $status, "message" => "Order status already modified",]);
                    }
                } else {
                    error_log('Simpaisa log :: Postback fields are missing');
                    echo json_encode(["respose_code" => "1001", "message" => "Field(s) are required",]);
                    exit();
                }
            }
        }

        public function simpaisa_cko_redirect()
        {


            if (isset($_GET['token'])) {
                $token = 'Basic ' . $this->sanitize_input($_GET['token']);
                list($userkey, $mobile) = explode(':', base64_decode(substr($token, 6)));
                $_orderID = explode('-', $userkey)[0];
                $_simpaisa_transactionId = get_post_meta($_orderID, '_sp_transactionId', true);

                $order = wc_get_order($_orderID);
                $order_status = $order->get_status();

                if (isset($_simpaisa_transactionId)) {
                    $paymentUrl = rtrim($this->base_url, '/') . "/";
                    $paymentUrl = str_replace('/index.php', '', $paymentUrl) . 'api/checkout-backend-api.php';
                    $authorization = 'Basic ' . base64_encode($userkey . ':' . $mobile);


                    $payload = array(
                        'body' => [
                            'msisdn' => $mobile,
                            'userKey' => $userkey,
                            'transactionId' => $_simpaisa_transactionId,
                            'method' => 'verify'
                        ],
                        'redirection' => 5,  // added
                        'httpversion' => '1.0',
                        'method' => 'POST',
                        'headers' => array('Authorization' => $authorization)
                    );
                    $response = wp_remote_post($paymentUrl, $payload);
                    if (wp_remote_retrieve_response_code($response) != 200) {
                        $error_message = wp_remote_retrieve_response_code($response);
                        wc_add_notice(__(" Error: HTTP Response " . $error_message . " , <strong>Order payment transaction has been failed</strong> , please try again."), "error");
                        return false;
                    } elseif (is_wp_error($response) && count($response->get_error_messages()) > 0) {
                        $error_message = $response->get_error_message();
                        wc_add_notice(__(" Error: " . $error_message . " , <strong>Order payment transaction has been failed</strong> , please try again."), "error");
                        return false;
                    }

                    $__response     = json_decode(wp_remote_retrieve_body($response), true);

                    $sp_transactionId =  $this->sanitize_input($__response['transactionId']);
                    $status =  $this->sanitize_input($__response['message']);

                    if ($__response['status'] == "0000") {
                        $order->payment_complete();
                        wc_reduce_stock_levels($_orderID);

                        $order_key = get_post_meta($_orderID, '_order_key', true);
                        $note = __("Simpaisa Card Response - Trans Id : '$sp_transactionId' , CB Status : $status");
                        $order->add_order_note($note);

                        $returnURL = site_url() . '/index.php/checkout/order-received/' . $_orderID . '/?key=' . $order_key . '&pay_for_order=false';
                        wp_redirect($returnURL);
                        exit();
                    } else {
                        $order = wc_get_order($_orderID);
                        $order->update_status('failed');
                        $order_key = get_post_meta($_orderID, '_order_key', true);
                        $note = __("Simpaisa Card Response - Trans Id : '$sp_transactionId' , CB Status : $status");
                        $order->add_order_note($note);
                        wp_redirect(wc_get_checkout_url());
                        wc_add_notice(__('<strong>' . $status . '</strong> Transaction has been failed, please try again.'), 'error');
                        exit();
                    }

                    exit;
                } else {
                    echo json_encode(array(
                        'respose_code' => '1001',
                        'message' => 'Field(s) are required',
                    ));
                    exit;
                }
            } else {
                echo json_encode(array(
                    'respose_code' => '1002',
                    'message' => 'Field(s) are required',
                ));
                exit;
            }
        }

        public function sanitize_input($sanitize_input, $default = null)
        {
            return isset($sanitize_input) ? sanitize_text_field($sanitize_input) : $default;
        }
    }
}


configure_plugin_CKO();

function configure_plugin_CKO()
{

    class PluginConfiguration_CKO
    {

        public function __construct()
        {

            //It will fire when the plugin is activated and stop the user to install this plugin if woocommerce is not installed.
            register_activation_hook(__FILE__, [$this, 'plugin_activate_hook_CKO']);

            // Admin Notice
            add_action("admin_notices", [$this, "my_plugin_admin_notices_CKO",]);

            // Woocommerce plugin Notice
            add_action("admin_notices", [$this, "woocommerce_related_notices_CKO",]);
        }

        public function plugin_activate_hook_CKO()
        {
            if (!class_exists("WC_Payment_Gateway")) {
                $notices = get_option("my_plugin_deferred_admin_notices", []);
                $url = admin_url("plugins.php?deactivate=true");
                $notices[] = "Error: Install <b>WooCommerce</b> before activating this plugin. <a href=" . $url . ">Go Back</a>";
                update_option("my_plugin_deferred_admin_notices", $notices);
            }
        }

        public function my_plugin_admin_notices_CKO()
        {
            if ($notices = get_option("my_plugin_deferred_admin_notices")) {
                foreach ($notices as $notice) {
                    echo "<div class='updated' style='background-color:#f2dede'><p>$notice</p></div>";
                }

                deactivate_plugins(plugin_basename(__FILE__), true);
                delete_option("my_plugin_deferred_admin_notices");
                die();
            }
        }

        public function woocommerce_related_notices_CKO()
        {
            global $woocommerce;

            if (!class_exists("WC_Payment_Gateway")) {
                echo "<div class='notice notice-success is-dismissible'>
                        <p>Simpaisa Card Payment Service requires <b>WooCommerce</b> Plugin to make it work!</p>
                    </div>";
            }

            if (class_exists("WC_Payment_Gateway") && get_woocommerce_currency_symbol() != get_woocommerce_currency_symbol("PKR")) {
                echo "<div class='notice notice-success is-dismissible'>
                        <p>Simpaisa Card Payment Service requires <b>PKR</b> Currency to make it work!</p>
                    </div>";
            }
        }
    }

    $PluginConfiguration = new PluginConfiguration_CKO();
}
