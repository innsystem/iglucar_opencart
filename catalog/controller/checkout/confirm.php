<?php
class ControllerCheckoutConfirm extends Controller {
	public function index() {
		$redirect = '';

		if ($this->cart->hasShipping()) {
			// Validate if shipping address has been set.
			if (!isset($this->session->data['shipping_address'])) {
				$redirect = $this->url->link('checkout/checkout', '', true);
			}

			// Validate if shipping method has been set.
			if (!isset($this->session->data['shipping_method'])) {
				$redirect = $this->url->link('checkout/checkout', '', true);
			}
		} else {
			unset($this->session->data['shipping_address']);
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
		}

		// Validate if payment address has been set.
		if (!isset($this->session->data['payment_address'])) {
			$redirect = $this->url->link('checkout/checkout', '', true);
		}

		// Validate if payment method has been set.
		if (!isset($this->session->data['payment_method'])) {
			$redirect = $this->url->link('checkout/checkout', '', true);
		}

		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$redirect = $this->url->link('checkout/cart');
		}

		// Validate minimum quantity requirements.
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$redirect = $this->url->link('checkout/cart');

				break;
			}
		}

		if (!$redirect) {
			$order_data = array();

			$totals = array();
			$taxes = $this->cart->getTaxes();
			$total = 0;

			// Because __call can not keep var references so we put them into an array.
			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);

			$this->load->model('setting/extension');

			$sort_order = array();

			$results = $this->model_setting_extension->getExtensions('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
				if ($this->config->get('total_' . $result['code'] . '_status')) {
					$this->load->model('extension/total/' . $result['code']);

					// We have to put the totals in an array so that they pass by reference.
					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
				}
			}

			$sort_order = array();

			foreach ($totals as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $totals);

			$order_data['totals'] = $totals;

			$this->load->language('checkout/checkout');

			$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
			$order_data['store_id'] = $this->config->get('config_store_id');
			$order_data['store_name'] = $this->config->get('config_name');

			if ($order_data['store_id']) {
				$order_data['store_url'] = $this->config->get('config_url');
			} else {
				if ($this->request->server['HTTPS']) {
					$order_data['store_url'] = HTTPS_SERVER;
				} else {
					$order_data['store_url'] = HTTP_SERVER;
				}
			}
			
			$this->load->model('account/customer');

			if ($this->customer->isLogged()) {
				$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

				$order_data['customer_id'] = $this->customer->getId();
				$order_data['customer_group_id'] = $customer_info['customer_group_id'];
				$order_data['firstname'] = $customer_info['firstname'];
				$order_data['lastname'] = $customer_info['lastname'];
				$order_data['email'] = $customer_info['email'];
				$order_data['telephone'] = $customer_info['telephone'];
				$order_data['custom_field'] = json_decode($customer_info['custom_field'], true);
			} elseif (isset($this->session->data['guest'])) {
				$order_data['customer_id'] = 0;
				$order_data['customer_group_id'] = $this->session->data['guest']['customer_group_id'];
				$order_data['firstname'] = $this->session->data['guest']['firstname'];
				$order_data['lastname'] = $this->session->data['guest']['lastname'];
				$order_data['email'] = $this->session->data['guest']['email'];
				$order_data['telephone'] = $this->session->data['guest']['telephone'];
				$order_data['custom_field'] = $this->session->data['guest']['custom_field'];
			}

			$order_data['payment_firstname'] = $this->session->data['payment_address']['firstname'];
			$order_data['payment_lastname'] = $this->session->data['payment_address']['lastname'];
			$order_data['payment_company'] = $this->session->data['payment_address']['company'];
			$order_data['payment_address_1'] = $this->session->data['payment_address']['address_1'];
			$order_data['payment_address_2'] = $this->session->data['payment_address']['address_2'];
			$order_data['payment_city'] = $this->session->data['payment_address']['city'];
			$order_data['payment_postcode'] = $this->session->data['payment_address']['postcode'];
			$order_data['payment_zone'] = $this->session->data['payment_address']['zone'];
			$order_data['payment_zone_id'] = $this->session->data['payment_address']['zone_id'];
			$order_data['payment_country'] = $this->session->data['payment_address']['country'];
			$order_data['payment_country_id'] = $this->session->data['payment_address']['country_id'];
			$order_data['payment_address_format'] = $this->session->data['payment_address']['address_format'];
			$order_data['payment_custom_field'] = (isset($this->session->data['payment_address']['custom_field']) ? $this->session->data['payment_address']['custom_field'] : array());

			if (isset($this->session->data['payment_method']['title'])) {
				$order_data['payment_method'] = $this->session->data['payment_method']['title'];
			} else {
				$order_data['payment_method'] = '';
			}

			if (isset($this->session->data['payment_method']['code'])) {
				$order_data['payment_code'] = $this->session->data['payment_method']['code'];
			} else {
				$order_data['payment_code'] = '';
			}

			if ($this->cart->hasShipping()) {
				$order_data['shipping_firstname'] = $this->session->data['shipping_address']['firstname'];
				$order_data['shipping_lastname'] = $this->session->data['shipping_address']['lastname'];
				$order_data['shipping_company'] = $this->session->data['shipping_address']['company'];
				$order_data['shipping_address_1'] = $this->session->data['shipping_address']['address_1'];
				$order_data['shipping_address_2'] = $this->session->data['shipping_address']['address_2'];
				$order_data['shipping_city'] = $this->session->data['shipping_address']['city'];
				$order_data['shipping_postcode'] = $this->session->data['shipping_address']['postcode'];
				$order_data['shipping_zone'] = $this->session->data['shipping_address']['zone'];
				$order_data['shipping_zone_id'] = $this->session->data['shipping_address']['zone_id'];
				$order_data['shipping_country'] = $this->session->data['shipping_address']['country'];
				$order_data['shipping_country_id'] = $this->session->data['shipping_address']['country_id'];
				$order_data['shipping_address_format'] = $this->session->data['shipping_address']['address_format'];
				$order_data['shipping_custom_field'] = (isset($this->session->data['shipping_address']['custom_field']) ? $this->session->data['shipping_address']['custom_field'] : array());

				if (isset($this->session->data['shipping_method']['title'])) {
					$order_data['shipping_method'] = $this->session->data['shipping_method']['title'];
				} else {
					$order_data['shipping_method'] = '';
				}

				if (isset($this->session->data['shipping_method']['code'])) {
					$order_data['shipping_code'] = $this->session->data['shipping_method']['code'];
				} else {
					$order_data['shipping_code'] = '';
				}
			} else {
				$order_data['shipping_firstname'] = '';
				$order_data['shipping_lastname'] = '';
				$order_data['shipping_company'] = '';
				$order_data['shipping_address_1'] = '';
				$order_data['shipping_address_2'] = '';
				$order_data['shipping_city'] = '';
				$order_data['shipping_postcode'] = '';
				$order_data['shipping_zone'] = '';
				$order_data['shipping_zone_id'] = '';
				$order_data['shipping_country'] = '';
				$order_data['shipping_country_id'] = '';
				$order_data['shipping_address_format'] = '';
				$order_data['shipping_custom_field'] = array();
				$order_data['shipping_method'] = '';
				$order_data['shipping_code'] = '';
			}

			$order_data['products'] = array();

			foreach ($this->cart->getProducts() as $product) {
				$option_data = array();

				foreach ($product['option'] as $option) {
					$option_data[] = array(
						'product_option_id'       => $option['product_option_id'],
						'product_option_value_id' => $option['product_option_value_id'],
						'option_id'               => $option['option_id'],
						'option_value_id'         => $option['option_value_id'],
						'name'                    => $option['name'],
						'value'                   => $option['value'],
						'type'                    => $option['type']
					);
				}

				$order_data['products'][] = array(
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					'download'   => $product['download'],
					'quantity'   => $product['quantity'],
					'subtract'   => $product['subtract'],
					'price'      => $product['price'],
					'total'      => $product['total'],
					'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
					'reward'     => $product['reward']
				);
			}

			// Gift Voucher
			$order_data['vouchers'] = array();

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $voucher) {
					$order_data['vouchers'][] = array(
						'description'      => $voucher['description'],
						'code'             => token(10),
						'to_name'          => $voucher['to_name'],
						'to_email'         => $voucher['to_email'],
						'from_name'        => $voucher['from_name'],
						'from_email'       => $voucher['from_email'],
						'voucher_theme_id' => $voucher['voucher_theme_id'],
						'message'          => $voucher['message'],
						'amount'           => $voucher['amount']
					);
				}
			}

			$order_data['comment'] = $this->session->data['comment'];
			$order_data['total'] = $total_data['total'];

			if (isset($this->request->cookie['tracking'])) {
				$order_data['tracking'] = $this->request->cookie['tracking'];

				$subtotal = $this->cart->getSubTotal();

				// Affiliate
				$affiliate_info = $this->model_account_customer->getAffiliateByTracking($this->request->cookie['tracking']);

				if ($affiliate_info) {
					$order_data['affiliate_id'] = $affiliate_info['customer_id'];
					$order_data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
				} else {
					$order_data['affiliate_id'] = 0;
					$order_data['commission'] = 0;
				}

				// Marketing
				$this->load->model('checkout/marketing');

				$marketing_info = $this->model_checkout_marketing->getMarketingByCode($this->request->cookie['tracking']);

				if ($marketing_info) {
					$order_data['marketing_id'] = $marketing_info['marketing_id'];
				} else {
					$order_data['marketing_id'] = 0;
				}
			} else {
				$order_data['affiliate_id'] = 0;
				$order_data['commission'] = 0;
				$order_data['marketing_id'] = 0;
				$order_data['tracking'] = '';
			}

			$order_data['language_id'] = $this->config->get('config_language_id');
			$order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
			$order_data['currency_code'] = $this->session->data['currency'];
			$order_data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
			$order_data['ip'] = $this->request->server['REMOTE_ADDR'];

			if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
				$order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
			} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
				$order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
			} else {
				$order_data['forwarded_ip'] = '';
			}

			if (isset($this->request->server['HTTP_USER_AGENT'])) {
				$order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
			} else {
				$order_data['user_agent'] = '';
			}

			if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
				$order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
			} else {
				$order_data['accept_language'] = '';
			}

			$this->load->model('checkout/order');

			$this->session->data['order_id'] = $this->model_checkout_order->addOrder($order_data);


			if ($this->config->get('shipping_melhorenvios_status') == '1' && substr($this->session->data['shipping_method']['code'], 0, 12) == 'melhorenvios') {	
		    		
				if ($this->config->get('shipping_melhorenvios_type') == 0) {
					$url    = 'https://melhorenvio.com.br';
					$token  = $this->config->get('shipping_melhorenvios_token');    
				} else {
					$url    = 'https://sandbox.melhorenvio.com.br';
					$token  = $this->config->get('shipping_melhorenvios_token');        
				}
				
				$tkn = "T3BlbkNhcnQgTWFzdGVyIChzdXBvcnRlQG9wZW5jYXJ0bWFzdGVyLmNvbS5icik=";
				$header = array('Accept: application/json', 'Content-Type: application/json;charset=UTF-8', 'Authorization: Bearer '. $token, 'User-Agent: ' . base64_decode($tkn) );
		
				$prod = array();
				$prods2 = array();
		
				foreach ($this->cart->getProducts() as $product) {
					if ($product['shipping']) {
						$prod[] = array('id' => $product['name'],  'width' => number_format($this->length->convert($product['width'], $product['length_class_id'], $this->config->get('config_length_class_id')), 1), 'height' => number_format($this->length->convert($product['height'], $product['length_class_id'], $this->config->get('config_length_class_id')), 1), 'length' => number_format($this->length->convert($product['length'] , $product['length_class_id'], $this->config->get('config_length_class_id')), 1),'weight' => number_format($this->weight->convert($product['weight2'], $product['weight_class_id'], $this->config->get('config_weight_class_id')), 1), 'insurance_value' => number_format($product['price'], 2, '.', ''), 'quantity' => (int)$product['quantity']);
						$prods2[] = array('name' => $product['name'], 'quantity' => $product['quantity'], 'unitary_value' => number_format($product['price'], 2, '.', ''));
						
					}
				}
		
				if($this->config->get('shipping_melhorenvios_ar') == 0) {
					$ar = false;
				} else {
					$ar = true;
				}
				
				if($this->config->get('shipping_melhorenvios_mp') == 0) {
					$mp = false;
				} else {
					$mp = true;
				}
		
				if($this->config->get('shipping_melhorenvios_col') == 0) {
					$col = false;
				} else {
					$col = true;
				}
				
				$postcode = preg_replace("/[^0-9]/", "", $order_data['shipping_postcode']);
				$postcode2 = preg_replace("/[^0-9]/", "", $this->config->get('shipping_melhorenvios_postcode'));
				$service = array(str_replace('melhorenvios.', '', $this->session->data['shipping_method']['code']));
				$ie = preg_replace("/[^0-9]/", "", $this->config->get('shipping_melhorenvios_ie'));
				
				$json_convert  = json_encode(array('from' => array('postal_code' => $postcode2), 'to' => array('postal_code' => $postcode), 'products' => $prod, 'options' => array('receipt' => $ar, 'own_hand' => $mp, 'collect' => $col)));
				
				$getquote = $this->getCotation($url, $json_convert, $header);
				
				   if (array_key_exists("message", $getquote)) {
					$ativar = false;
				   } else {
					$ativar = true;
				   }
		
				  if($this->config->get('shipping_melhorenvios_security') == 1) {
				  $seguro = true;
				  } else {
				  $seguro = false;
				  }
			
				   if($ativar) { 
					   $prodss_ = array();
					   $volumes_ = array();
					   foreach($getquote as $value)	{
						 if (!array_key_exists("error", $value) && in_array($value['id'], $service)) {
						   $contar = count($value['packages']);
						   for ($i = 0; $i < $contar; $i ++) {
							   
							   $conta2 = count($value['packages'][$i]['products']);
							  
							   $phei[$i] = $value['packages'][$i]['dimensions']['height'];
							   $pwid[$i] = $value['packages'][$i]['dimensions']['width'];
							   $plen[$i] = $value['packages'][$i]['dimensions']['length'];
							   $pwei[$i] = $value['packages'][$i]['weight'];
							   $ppri[$i] = $seguro ? 0 : $value['packages'][$i]['insurance_value'];
							   for($x = 0; $x < $conta2; $x++) {
								   $prodss_[$i] = array('name' => $value['packages'][$i]['products'][$x]['id'], 'quantity' => $value['packages'][$i]['products'][$x]['quantity'], 'unitary_value' => $ppri[$i]);
							   }
							  
						  }
						  }
				   } 
				
				   if(strlen(preg_replace("/[^0-9]/", "", $this->config->get('shipping_melhorenvios_doc'))) > 11) {
					$document = 'company_document';
				   } else {
					$document = 'document'; 
				   }
				   
				   $madrress = explode(":", $this->config->get('shipping_melhorenvios_ad'));
				   
				   $cnome = $order_data['shipping_firstname'] .' ' .$order_data['shipping_lastname'];
				   $cmail = $customer_info['email'];
				   $cphone = preg_replace("/[^0-9]/", "",$customer_info['telephone']);
				   $cnumcom = $order_data['shipping_custom_field'];
				   $cpfcnpj = $order_data['custom_field'];
				   $number = $cnumcom[$this->config->get('shipping_melhorenvios_doc3')];
				   if($this->config->get('shipping_melhorenvios_doc4')) {
				   $comple = $cnumcom[$this->config->get('shipping_melhorenvios_doc4')];
				   } else {
				   $comple = '';    
				   }
				   if(array_key_exists($this->config->get('shipping_melhorenvios_doc2'), $cpfcnpj)) {
				   $cdoc = preg_replace("/[^0-9]/", "",$cpfcnpj[$this->config->get('shipping_melhorenvios_doc2')]);
				   } elseif (array_key_exists($this->config->get('shipping_melhorenvios_doc2a'), $cpfcnpj)) {
				   $cdoc = preg_replace("/[^0-9]/", "",$cpfcnpj[$this->config->get('shipping_melhorenvios_doc2a')]);
				   } else {
				   $cdoc = preg_replace("/[^0-9]/", "",$cpfcnpj[$this->config->get('shipping_melhorenvios_doc2')]);
				   }
				   $caddr = $this->session->data['shipping_address']['address_1'];
				   $caddr2 = $this->session->data['shipping_address']['address_2'];
				   $ccity = $this->session->data['shipping_address']['city'];
				   $czone = $this->session->data['shipping_address']['zone_id'];
				   $ccep = preg_replace("/[^0-9]/", "",$this->session->data['shipping_address']['postcode']);
				   
				   
				   if(strlen(preg_replace("/[^0-9]/", "", $cdoc)) > 11) {
					$document2 = 'company_document';
				   } else {
					$document2 = 'document'; 
				   }
		
				   $zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$czone . "'");
				   if ($zone_query->num_rows) {
					$cst = $zone_query->row['code'];
				   } else {
					$cst = '';
				   }
				   
				   $lurl = HTTPS_SERVER .'index.php?route=account/order/info&order_id='.(int)$this->session->data['order_id'];
		
				if($this->config->get('shipping_melhorenvios_ar') == 0) {
					$ar = false;
				} else {
					$ar = true;
				}
				
				if($this->config->get('shipping_melhorenvios_mp') == 0) {
					$mp = false;
				} else {
					$mp = true;
				}
		
				if($this->config->get('shipping_melhorenvios_col') == 0) {
					$col = false;
				} else {
					$col = true;
				}
		  
				if ($this->session->data['shipping_method']['code'] == 'melhorenvios.15' || $this->session->data['shipping_method']['code'] == 'melhorenvios.16') {
				 $agency = $this->config->get('shipping_melhorenvios_agency2');
				} elseif ($this->session->data['shipping_method']['code'] == 'melhorenvios.3' || $this->session->data['shipping_method']['code'] == 'melhorenvios.4') {
				 $agency = $this->config->get('shipping_melhorenvios_agency');
				} else {
				 $agency = '';
				}
				   
				   if ($this->session->data['shipping_method']['code'] == 'melhorenvios.1' || $this->session->data['shipping_method']['code'] == 'melhorenvios.2' || $this->session->data['shipping_method']['code'] == 'melhorenvios.17') {
					   $servico = '';
					   $servico2 = '';
					   for($x = 0; $x < $contar; $x++){
					   $volumes_[$x]  = array('height' => $phei[$x], 'width' => $pwid[$x], 'length' => $plen[$x], 'weight' => $pwei[$x]);
				   
					   $servico .= json_encode(array('service' => str_replace('melhorenvios.', '', $this->session->data['shipping_method']['code']), 'agency' => $agency, 'from' => array('name' => $this->config->get('config_owner'), 'phone' => $this->config->get('config_telephone'), 'email' => $this->config->get('config_email'), $document => $this->config->get('shipping_melhorenvios_doc'), 'state_register' => $ie, 'address' => $madrress[0], 'complement' => $madrress[2], 'number' => $madrress[1], 'district' => $madrress[3], 'city' => $madrress[4], 'country_id' => 'BR', 'postal_code' => $postcode2, 'note' => '' ), 'to' => array('name' => $cnome, 'phone' => $cphone, 'email' => $cmail, $document2 => $cdoc, 'address' => $caddr, 'complement' => $comple, 'number' => $number, 'district' => $caddr2, 'city' => $ccity, 'state_abbr' => $cst, 'country_id' => 'BR', 'postal_code' => $ccep, 'note' => '' ), 'products' => [$prodss_[$x]], 'volumes' => array('height' => $phei[$x], 'width' => $pwid[$x], 'length' => $plen[$x], 'weight' => $pwei[$x]), 'options' => array('insurance_value' => $ppri[$x], 'receipt' => $ar, 'own_hand' => $mp, 'collect' => $col, 'reverse' => false, 'non_commercial' => true, 'platform' => 'Opencart - Master', 'tags' => array('tags' => (int)$this->session->data['order_id'], 'url' => $lurl)))). "||";
					   $servico2 .= json_encode(array('service' => str_replace('melhorenvios.', '', $this->session->data['shipping_method']['code']), 'agency' => $agency, 'from' => array('name' => $this->config->get('config_owner'), 'phone' => $this->config->get('config_telephone'), 'email' => $this->config->get('config_email'), $document => $this->config->get('shipping_melhorenvios_doc'), 'state_register' => $ie, 'address' => $madrress[0], 'complement' => $madrress[2], 'number' => $madrress[1], 'district' => $madrress[3], 'city' => $madrress[4], 'country_id' => 'BR', 'postal_code' => $postcode2, 'note' => '' ), 'to' => array('name' => $cnome, 'phone' => $cphone, 'email' => $cmail, $document2 => $cdoc, 'address' => $caddr, 'complement' => $comple, 'number' => $number, 'district' => $caddr2, 'city' => $ccity, 'state_abbr' => $cst, 'country_id' => 'BR', 'postal_code' => $ccep, 'note' => '' ), 'products' => [$prodss_[$x]], 'volumes' => array('height' => $phei[$x], 'width' => $pwid[$x], 'length' => $plen[$x], 'weight' => $pwei[$x]), 'options' => array('insurance_value' => $ppri[$x], 'receipt' => $ar, 'own_hand' => $mp, 'collect' => $col, 'reverse' => false, 'non_commercial' => false, 'invoice' => array('key' => 'NOTA-FISCAL'), 'platform' => 'Opencart - Master', 'tags' => array('tags' => (int)$this->session->data['order_id'], 'url' => $lurl)))). "||";
		
					   }
				   } else {
				   for($x = 0; $x < $contar; $x++){
					   $volumes_[$x]  = array('height' => $phei[$x], 'width' => $pwid[$x], 'length' => $plen[$x], 'weight' => $pwei[$x]);
				   }   
					   
				   $servico = json_encode(array('service' => str_replace('melhorenvios.', '', $this->session->data['shipping_method']['code']), 'agency' => $agency, 'from' => array('name' => $this->config->get('config_owner'), 'phone' => $this->config->get('config_telephone'), 'email' => $this->config->get('config_email') , $document => $this->config->get('shipping_melhorenvios_doc'), 'state_register' => $ie, 'address' => $madrress[0], 'complement' => $madrress[2], 'number' => $madrress[1], 'district' => $madrress[3], 'city' => $madrress[4], 'country_id' => 'BR', 'postal_code' => $postcode2, 'note' => '' ), 'to' => array('name' => $cnome, 'phone' => $cphone, 'email' => $cmail, $document2 => $cdoc, 'address' => $caddr, 'complement' => $comple, 'number' => $number, 'district' => $caddr2, 'city' => $ccity, 'state_abbr' => $cst, 'country_id' => 'BR', 'postal_code' => $ccep, 'note' => '' ), 'products' => $prods2, 'volumes' => $volumes_, 'options' => array('insurance_value' => number_format($this->cart->getTotal(), 2, '.', ''), 'receipt' => $ar, 'own_hand' => $mp, 'collect' => $col, 'reverse' => false, 'non_commercial' => true, 'platform' => 'Opencart - Master', 'tags' => array('tags' => (int)$this->session->data['order_id'], 'url' => $lurl))));
		
					$servico2 = json_encode(array('service' => str_replace('melhorenvios.', '', $this->session->data['shipping_method']['code']), 'agency' => $agency, 'from' => array('name' => $this->config->get('config_owner'), 'phone' => $this->config->get('config_telephone'), 'email' => $this->config->get('config_email'), $document => $this->config->get('shipping_melhorenvios_doc'), 'state_register' => $ie, 'address' => $madrress[0], 'complement' => $madrress[2], 'number' => $madrress[1], 'district' => $madrress[3], 'city' => $madrress[4], 'country_id' => 'BR', 'postal_code' => $postcode2, 'note' => '' ), 'to' => array('name' => $cnome, 'phone' => $cphone, 'email' => $cmail, $document2 => $cdoc, 'address' => $caddr, 'complement' => $comple, 'number' => $number, 'district' => $caddr2, 'city' => $ccity, 'state_abbr' => $cst, 'country_id' => 'BR', 'postal_code' => $ccep, 'note' => '' ), 'products' => $prods2, 'volumes' => $volumes_, 'options' => array('insurance_value' => number_format($this->cart->getTotal(), 2, '.', ''), 'receipt' => $ar, 'own_hand' => $mp, 'collect' => $col, 'reverse' => false, 'non_commercial' => false, 'invoice' => array('key' => 'NOTA-FISCAL'), 'platform' => 'Opencart - Master', 'tags' => array('tags' => (int)$this->session->data['order_id'], 'url' => $lurl))));
				   }
				   
					$os_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_shipping` WHERE order_id = '" . (int)$this->session->data['order_id'] . "'");
		
					if ($os_query->num_rows) {
					   $this->db->query("UPDATE " . DB_PREFIX . "order_shipping SET service = '" . $this->db->escape($servico) . "', service2 = '" . $this->db->escape($servico2) . "',date_modified = NOW() WHERE order_id = '" . (int)$this->session->data['order_id'] . "'");
					} else {
					  $this->db->query("INSERT INTO " . DB_PREFIX . "order_shipping SET order_id = '" . (int)$this->session->data['order_id'] . "', service = '" . $this->db->escape($servico) . "', service2 = '" . $this->db->escape($servico2) . "', date_added = NOW()");  
					}
				}
				
				}

			$this->load->model('tool/upload');

			$data['products'] = array();

			foreach ($this->cart->getProducts() as $product) {
				$option_data = array();

				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}

				$recurring = '';

				if ($product['recurring']) {
					$frequencies = array(
						'day'        => $this->language->get('text_day'),
						'week'       => $this->language->get('text_week'),
						'semi_month' => $this->language->get('text_semi_month'),
						'month'      => $this->language->get('text_month'),
						'year'       => $this->language->get('text_year'),
					);

					if ($product['recurring']['trial']) {
						$recurring = sprintf($this->language->get('text_trial_description'), $this->currency->format($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
					}

					if ($product['recurring']['duration']) {
						$recurring .= sprintf($this->language->get('text_payment_description'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
					} else {
						$recurring .= sprintf($this->language->get('text_payment_cancel'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
					}
				}

				$data['products'][] = array(
					'cart_id'    => $product['cart_id'],
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					'recurring'  => $recurring,
					'quantity'   => $product['quantity'],
					'subtract'   => $product['subtract'],
					'price'      => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']),
					'total'      => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'], $this->session->data['currency']),
					'href'       => $this->url->link('product/product', 'product_id=' . $product['product_id'])
				);
			}

			// Gift Voucher
			$data['vouchers'] = array();

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $voucher) {
					$data['vouchers'][] = array(
						'description' => $voucher['description'],
						'amount'      => $this->currency->format($voucher['amount'], $this->session->data['currency'])
					);
				}
			}

			$data['totals'] = array();

			foreach ($order_data['totals'] as $total) {
				$data['totals'][] = array(
					'title' => $total['title'],
					'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
				);
			}

			$data['payment'] = $this->load->controller('extension/payment/' . $this->session->data['payment_method']['code']);
		} else {
			$data['redirect'] = $redirect;
		}

		$this->response->setOutput($this->load->view('checkout/confirm', $data));
		
	}

	public function getCotation($url, $json_convert, $header) {
		$soap_do = curl_init();
		curl_setopt($soap_do, CURLOPT_URL, $url .'/api/v2/me/shipment/calculate/');
		curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($soap_do, CURLOPT_TIMEOUT,        10);
		curl_setopt($soap_do, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($soap_do, CURLOPT_POST,           true );
		curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $json_convert);
		curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);
		$response = curl_exec($soap_do); 
		curl_close($soap_do);
		
		$retornou = json_decode($response, true);
		return   $retornou;
		}

}
