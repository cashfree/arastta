<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

class ControllerPaymentCashfree extends Controller {

    public function index() {

        $this->language->load('payment/cashfree');

        $data['text_testmode'] = $this->language->get('text_testmode');
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['testmode'] = $this->config->get('cashfree_test');

        if (!$this->config->get('cashfree_test')) {
            $data['curl_url'] = 'https://api.cashfree.com/pg/orders';
            $data['environment'] = 'production';
        } else {
            $data['curl_url'] = 'https://sandbox.cashfree.com/pg/orders';
            $data['environment'] = 'sandbox';
        }

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        if ($order_info) {
            $data['order_currency'] = $order_info['currency_code'];
            $data['customer_email'] = $order_info['email'];
            $data['customer_id'] = empty($order_info['customer_id']) ? "ArasttaCustomer" : $order_info['customer_id'];
            $data['order_id'] = $order_info['order_id']."_".time();
            $data['order_note'] = "Arastta Order";
            $data['order_amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
            $data['return_url'] = $this->url->link('payment/cashfree/callback', '', 'SSL');
            $data['notify_url'] = $this->url->link('payment/cashfree/webhook', '', 'SSL');
            $data["order_token"] = "";
            $countryCode = "";
            $countryId = !empty($order_info["shipping_iso_code_2"]) ? $order_info["shipping_iso_code_2"] : $order_info["payment_iso_code_2"];;
            
            if(!empty($countryId)){
                $countryCode = $this->getPhoneCode($countryId);
            } else {
                $countryCode = "91";
            }
            
            $getCustomentNumber = $order_info['telephone'];
            $customerNumber = "+".$countryCode.preg_replace("/[^0-9]/", '', $getCustomentNumber);
            $data['customer_phone'] = $customerNumber;
            $mobileDigitsLength = strlen($customerPhone);
            $getToken = $this->getCashfreeOrderToken($data);
            
            if(!empty($getToken)){
                $data["order_token"] = $getToken;
            }

            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/cashfree.tpl')) {
                return $this->load->view($this->config->get('config_template') . '/template/payment/cashfree.tpl', $data);
            } else {
                return $this->load->view('default/template/payment/cashfree.tpl', $data);
            }
        }
    }

