<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

class ModelPaymentCashfree extends Model {
    public function getMethod($address, $total) {
        $this->load->language('payment/cashfree');

        $method_data = array(
            'code'       => 'cashfree',
            'title'      => $this->language->get('text_title'),
            'terms'      => '',
            'sort_order' => $this->config->get('cashfree_sort_order')
        );

        return $method_data;
    }
}
