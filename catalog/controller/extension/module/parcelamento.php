<?php
class ControllerExtensionModuleParcelamento extends Controller {
    public function __construct($params) {
        parent::__construct($params);

        $this->status = $this->config->get('module_parcelamento_status');
        $this->options_container = $this->config->get('module_parcelamento_options_container');
        $this->parcelamento_container = $this->config->get('module_parcelamento_view_container');
    }

    public function index() {
        $json = array();

        if ($this->status && ($this->customer->isLogged() || !$this->config->get('config_customer_price'))) {
            if (isset($this->request->get['product_id'])) {
                $product_id = (int)$this->request->get['product_id'];
            } else {
                $product_id = 0;
            }

            $this->load->model('catalog/product');

            $product_info = $this->model_catalog_product->getProduct($product_id);
            if ($product_info) {
                if (isset($this->request->post['quantity'])) {
                    $quantity = (int)$this->request->post['quantity'];
                } else {
                    $quantity = 1;
                }

                $special = $product_info['special'];
                if ($special) {
                    $price = $special;
                } else {
                    $price = $product_info['price'];
                }

                $this->load->model('extension/module/parcelamento');
                $discount_price = $this->model_extension_module_parcelamento->getDiscountPrice($product_id, $quantity);
                if ($discount_price && !$special) {
                    if ((float)$discount_price) {
                        $price = $discount_price;
                    }
                }

                if (isset($this->request->post['option'])) {
                    $options_price = 0;

                    $product_options = $this->model_catalog_product->getProductOptions($product_info['product_id']);
                    foreach ($product_options as $option) {
                        foreach ($option['product_option_value'] as $option_value) {
                            if (isset($this->request->post['option'][$option['product_option_id']])) {
                                if ($option_value['price_prefix'] == '+%' || $option_value['price_prefix'] == '-%') {
                                    $option_value['price'] = $option_value['price'] * ($product_info['price'] / 100);
                                    if ($option_value['price_prefix'] == '-%') {
                                        $option_value['price_prefix'] = '-';
                                    } else {
                                        $option_value['price_prefix'] = '+';
                                    }
                                }
                                if (is_array($this->request->post['option'][$option['product_option_id']])) {
                                    foreach ($this->request->post['option'][$option['product_option_id']] as $product_option_id) {
                                        if ($product_option_id == $option_value['product_option_value_id']) {
                                            $options_price += $this->get_options_price($option_value);
                                        }
                                    }
                                } else if ($this->request->post['option'][$option['product_option_id']] == $option_value['product_option_value_id']){
                                    $options_price += $this->get_options_price($option_value);
                                }
                            }
                        }
                    }

                    if ($options_price > 0) {
                        $price = $price + $options_price;
                    } else if ($options_price < 0) {
                        $price = $price - abs($options_price);
                    }
                }

                $price = $price * $quantity;

                $json['parcelamento'] = $this->model_catalog_product->getParcelamento($price, $product_info['tax_class_id'], true);

                $json['success'] = true;
            } else {
                $json['success'] = false;
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function js() {
        header('Content-Type: application/javascript');
        $product_id = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;

        if ($product_id == 0 || !$this->status) {
            exit;
        }

        $js = <<<HTML
    var parcelamento_ajax_call = function() {
        $.ajax({
            type: 'POST',
            url: 'index.php?route=extension/module/parcelamento&product_id=$product_id',
            data: $('{$this->options_container} input[type=\'text\'], {$this->options_container} input[type=\'number\'], {$this->options_container} input[type=\'hidden\'], {$this->options_container} input[type=\'radio\']:checked, {$this->options_container} input[type=\'checkbox\']:checked, {$this->options_container} select, {$this->options_container} textarea'),
            dataType: 'json',
            beforeSend: function() {
            },
            complete: function() {
            },
            success: function(json) {
                if (json.success) {
                    if ($('{$this->parcelamento_container}').length > 0 && json.parcelamento) {
                        animation_on_change_options('{$this->parcelamento_container}', json.parcelamento);
                    }
                }
            }
        });
    }

    var animation_on_change_options = function(selector_class_or_id, new_html_content) {
        $(selector_class_or_id).fadeOut(150, function() {
            $(this).html(new_html_content).fadeIn(50);
        });
    }

    if ($('{$this->options_container} input[name=\'quantity\']').val() > 1) {
        parcelamento_ajax_call();
    }

    if ( jQuery.isFunction(jQuery.fn.on) ) {
        $(document).on('change', '{$this->options_container} input[type=\'radio\']:checked, {$this->options_container} input[type=\'checkbox\'], {$this->options_container} select', function () {
            parcelamento_ajax_call();
        });
        $(document).on('input', '{$this->options_container} input[type=\'number\'], {$this->options_container} input[name=\'quantity\']', function () {
            parcelamento_ajax_call();
        });
    } else {
        $('{$this->options_container} input[type=\'text\'], {$this->options_container} input[type=\'number\'], {$this->options_container} input[type=\'hidden\'], {$this->options_container} input[type=\'radio\']:checked, {$this->options_container} input[type=\'checkbox\'], {$this->options_container} select, {$this->options_container} textarea, {$this->options_container} input[name=\'quantity\']').live('change touchend', function() {
            parcelamento_ajax_call();
        });
    }
    // Support spinner_quantity
    if($('.number-spinner button').length){
        $(document).on('click', '.number-spinner button', function(){
            setTimeout(function(){
                parcelamento_ajax_call();
            }, 100);
        });
    }
    // Support bt_claudine
    if($('.increase').length || $('.decrease').length){
        $(document).on('click', '.increase', function(){
            setTimeout(function(){
                parcelamento_ajax_call();
            }, 100);
        });
        $(document).on('click', '.decrease', function(){
            setTimeout(function(){
                parcelamento_ajax_call();
            }, 100);
        });
    }
    // Support Pav Theme
    if($('.quantity-adder .add-action').length){
        $(".quantity-adder .add-action").bind("mouseup touchend", function(e){
            setTimeout(function(){
                parcelamento_ajax_call();
            }, 100);
        });
    }
    // Support so-jenzo
    if($('.product_quantity_up').length || $('.product_quantity_down').length){
        $('.product_quantity_up,.product_quantity_down').click(function(){
            setTimeout(function(){
                parcelamento_ajax_call();
            }, 100);
        });
    }
    // Support Vitalia
    if($('#q_up').length || $('#q_down').length){
        $('#q_up,#q_down').click(function(){
            setTimeout(function(){
                parcelamento_ajax_call();
            }, 100);
        });
    }
    // Support Journal2
    if($('.qty .journal-stepper').length){
        $(".qty .journal-stepper").bind("mouseup touchend", function(e){
            setTimeout(function(){
                parcelamento_ajax_call();
            }, 100);
        });
    }
    // Support Journal3
    if($('.stepper').length){
        $(".stepper").bind("mouseup touchend", function(e){
            setTimeout(function(){
                parcelamento_ajax_call();
            }, 100);
        });
    }
HTML;

        echo $js;
        exit;
    }

    private function get_options_price($option_value) {
        $options_price = 0;
        if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
            if ((float)$option_value['price']) {
                $price = $option_value['price'];
            } else {
                $price = false;
            }

            if ($price) {
                if ($option_value['price_prefix'] === '+') {
                    $options_price = $options_price + (float)$price;
                } else {
                    $options_price = $options_price - (float)$price;
                }
            }
        }

        return $options_price;
    }
}