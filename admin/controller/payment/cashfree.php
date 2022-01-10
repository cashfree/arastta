<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

class ControllerPaymentCashfree extends Controller {
    
    private $error = array();

    public function index() {
        $this->load->language('payment/cashfree');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('cashfree', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            if (isset($this->request->post['button']) and $this->request->post['button'] == 'save') {
                $route = $this->request->get['route'];
                $module_id = '';
                if (isset($this->request->get['module_id'])) {
                    $module_id = '&module_id=' . $this->request->get['module_id'];
                }
                elseif ($this->db->getLastId()) {
                    $module_id = '&module_id=' . $this->db->getLastId();
                }
                $this->response->redirect($this->url->link($route, 'token=' . $this->session->data['token'] . $module_id, 'SSL'));
            }

            $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');

        $data['entry_app_id'] = $this->language->get('entry_app_id');
        $data['entry_secret_key'] = $this->language->get('entry_secret_key');
        $data['entry_test'] = $this->language->get('entry_test');
        $data['entry_debug'] = $this->language->get('entry_debug');
        $data['entry_expired_status'] = $this->language->get('entry_expired_status');
        $data['entry_failed_status'] = $this->language->get('entry_failed_status');
        $data['entry_pending_status'] = $this->language->get('entry_pending_status');
        $data['entry_processed_status'] = $this->language->get('entry_processed_status');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');

        $data['help_app_id'] = $this->language->get('help_app_id');
        $data['help_secret_key'] = $this->language->get('help_secret_key');
        $data['help_test'] = $this->language->get('help_test');
        $data['help_debug'] = $this->language->get('help_debug');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_savenew'] = $this->language->get('button_savenew');
        $data['button_saveclose'] = $this->language->get('button_saveclose');        
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['tab_general'] = $this->language->get('tab_general');
        $data['tab_order_status'] = $this->language->get('tab_order_status');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['cashfree_app_id'])) {
            $data['error_app_id'] = $this->error['cashfree_app_id'];
        } else {
            $data['error_app_id'] = '';
        }

        if (isset($this->error['cashfree_secret_key'])) {
            $data['error_secret_key'] = $this->error['cashfree_secret_key'];
        } else {
            $data['error_secret_key'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('payment/cashfree', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['action'] = $this->url->link('payment/cashfree', 'token=' . $this->session->data['token'], 'SSL');

        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

        if (isset($this->request->post['cashfree_app_id'])) {
            $data['cashfree_app_id'] = $this->request->post['cashfree_app_id'];
        } else {
            $data['cashfree_app_id'] = $this->config->get('cashfree_app_id');
        }

        if (isset($this->request->post['cashfree_secret_key'])) {
            $data['cashfree_secret_key'] = $this->request->post['cashfree_secret_key'];
        } else {
            $data['cashfree_secret_key'] = $this->config->get('cashfree_secret_key');
        }

        if (isset($this->request->post['cashfree_test'])) {
            $data['cashfree_test'] = $this->request->post['cashfree_test'];
        } else {
            $data['cashfree_test'] = $this->config->get('cashfree_test');
        }

        if (isset($this->request->post['cashfree_debug'])) {
            $data['cashfree_debug'] = $this->request->post['cashfree_debug'];
        } else {
            $data['cashfree_debug'] = $this->config->get('cashfree_debug');
        }

        if (isset($this->request->post['cashfree_expired_status_id'])) {
            $data['cashfree_expired_status_id'] = $this->request->post['cashfree_expired_status_id'];
        } else {
            $data['cashfree_expired_status_id'] = $this->config->get('cashfree_expired_status_id');
        }

        if (isset($this->request->post['cashfree_failed_status_id'])) {
            $data['cashfree_failed_status_id'] = $this->request->post['cashfree_failed_status_id'];
        } else {
            $data['cashfree_failed_status_id'] = $this->config->get('cashfree_failed_status_id');
        }

        if (isset($this->request->post['cashfree_pending_status_id'])) {
            $data['cashfree_pending_status_id'] = $this->request->post['cashfree_pending_status_id'];
        } else {
            $data['cashfree_pending_status_id'] = $this->config->get('cashfree_pending_status_id');
        }

        if (isset($this->request->post['cashfree_processed_status_id'])) {
            $data['cashfree_processed_status_id'] = $this->request->post['cashfree_processed_status_id'];
        } else {
            $data['cashfree_processed_status_id'] = $this->config->get('cashfree_processed_status_id');
        }

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['cashfree_status'])) {
            $data['cashfree_status'] = $this->request->post['cashfree_status'];
        } else {
            $data['cashfree_status'] = $this->config->get('cashfree_status');
        }

        if (isset($this->request->post['cashfree_sort_order'])) {
            $data['cashfree_sort_order'] = $this->request->post['cashfree_sort_order'];
        } else {
            $data['cashfree_sort_order'] = $this->config->get('cashfree_sort_order');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('payment/cashfree.tpl', $data));
    }

    private function validate() {
        if (!$this->user->hasPermission('modify', 'payment/cashfree')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['cashfree_app_id']) {
            $this->error['app_id'] = $this->language->get('error_app_id');
        }

        if (!$this->request->post['cashfree_secret_key']) {
            $this->error['secret_key'] = $this->language->get('error_secret_key');
        }

        return !$this->error;
    }
}