    public function callback() {

        $this->load->model('checkout/order');
        $response_data = $this->request->post;

        if(!empty($this->session->data['order_id'])) {
            $order_id = $this->session->data['order_id'];
        } elseif(!empty($response_data["transaction"]["orderId"])) {
            $order_id = $response_data["transaction"]["orderId"];
            list($order_id) = explode('_', $order_id);
        } else {
            $order_id = 0;
        }

        $order_status_id = $this->config->get('config_order_status_id');

        if(!empty($response_data["order"]) && $response_data["order"]["status"] == "PAID") {
            $order_info = $this->model_checkout_order->getOrder($order_id);
            $validate_signature = $this->verifySignature($order_info, $response_data);

            if($validate_signature) {
                $order_status_id = $this->config->get('cashfree_processed_status_id');
                $redirect_url = $this->url->link('checkout/success');
                $comment = "ORDER_STATUS_CHANGE_TO_PROCESSING";
            } else {
                $order_status_id = $this->config->get('cashfree_failed_status_id');
                $redirect_url = $this->url->link('checkout/failure');
                $comment = "SIGNATURE_VERIFICATION_FAILED";
            }
        } elseif(!empty($response_data["order"]) && $response_data["order"]["status"] == "EXPIRED") {
            $order_status_id = $this->config->get('cashfree_expired_status_id');
            $redirect_url = $this->url->link('checkout/failure', '', 'SSL');
            $comment = "ORDER_EXPIRED";
        } else {
            if($response_data["order"]["status"] == "ACTIVE" && $response_data["transaction"]["txStatus"] == "FAILED") {
                $order_status_id = $this->config->get('cashfree_failed_status_id');
                $redirect_url = $this->url->link('checkout/failure', '', 'SSL');
                $comment = "ORDER_FAILED";
            } else {
                $order_status_id = $this->config->get('cashfree_pending_status_id');
                $redirect_url = $this->url->link('checkout/checkout', '', 'SSL');
                $comment = "ORDER_ACTIVE";
            }
        }

        $logs = 'CASHFREE :: '.$comment." ". $order_id;
        $this->log->write($logs);
        $responseContent['redirect_url'] = $redirect_url;
        $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $logs);
        echo $redirect_url;
    }

    /**
     * webhook
     *
     * @param array $data
     */
    public function webhook()
    {
        $response_data = $this->request->post;

        if(!empty($response_data) && $response_data["txStatus"] == "SUCCESS") {

            $order_id = $response_data["orderId"];

            list($order_id) = explode('_', $order_id);
            //
            // Order entity should be sent as part of the webhook payload
            //
            $this->load->model('checkout/order');

            $order = $this->model_checkout_order->getOrder($order_id);

            // If it is already marked as paid or failed, ignore the event
            if ($order['order_status'] === 'Processing' or $order['order_status'] === 'Failed')
            {
                return;
            }

            $verifyWebhookResponse = false;

            $verifyWebhookResponse = $this->verifyWehookResponse($order, $response_data);

            if($verifyWebhookResponse)
            {
                $this->log->write('CASHFREE :: WEBHOOK_ORDER_UPDATE ' . $data['orderId']);
                $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('cashfree_processed_status_id'), 'CASHFREE_WEBHOOK : Payment Successful. Cashfree Transaction Id - '.$response_data["referenceId"]);
            }
            else
            {
                $error = 'ARASTTA_ERROR: Payment to Cashfree Failed. Signature mismatch.';

                $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('cashfree_failed_status_id'), $error.' Payment Failed! Check Cashfree dashboard for details of Transaction Id:'.$response_data["referenceId"]);
            }

            // Graceful exit since payment is now processed.
            exit;
        }

        exit;
    }

    public function getCashfreeOrderToken($data) {
        
        $params = array(
            "customer_details" => array(
                "customer_id" => $data["customer_id"],
                "customer_email" => $data['customer_email'],
                "customer_phone"=> $data["customer_phone"]
            ),
            "order_id" => $data["order_id"],
            "order_amount" => $data["order_amount"],
            "order_currency" => $data["order_currency"],
            "order_note" => "Arrasta Order",
            "order_meta"=> array(
                "notify_url" => $data["notify_url"]
            )
        );

        $curlPostfield = json_encode($params);
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $data["curl_url"],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $curlPostfield,
            CURLOPT_HTTPHEADER => [
                "Accept: application/json",
                "Content-Type: application/json",
                "x-api-version: 2021-05-21",
                "x-client-id: ". $this->config->get('cashfree_app_id'),
                "x-client-secret: ".$this->config->get('cashfree_secret_key')
            ],
        ]);

        $response = curl_exec($curl);
        $order = json_decode($response);
        return $order->order_token;
    }

    protected function verifySignature($order_data, $response_data)
    {
        $order_amount = $this->currency->format($order_data['total'], $order_data['currency_code'], $order_data['currency_value'], false);

        if($order_amount != $response_data['transaction']['transactionAmount']) {
            $this->log->write('CASHFREE :: TOTAL PAID MISMATCH! ' . $response_data['transaction']['transactionAmount']);
            return false;
        }

        $cfOrderId = $response_data["transaction"]["orderId"];
        $cfOrderStatus = $response_data['order']['status'];

        if (!$this->config->get('cashfree_test')) {
            $curl_url = 'https://api.cashfree.com/pg/orders';
        } else {
            $curl_url = 'https://sandbox.cashfree.com/pg/orders';
        }

        $getOrderUrl = $curl_url."/".$cfOrderId;
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $getOrderUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Accept: application/json",
                "Content-Type: application/json",
                "x-api-version: 2021-05-21",
                "x-client-id: ".$this->config->get('cashfree_app_id'),
                "x-client-secret: ".$this->config->get('cashfree_secret_key')
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        if($err) {
            $this->log->write('CASHFREE :: ORDER_GET_API_CALL_FAILED! ' . $cfOrderId);
            return false;
        }

        curl_close($curl);
        $cfOrder = json_decode($response);

        if (null !== $cfOrder && !empty($cfOrder->order_status))
        {
            if($cfOrderStatus == $cfOrder->order_status && $cfOrder->order_status == 'PAID') {
                return true;
            } else {
                $this->log->write('CASHFREE :: ORDER_STATUS_MISMATCH! ' . $cfOrderId);
                return false;
            }
        } else {
            $this->log->write('CASHFREE :: ORDER_STATUS_NOT_FOUND! ' . $cfOrderId);
            return false;
        }
    }

    protected function verifyWehookResponse($order_data, $data) {
        
        $order_amount = $this->currency->format($order_data['total'], $order_data['currency_code'], $order_data['currency_value'], false);

        if($order_amount != $data['orderAmount']) {
            $this->log->write('CASHFREE :: TOTAL PAID MISMATCH! ' . $data['orderAmount']);
            return false;
        }

        $orderId        = $data["orderId"];
        $orderAmount    = $data["orderAmount"];
        $referenceId    = $data["referenceId"];
        $txStatus       = $data["txStatus"];
        $paymentMode    = $data["paymentMode"];
        $txMsg          = $data["txMsg"];
        $txTime         = $data["txTime"];
        $signature      = $data["signature"];
        $secretKey      = $this->config->get('cashfree_secret_key');
        $data           = $orderId.$orderAmount.$referenceId.$txStatus.$paymentMode.$txMsg.$txTime;
        $hashHmac       = hash_hmac('sha256', $data, $secretKey, true) ;
        $computedSignature = base64_encode($hashHmac);
        
        if ($signature == $computedSignature) {
            return true;
        } else {
            $this->log->write('CASHFREE :: SIGNATURE MISMATCH! ' . $data['orderId']);
            return false;
        }
    }

    public function getPhoneCode($code)
    {
        $countrycode = array(
            '376'=>'AD',
            '971'=>'AE',
            '93'=>'AF',
            '1268'=>'AG',
            '1264'=>'AI',
            '355'=>'AL',
            '374'=>'AM',
            '599'=>'AN',
            '244'=>'AO',
            '672'=>'AQ',
            '54'=>'AR',
            '1684'=>'AS',
            '43'=>'AT',
            '61'=>'AU',
            '297'=>'AW',
            '994'=>'AZ',
            '387'=>'BA',
            '1246'=>'BB',
            '880'=>'BD',
            '32'=>'BE',
            '226'=>'BF',
            '359'=>'BG',
            '973'=>'BH',
            '257'=>'BI',
            '229'=>'BJ',
            '590'=>'BL',
            '1441'=>'BM',
            '673'=>'BN',
            '591'=>'BO',
            '55'=>'BR',
            '1242'=>'BS',
            '975'=>'BT',
            '267'=>'BW',
            '375'=>'BY',
            '501'=>'BZ',
            '1'=>'CA',
            '61'=>'CC',
            '243'=>'CD',
            '236'=>'CF',
            '242'=>'CG',
            '41'=>'CH',
            '225'=>'CI',
            '682'=>'CK',
            '56'=>'CL',
            '237'=>'CM',
            '86'=>'CN',
            '57'=>'CO',
            '506'=>'CR',
            '53'=>'CU',
            '238'=>'CV',
            '61'=>'CX',
            '357'=>'CY',
            '420'=>'CZ',
            '49'=>'DE',
            '253'=>'DJ',
            '45'=>'DK',
            '1767'=>'DM',
            '1809'=>'DO',
            '213'=>'DZ',
            '593'=>'EC',
            '372'=>'EE',
            '20'=>'EG',
            '291'=>'ER',
            '34'=>'ES',
            '251'=>'ET',
            '358'=>'FI',
            '679'=>'FJ',
            '500'=>'FK',
            '691'=>'FM',
            '298'=>'FO',
            '33'=>'FR',
            '241'=>'GA',
            '44'=>'GB',
            '1473'=>'GD',
            '995'=>'GE',
            '233'=>'GH',
            '350'=>'GI',
            '299'=>'GL',
            '220'=>'GM',
            '224'=>'GN',
            '240'=>'GQ',
            '30'=>'GR',
            '502'=>'GT',
            '1671'=>'GU',
            '245'=>'GW',
            '592'=>'GY',
            '852'=>'HK',
            '504'=>'HN',
            '385'=>'HR',
            '509'=>'HT',
            '36'=>'HU',
            '62'=>'ID',
            '353'=>'IE',
            '972'=>'IL',
            '44'=>'IM',
            '91'=>'IN',
            '964'=>'IQ',
            '98'=>'IR',
            '354'=>'IS',
            '39'=>'IT',
            '1876'=>'JM',
            '962'=>'JO',
            '81'=>'JP',
            '254'=>'KE',
            '996'=>'KG',
            '855'=>'KH',
            '686'=>'KI',
            '269'=>'KM',
            '1869'=>'KN',
            '850'=>'KP',
            '82'=>'KR',
            '965'=>'KW',
            '1345'=>'KY',
            '7'=>'KZ',
            '856'=>'LA',
            '961'=>'LB',
            '1758'=>'LC',
            '423'=>'LI',
            '94'=>'LK',
            '231'=>'LR',
            '266'=>'LS',
            '370'=>'LT',
            '352'=>'LU',
            '371'=>'LV',
            '218'=>'LY',
            '212'=>'MA',
            '377'=>'MC',
            '373'=>'MD',
            '382'=>'ME',
            '1599'=>'MF',
            '261'=>'MG',
            '692'=>'MH',
            '389'=>'MK',
            '223'=>'ML',
            '95'=>'MM',
            '976'=>'MN',
            '853'=>'MO',
            '1670'=>'MP',
            '222'=>'MR',
            '1664'=>'MS',
            '356'=>'MT',
            '230'=>'MU',
            '960'=>'MV',
            '265'=>'MW',
            '52'=>'MX',
            '60'=>'MY',
            '258'=>'MZ',
            '264'=>'NA',
            '687'=>'NC',
            '227'=>'NE',
            '234'=>'NG',
            '505'=>'NI',
            '31'=>'NL',
            '47'=>'NO',
            '977'=>'NP',
            '674'=>'NR',
            '683'=>'NU',
            '64'=>'NZ',
            '968'=>'OM',
            '507'=>'PA',
            '51'=>'PE',
            '689'=>'PF',
            '675'=>'PG',
            '63'=>'PH',
            '92'=>'PK',
            '48'=>'PL',
            '508'=>'PM',
            '870'=>'PN',
            '1'=>'PR',
            '351'=>'PT',
            '680'=>'PW',
            '595'=>'PY',
            '974'=>'QA',
            '40'=>'RO',
            '381'=>'RS',
            '7'=>'RU',
            '250'=>'RW',
            '966'=>'SA',
            '677'=>'SB',
            '248'=>'SC',
            '249'=>'SD',
            '46'=>'SE',
            '65'=>'SG',
            '290'=>'SH',
            '386'=>'SI',
            '421'=>'SK',
            '232'=>'SL',
            '378'=>'SM',
            '221'=>'SN',
            '252'=>'SO',
            '597'=>'SR',
            '239'=>'ST',
            '503'=>'SV',
            '963'=>'SY',
            '268'=>'SZ',
            '1649'=>'TC',
            '235'=>'TD',
            '228'=>'TG',
            '66'=>'TH',
            '992'=>'TJ',
            '690'=>'TK',
            '670'=>'TL',
            '993'=>'TM',
            '216'=>'TN',
            '676'=>'TO',
            '90'=>'TR',
            '1868'=>'TT',
            '688'=>'TV',
            '886'=>'TW',
            '255'=>'TZ',
            '380'=>'UA',
            '256'=>'UG',
            '1'=>'US',
            '598'=>'UY',
            '998'=>'UZ',
            '39'=>'VA',
            '1784'=>'VC',
            '58'=>'VE',
            '1284'=>'VG',
            '1340'=>'VI',
            '84'=>'VN',
            '678'=>'VU',
            '681'=>'WF',
            '685'=>'WS',
            '381'=>'XK',
            '967'=>'YE',
            '262'=>'YT',
            '27'=>'ZA',
            '260'=>'ZM',
            '263'=>'ZW'
        );

        $key = array_search($code,$countrycode);
        
        return $key;
    }
}
