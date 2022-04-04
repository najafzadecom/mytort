<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * WC_Gateway_Payriff class.
 *
 * @since 1.0.0
 * @extends WC_Payment_Gateway
 */
class WC_Gateway_Payriff extends WC_Payment_Gateway {

    /**
     * Constructor
     */
    public function __construct() {
        // Register plugin information

        $this->id = 'payriff';
        $this->method_title = 'Payriff';
        $this->has_fields = false;

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->settings['title'];
        $this->description = $this->settings['description'];
        $this->currency = $this->settings['currency'];
        $this->icon = WC_PAYRIFF_PLUGIN_URL. '/images/payriff-logo.png';

        $this->merch_name = get_bloginfo('name');
        $this->email = get_bloginfo('admin_email');
        $this->callback = WC_PAYRIFF_PLUGIN_URL. '/payriff-callback.php';

        $this->msg['message'] = "";
        $this->msg['class'] = "";

        if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
        } else {
            add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
        }
    }

    /**
     * Initialize Gateway Settings Form Fields.
     */
    public function init_form_fields() {

        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'payriff'),
                'type' => 'checkbox',
                'label' => __('Enable Payriff Payment Module.', 'payriff'),
                'default' => 'no'),
            'title' => array(
                'title' => __('Title:', 'payriff'),
                'type'=> 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'payriff'),
                'default' => __('Payriff', 'payriff')),
            'description' => array(
                'title' => __('Description:', 'payriff'),
                'type' => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'payriff'),
                'default' => __('Pay securely by Credit or Debit card or internet banking through Payriff Secure Servers.', 'payriff')),
            'currency'  => array(
                'title'       => __( 'Select Currency', 'payriff' ),
                'type'        => 'select',
                'description' => __( 'Select your bank accounts currency.', 'payriff' ),
                'options'     => array(
                    'AZN' => 'AZN',
                    'USD' => 'USD',
                    'EUR' => 'EUR')),
            'api_url' => array(
                'title' => __('Payriff url (Api Url):', 'payriff'),
                'type'=> 'text',
                'class' => 'production-mode',
                'description' => __('Payriff url.', 'payriff'),
                'default' => __('https://api.payriff.com/api/v1/', 'payriff')),
            'terminal' => array(
                'title' => __('Terminal', 'payriff'),
                'type' => 'text',
                'class' => 'production-mode',
                'description' => __('This terminal id use at Payriff.'),
                'default' => __('ES1234567', 'payriff')),
            'secret_key' => array(
                'title' => __('Secret Key', 'payriff'),
                'type' => 'text',
                'class' => 'production-mode',
                'description' =>  __('Given to key by Payriff', 'payriff'),
                'default' => __('00112233445566778899AABBCCDDEEFF', 'payriff'))
        );
    }

    public function get_admin_settings(){
        $admin_settings = array();

        $admin_settings['url'] = $this->settings['api_url'];
        $admin_settings['terminal'] = $this->settings['terminal'];
        $admin_settings['currency'] = $this->settings['currency'];
        $admin_settings['secret_key'] = $this->settings['secret_key'];   
        $admin_settings['callback'] = $this->callback;      

        return $admin_settings;
    }
    
     // get all pages
    public function get_callback_pages($title = false, $indent = true) {
        $wp_pages = get_pages('sort_column=menu_order');
        $page_list = array();
        if ($title) $page_list[] = $title;
        foreach ($wp_pages as $page) {
            $prefix = '';
            // show indented child pages?
            if ($indent) {
                $has_parent = $page->post_parent;
                while($has_parent) {
                    $prefix .=  ' - ';
                    $next_page = get_page($has_parent);
                    $has_parent = $next_page->post_parent;
                }
            }
            // add to page list array array
            $page_list[$page->ID] = $prefix . $page->post_title;
        }
        return $page_list;
    }

    /**
     * Process the payment and return the result
     **/
    public function process_payment($order_id){
        session_set_cookie_params(0, '/; samesite=None', null, true, false);

        $order = wc_get_order( $order_id );

        $request = $this->create_order($order_id);

        if ($request['status'] == 0) {
            // Mark as on-hold (we're awaiting the cheque)
            $order->update_status( 'on-hold', _x( 'Error', 'Check payment method', 'payriff' ) );

            // Remove cart
            WC()->cart->empty_cart();

            // Return thankyou redirect
            return array(
              'result'   => 'success',
              'redirect'  => $request['payment_url'],
            );
        }

        // Mark as on-hold (we're awaiting the cheque)
        $order->update_status( 'on-hold', _x( 'Awaiting check payment', 'Check payment method', 'payriff' ) );

        // Reduce stock levels
        wc_reduce_stock_levels( $order_id );

        // Remove cart
        WC()->cart->empty_cart();

        define( 'WP_SAMESITE_COOKIE', 'None' );

        // Return thankyou redirect
        return array(
          'result'   => 'success',
          'redirect'  => $request['payment_url'],
        );

    }

    public function create_order($order_id){
        $admin_settings = $this->get_admin_settings();

        $order = new WC_Order( $order_id );

        $encryptionToken = $order_id . time() . rand();

        $result = [];
        $result['status'] = 0;
        
        $request_data = [
            'body' => [
                'amount' => $order->total,
                'currencyType' => $admin_settings['currency'],
                'description' => 'Sifariş #'.$order_id,
                'language' => 'AZ',
                'approveURL' => $admin_settings['callback']."?order_id=".$order_id,
                'cancelURL' => $admin_settings['callback']."?order_id=".$order_id,
                'declineURL' => $admin_settings['callback']."?order_id=".$order_id,
            ],
            'encryptionToken' => $encryptionToken,
            'merchant' => $admin_settings['terminal']
        ];

        if($_POST['month'] > 0){
            $request_data['body']['installmentProductType'] = 'BIRKART';
            $request_data['body']['installmentPeriod'] = (int)$_POST['month'];
        }

        $auth = sha1($admin_settings['secret_key'] . $encryptionToken);

        $url = $admin_settings['url']."createOrder";
        
        $request = $this->post_data($url,$auth, json_encode($request_data));
        
        if ($request['http_code'] == 200) {
            $content = json_decode($request['content'],true);

            if ($content['code'] == '00000') {
                $order -> add_order_note( __($content['message'].': '.$order_id, 'payriff'));

                $payload = $content['payload'];

                update_post_meta( $order_id, 'order_order_id', $payload['orderId']);
                update_post_meta( $order_id, 'order_session_id', $payload['sessionId']);

                $order -> add_order_note( __('Payriff Order ID: '.$payload['orderId'], 'payriff'));
                $order -> add_order_note( __('Payriff Session ID: '.$payload['sessionId'], 'payriff'));

                $result['status'] = 1;
                $result['payment_url'] = $payload['paymentUrl'];
            }else{
                $order -> add_order_note( __('Code : '.$content['code'], 'payriff'));
                $order -> add_order_note( __('Error Message : '.$content['message'], 'payriff'));
            }
        }else{
            $order -> add_order_note( __('Error on request', 'payriff'));
        }

        return $result;
    }

	public function reverse($order_id, $orderId, $sessionId, $amount)
	{
		$admin_settings = $this->get_admin_settings();

		$order = new WC_Order( $order_id );

		$encryptionToken = $order_id . time() . rand();

		$result = [];
		$result['status'] = 0;

		$request_data = [
			'body' => [
				'amount'        => $amount,
				'description'   => 'Sifariş #'.$order_id,
				'language'      => 'AZ',
				'orderId'       => $orderId,
				'sessionId'     => $sessionId
			],
			'encryptionToken' => $encryptionToken,
			'merchant' => $admin_settings['terminal']
		];


		$auth = sha1($admin_settings['secret_key'] . $encryptionToken);

		$url = $admin_settings['url']."reverse";

		$request = $this->post_data($url,$auth, json_encode($request_data));
        

		if ($request['http_code'] == 200) {
			$content = json_decode($request['content'],true);

			if ($content['code'] == '00000') {
				$order -> add_order_note( __($content['message'].': '.$order_id, 'payriff'));
				$order -> add_order_note( __($content['route'].': '.$order_id, 'payriff'));

				$result['status'] = 1;
			}else{
                $result['status'] = 0;
				$order -> add_order_note( __('Code : '.$content['code'], 'payriff'));
				$order -> add_order_note( __('Error Message : '.$content['message'], 'payriff'));
			}
		}else{
            $result['status'] = 0;
			$order -> add_order_note( __('Error on request', 'payriff'));
		}

		return $result;
	}

    public function get_order_information($payload){
        //Define woocommerce
        global $woocommerce;

        //Get admin settings
        $admin_settings = $this->get_admin_settings();

        //Get order data by post meta
        $order_data = $this->get_order_by_meta($payload['orderID'],$payload['sessionId']);

        //Define order id
        $order_id = $order_data->ID;

        //Create object from Order class
        $order = new WC_Order( $order_id );

        //Get order total with cent
        $total = $order->get_total() * 100;
        
        //Create unique token
        $encryptionToken = $order_id . time() . rand();

        $result = [];
        
        //Make request data
        $request_data = [
            'body' => [
                'languageType' => 'AZ',
                'orderId' => $payload['orderID'],
                'sessionId' => $payload['sessionId'],
            ],
            'encryptionToken' => $encryptionToken,
            'merchant' => $admin_settings['terminal']
        ];

        //Make Authorization header
        $auth = sha1($admin_settings['secret_key'] . $encryptionToken);

        //Define url from admin settings
        $url = $admin_settings['url']."getOrderInformation";

        //Curl request to payment service
        $request = $this->post_data($url,$auth, json_encode($request_data));

        //Check request status
        if ($request['http_code'] == 200) {
            $content = json_decode($request['content'],true);

            //Check order status
            if ($content['code'] == '00000') {
                $payload = $content['payload']['row'];

                $order_total = $payload['amount'];
                $order_status = $payload['orderstatus'];

                //Additional Control for order
                if ($total == $order_total && $order_status == 'APPROVED'){
                    $this->msg['message'] = __('Thank you for shopping with us. Your account has been charged and your transaction is successful. We will be shipping your order to you soon.', 'payriff');
                    $this->msg['class'] = 'woocommerce_message';

                    $order->update_status( 'processing' );
                    $order -> add_order_note( __('Payriff payment successful', 'payriff'));
                    $woocommerce->cart->empty_cart();
                }else{
                    $this->msg['message'] = __('Payment not accepted.', 'payriff');
                    $this->msg['class'] = 'woocommerce_error';

                    $order->update_status( 'failed' );
                    $order -> add_order_note( __('Code : '.$content['code'], 'payriff'));
                    $order -> add_order_note( __('Error Message : '.$content['message'], 'payriff'));
                }
            }else{
                $this->msg['message'] = __('Payment not accepted.', 'payriff');
                $this->msg['class'] = 'woocommerce_error';

                $order->update_status( 'failed' );
                $order -> add_order_note( __('Code : '.$content['code'], 'payriff'));
                $order -> add_order_note( __('Error Message : '.$content['message'], 'payriff'));
            }
        }else{
            $order -> add_order_note( __('Error on request', 'payriff'));
        }
    }

    //CURL query
    public function post_data($url, $auth, $reqeust_data ){
        $options = array(
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            //-------to post-------------
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $reqeust_data, //$data,
            CURLOPT_SSL_VERIFYPEER => false,    // DONT VERIFY      
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => array(
                'Authorization: '. $auth,
                'Content-type: application/json'
            ),
        );

        $ch      = curl_init( $url );
        curl_setopt_array( $ch, $options );
            $content = curl_exec( $ch );
            $err     = curl_errno( $ch );
            $errmsg  = curl_error( $ch );
            $header  = curl_getinfo( $ch );
        curl_close( $ch );
            $header['errno']   = $err;
            $header['errmsg']  = $errmsg;
            $header['content'] = $content;
        
        return $header;
    }

    public function get_order_by_meta($order_id,$session_id){
        $args = array(
            'post_type'  => 'shop_order',
            'post_status'  => 'wc-on-hold',
            'meta_query' => array(
                array(
                    'key'     => 'order_order_id',
                    'value'   => $order_id,
                    'compare' => '=',
                ),
                array(
                    'key'     => 'order_session_id',
                    'value'   => $session_id,
                    'compare' => '=',
                ),
            ),
        );

        $query = new WP_Query($args);

        $posts = $query->posts;

        if (empty($posts)) {
            exit();
        }

        return $posts[0];
    }
}

