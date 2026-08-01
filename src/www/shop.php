<?php
$controller_type = "shop";

$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


$action = $page->actions();
$model = shop(); //new Shop();


$page->bodyClass("shop");
$page->pageTitle("Shop");


if($action) {

	// /shop/receipt
	if($action[0] == "receipt") {


		if(count($action) >= 3 && $action[1] === "orders") {

			$page->page(array(
				"templates" => "shop/receipt/orders.php"
			));
			exit();

		}
		else if(count($action) >= 3 && $action[1] === "new-order") {

			$page->page(array(
				"templates" => "shop/receipt/new-order.php"
			));
			exit();

		}
		else if(count($action) >= 3 && $action[1] === "order") {

			$page->page(array(
				"templates" => "shop/receipt/order.php"
			));
			exit();

		}
		else {

			$page->page(array(
				"templates" => "shop/receipt/error.php"
			));
			exit();

		}

	}

	else if($action[0] == "payment-gateway") {


		// process payment session response
		// /shop/payment-gateway/#gateway#/success/#session_id#
		if(count($action) == 4 && $action[2] == "success") {


			$result = payments()->processPaymentSession($action);

			debug(["result", $result]);
			exit();
			if($result["status"] === "PAYMENT_COMPLETE") {

				
				// header("Location: /shop/receipt/new-order/".$order["order_no"]."/".superNormalize($id_result["gateway"]));
				exit();

				//
				// $payment_intent_id = getVar("payment_intent");
				//
				// $id_result = payments()->identifyPaymentIntent($result["payment_intent_id"]);
				// debug([$id_result]);
				// exit();
				//
				// if($id_result && $id_result["amount_received"] === $result) {
				//
				// 	if($id_result["cart_reference"]) {
				//
				// 		$order = $model->newOrderFromCart(["newOrderFromCart", $id_result["cart_reference"]]);
				// 		// Clear messages
				// 		message()->resetMessages();
				// 		if($order) {
				//
				// 			// get payment intent
				// 			$registration_result = payments()->registerPaymentIntent($payment_intent_id, $order);
				// 			if($registration_result["status"] === "success") {
				//
				// 				// redirect to leave POST state
				// 				header("Location: /shop/receipt/new-order/".$order["order_no"]."/".superNormalize($id_result["gateway"]));
				// 				exit();
				// 			}
				//
				// 		}
				//
				// 	}
				//
				// }
				// else if($id_result && $id_result["status"] === "error") {
				//
				// 	message()->addMessage($id_result["message"], ["type" => "error"]);
				// 	// redirect to leave POST state
				// 	if($id_result["cart_reference"]) {
				// 		header("Location: /shop/payment-gateway/".$id_result["gateway"]."/cart/".$id_result["cart_reference"]);
				// 	}
				// 	else if($id_result["order_no"]) {
				// 		header("Location: /shop/payment-gateway/".$id_result["gateway"]."/order/".$id_result["order_no"]);
				// 	}
				//
				// 	exit();
				//
				// }
				//
				// // Fatal error
				// message()->addMessage("We failed to process your payment request. Please try again or <a href=\"mailto:payment@think.dk?subject=Payment%20error&body=Payment%20Intent:%20$payment_intent_id\">contact us</a> to resolve the issue.", ["type" => "error"]);
				// // redirect to leave POST state
				// header("Location: /shop/receipt/error");
				// exit();
				//
				// // redirect to leave POST state
				// header("Location: ".$result["return_url"]."/?payment_intent=".$result["payment_intent_id"]);
				//
				// exit();


			}
			else if($result["status"] === "PAYMENT_CAPTURED") {

				// redirect to leave POST state
				header("Location: ".$result["return_url"]."/?payment_intent=".$result["payment_intent_id"]);
				exit();

			}
			else if($result["status"] === "PAYMENT_READY") {

				// redirect to leave POST state
				header("Location: ".$result["return_url"]."/?payment_intent=".$result["payment_intent_id"]);
				exit();

			}
			else if($result["status"] === "ACTION_REQUIRED") {

				// redirect to leave POST state
				header("Location: ".$result["action"]);
				exit();
	
			}

			else if($result["status"] === "STRIPE_ERROR" || $result["status"] === "CARD_NOT_FOUND" || $result["status"] === "REFERENCE_NOT_FOUND") {

				// Some error from Stripe
				message()->addMessage($result["message"], ["type" => "error"]);

				if(isset($result["reference"])) {
					if($result["reference"] === "order") {
						header("Location: /shop/payment/".$result["order_no"]);
						exit();
					}
					else if($result["reference"] === "orders") {
						header("Location: /shop/payments");
						exit();
					}

				}

				header("Location: /shop/checkout");
				exit();

			}

			message()->addMessage("Unknown gateway error. Please try again.", ["type" => "error"]);
			header("Location: /shop/checkout");
			exit();

		}


		// Register intent
		else if(count($action) == 3 && $action[2] == "register-intent") {

			$payment_intent_id = getVar("payment_intent");

			$id_result = payments()->identifyPaymentIntent($payment_intent_id);

			if($id_result && $id_result["status"] === "success") {

				if($id_result["cart_reference"]) {

					$order = $model->newOrderFromCart(["newOrderFromCart", $id_result["cart_reference"]]);
					// Clear messages
					message()->resetMessages();
					if($order) {

						// get payment intent
						$registration_result = payments()->registerPaymentIntent($payment_intent_id, $order);
						if($registration_result["status"] === "success") {

							// redirect to leave POST state
							header("Location: /shop/receipt/new-order/".$order["order_no"]."/".superNormalize($id_result["gateway"]));
							exit();
						}

					}

				}

			}
			else if($id_result && $id_result["status"] === "error") {

				message()->addMessage($id_result["message"], ["type" => "error"]);
				// redirect to leave POST state
				if($id_result["cart_reference"]) {
					header("Location: /shop/payment-gateway/".$id_result["gateway"]."/cart/".$id_result["cart_reference"]);
				}
				else if($id_result["order_no"]) {
					header("Location: /shop/payment-gateway/".$id_result["gateway"]."/order/".$id_result["order_no"]);
				}
				
				exit();

			}

			// Fatal error
			message()->addMessage("We failed to process your payment request. Please try again or <a href=\"mailto:payment@think.dk?subject=Payment%20error&body=Payment%20Intent:%20$payment_intent_id\">contact us</a> to resolve the issue.", ["type" => "error"]);
			// redirect to leave POST state
			header("Location: /shop/receipt/error");
			exit();

		}


		// Register paid intent – for orders only
		else if(count($action) == 3 && $action[2] == "register-paid-intent") {

			$payment_intent_id = getVar("payment_intent");

			$id_result = payments()->identifyPaymentIntent($payment_intent_id);
			if($id_result && $id_result["status"] === "success") {

				// Single order
				if($id_result["order_no"]) {

					$order = $model->getOrders(["order_no" => $id_result["order_no"]]);
					if($order) {

						// Register intent for order (and subscription)
						$intent_registration_result = payments()->updatePaymentIntent($payment_intent_id, $order);
						if($intent_registration_result["status"] === "success") {

							// Register payment for order (if paid)
							if($id_result["payment_status"] === "succeeded") {

								$payment_registration_result = payments()->registerPayment($order, $id_result["payment_intent"]);

								// Clear messages
								message()->resetMessages();

								// Successful registration of payment
								if($payment_registration_result && $payment_registration_result["status"] === "REGISTERED") {

									// redirect to leave POST state
									header("Location: /shop/receipt/order/".$order["order_no"]."/".superNormalize($id_result["gateway"]));
									exit();

								}

							}

						}

					}

				}
				// Multiple orders
				else if($id_result["order_nos"]) {

					// Register payment for order (if paid)
					if($id_result["payment_status"] === "succeeded") {

						$orders = [];
						$order_nos = explode(",", $id_result["order_nos"]);
						foreach($order_nos as $order_no) {

							$order = $model->getOrders(["order_no" => $order_no]);
							if($order) {
								$orders[] = $order;
							}

						}

						$payments_registration_result = payments()->registerPayments($orders, $id_result["payment_intent"]);


						// Clear messages
						message()->resetMessages();

						// Successful registration of payment
						if($payments_registration_result && $payments_registration_result["status"] === "REGISTERED") {

							// redirect to leave POST state
							header("Location: /shop/receipt/orders/".$id_result["order_nos"]."/".superNormalize($id_result["gateway"]));
							exit();

						}

					}

				}

			}
			else if($id_result && $id_result["status"] === "error") {

				message()->addMessage($id_result["message"], ["type" => "error"]);
				// redirect to leave POST state
				if($id_result["order_no"]) {
					header("Location: /shop/payment-gateway/".$id_result["gateway"]."/order/".$id_result["order_no"]);
				}
				else {
					header("Location: /shop/payments");
				}
				exit();

			}

			// Fatal error
			message()->addMessage("We failed to process your payment request. Please try again or <a href=\"mailto:payment@think.dk?subject=Payment%20error&body=Payment%20intent:%20$payment_intent_id\">contact us</a> to resolve the issue.", ["type" => "error"]);
			// redirect to leave POST state
			header("Location: /shop/receipt/error");
			exit();

		}

	}


	# /shop/checkout [POST]
	else if($action[0] == "checkout" && $_SERVER['REQUEST_METHOD'] === "POST") {

		// redirect to leave POST state
		header("Location: /shop/checkout");
		exit();
	}

	# /shop/checkout
	else if($action[0] == "checkout") {

		$page->page(array(
			"templates" => "shop/checkout.php"
		));
		exit();
	}

	# /shop/profile
	else if($action[0] == "profile") {

		$page->page(array(
			"templates" => "shop/profile.php"
		));
		exit();
	}



	# /shop/confirmCartAndSelectPaymentMethod
	else if($action[0] == "confirmCartAndSelectPaymentMethod" && count($action) == 1) {

		// register payment method
		$result = $model->selectPaymentMethodForCart(["selectPaymentMethodForCart"]);
		if($result) {

			if($result["status"] === "PROCEED_TO_GATEWAY") {

				$success_url = SITE_URL . "/shop/payment-gateway/".$result["payment_gateway"]."/success/{CHECKOUT_SESSION_ID}";
				$cancel_url = SITE_URL . "/shop/checkout";

				$session = $model->createCartPaymentSession($result["cart_reference"], $success_url, $cancel_url);

				// Redirect to gateway
				if($session && $session["url"]) {

					header("Location: ".$session["url"], true, 303);
					exit();

				}
				else if($session["status"] === "CART_NOT_FOUND" || $session["status"] === "STRIPE_ERROR") {

					// Some error from Stripe
					message()->addMessage($result["message"], ["type" => "error"]);
					header("Location: /shop/checkout");
					exit();

				}
				else {

					// redirect to leave POST state
					message()->addMessage("We could not get Payment gateway session from ".ucfirst($result["payment_gateway"])." – please try again.", ["type" => "error"]);
					header("Location: /shop/checkout");
					exit();

				}

			}
			else if($result["status"] === "PROCEED_TO_RECEIPT") {

				// redirect to leave POST state
				header("Location: /shop/receipt/order/".$result["order_no"]."/".superNormalize($result["payment_name"]));
				exit();

			}
			else if($result["status"] === "ORDER_FAILED") {

				// redirect to leave POST state
				message()->addMessage("Could not create order – please try again.", ["type" => "error"]);
				header("Location: /shop/checkout");
				exit();

			}

		}

		// redirect to leave POST state
		message()->addMessage("Unrecognized payment method – please try again.", ["type" => "error"]);
		header("Location: /shop/checkout");
		exit();

	}

	# /shop/confirmCartAndSelectUserPaymentMethod
	else if($action[0] == "confirmCartAndSelectUserPaymentMethod" && count($action) == 1) {

		// register payment method
		$payment_method_result = $model->selectUserPaymentMethodForCart(["selectUserPaymentMethodForCart"]);
		if($payment_method_result) {

			if($payment_method_result["status"] === "PROCEED_TO_INTENT") {

				$return_url = str_replace("{GATEWAY}", $payment_method_result["payment_gateway"], SITE_PAYMENT_REGISTER_INTENT);
				$result = payments()->requestPaymentIntentForCart(
					$payment_method_result["cart"], 
					$payment_method_result["gateway_payment_method_id"], 
					$return_url
				);
				if($result) {

					if($result["status"] === "PAYMENT_READY") {

						// redirect to leave POST state
						header("Location: $return_url/?payment_intent=".$result["payment_intent_id"]);
						exit();

					}
					else if($result["status"] === "ACTION_REQUIRED") {

						// redirect to leave POST state
						header("Location: ".$result["action"]);
						exit();
			
					}

				}
			}
			else if($payment_method_result["status"] === "PROCEED_TO_RECEIPT") {

				// redirect to leave POST state
				header("Location: /shop/receipt/order/".$payment_method_result["order_no"]."/".superNormalize($payment_method_result["payment_name"]));
				exit();

			}
			else if($payment_method_result["status"] === "ORDER_FAILED") {

				// redirect to leave POST state
				message()->addMessage("Could not create order – please try again.", ["type" => "error"]);
				header("Location: /shop/checkout");
				exit();

			}

		}

		// redirect to leave POST state
		message()->addMessage("Unrecognized payment method – please try again.", ["type" => "error"]);
		header("Location: /shop/checkout");
		exit();

	}



	# /shop/selectPaymentMethodForOrder
	else if($action[0] == "selectPaymentMethodForOrder" && security()->validateCsrfToken()) {

		// register payment method
		$result = $model->selectPaymentMethodForOrder(array("selectPaymentMethodForOrder"));
		if($result) {

			if($result["status"] === "PROCEED_TO_GATEWAY") {

				$success_url = SITE_URL . "/shop/payment-gateway/".$result["payment_gateway"]."/success/{CHECKOUT_SESSION_ID}";
				$cancel_url = SITE_URL . "/shop/payment/".$result["order_no"];

				$session = $model->createOrderPaymentSession($result["order_no"], $success_url, $cancel_url);

				// Redirect to gateway
				if($session && $session["url"]) {

					header("Location: ".$session["url"], true, 303);
					exit();

				}
				else if($session["status"] === "ORDER_NOT_FOUND") {

					// Some error from Stripe
					message()->addMessage($result["message"], ["type" => "error"]);
					header("Location: /shop/payment/".$result["order_no"]);
					exit();

				}
				else {

					// redirect to leave POST state
					message()->addMessage("We could not get Payment gateway session from ".ucfirst($result["payment_gateway"])." – please try again.", ["type" => "error"]);
					header("Location: /shop/payment/".$result["order_no"]);
					exit();

				}

			}
			else if($result["status"] === "PROCEED_TO_RECEIPT") {

				// redirect to leave POST state
				header("Location: /shop/receipt/order/".$result["order_no"]."/".superNormalize($result["payment_name"]));
				exit();

			}

		}

		// redirect to leave POST state
		message()->addMessage("Unrecognized payment method – please try again.", ["type" => "error"]);
		header("Location: /shop/payment/".$result["order_no"]);
		exit();

	}

	# /shop/selectUserPaymentMethodForOrder
	else if($action[0] == "selectUserPaymentMethodForOrder" && count($action) == 1) {

		// register payment method
		$payment_method_result = $model->selectUserPaymentMethodForOrder(["selectUserPaymentMethodForOrder"]);
		if($payment_method_result) {

			if($payment_method_result["status"] === "PROCEED_TO_INTENT") {

				$return_url = str_replace("{GATEWAY}", $payment_method_result["payment_gateway"], SITE_PAYMENT_REGISTER_PAID_INTENT);
				$result = payments()->requestPaymentIntentForOrder(
					$payment_method_result["order"], 
					$payment_method_result["gateway_payment_method_id"], 
					$return_url
				);
				if($result) {

					if($result["status"] === "PAYMENT_CAPTURED") {

						// redirect to leave POST state
						header("Location: $return_url/?payment_intent=".$result["payment_intent_id"]);
						exit();

					}
					else if($result["status"] === "ACTION_REQUIRED") {

						// redirect to leave POST state
						header("Location: ".$result["action"]);
						exit();
			
					}

				}

			}
			else if($payment_method_result["status"] === "PROCEED_TO_RECEIPT") {

				// redirect to leave POST state
				header("Location: /shop/receipt/order/".$payment_method_result["order_no"]."/".superNormalize($payment_method_result["payment_name"]));
				exit();

			}

		}

		// redirect to leave POST state
		message()->addMessage("Unrecognized payment method – please try again.", ["type" => "error"]);
		header("Location: /shop/payments");
		exit();

	}



	# /shop/selectPaymentMethodForOrders
	else if($action[0] == "selectPaymentMethodForOrders" && security()->validateCsrfToken()) {

		// register payment method
		$result = $model->selectPaymentMethodForOrders(array("selectPaymentMethodForOrders"));
		if($result) {

			if($result["status"] === "PROCEED_TO_GATEWAY") {

				$success_url = SITE_URL . "/shop/payment-gateway/".$result["payment_gateway"]."/success/{CHECKOUT_SESSION_ID}";
				$cancel_url = SITE_URL . "/shop/payments";

				$session = $model->createOrdersPaymentSession($result["order_ids"], $success_url, $cancel_url);

				// Redirect to gateway
				if($session && $session["url"]) {

					header("Location: ".$session["url"], true, 303);
					exit();

				}
				else if($session["status"] === "ORDERS_NOT_FOUND") {

					// Some error from Stripe
					message()->addMessage($result["message"], ["type" => "error"]);
					header("Location: /shop/payments");
					exit();

				}
				else {

					// redirect to leave POST state
					message()->addMessage("We could not get Payment gateway session from ".ucfirst($result["payment_gateway"])." – please try again.", ["type" => "error"]);
					header("Location: /shop/payments");
					exit();

				}

			}
			else if($result["status"] === "PROCEED_TO_RECEIPT") {

				// redirect to leave POST state
				header("Location: /shop/receipt/orders/".$result["order_nos"]."/".superNormalize($result["payment_name"]));
				exit();

			}

		}

		// redirect to leave POST state
		message()->addMessage("Unrecognized payment method – please try again.", ["type" => "error"]);
		header("Location: /shop/payments");
		exit();

	}

	# /shop/selectUserPaymentMethodForOrders
	else if($action[0] == "selectUserPaymentMethodForOrders" && count($action) == 1) {

		// register payment method
		$payment_method_result = $model->selectUserPaymentMethodForOrders(["selectUserPaymentMethodForOrders"]);
		if($payment_method_result) {

			if($payment_method_result["status"] === "PROCEED_TO_INTENT") {

				$return_url = str_replace("{GATEWAY}", $payment_method_result["payment_gateway"], SITE_PAYMENT_REGISTER_PAID_INTENT);
				$result = payments()->requestPaymentIntentForOrders(
					$payment_method_result["orders"], 
					$payment_method_result["gateway_payment_method_id"], 
					$return_url
				);
				if($result) {

					if($result["status"] === "PAYMENT_CAPTURED") {

						// redirect to leave POST state
						header("Location: $return_url/?payment_intent=".$result["payment_intent_id"]);
						exit();

					}
					else if($result["status"] === "ACTION_REQUIRED") {

						// redirect to leave POST state
						header("Location: ".$result["action"]);
						exit();
			
					}

				}

			}
			else if($payment_method_result["status"] === "PROCEED_TO_RECEIPT") {

				// redirect to leave POST state
				header("Location: /shop/receipt/orders/".$payment_method_result["order_nos"]."/".superNormalize($payment_method_result["payment_name"]));
				exit();

			}

		}

		// redirect to leave POST state
		message()->addMessage("Unrecognized payment method – please try again.", ["type" => "error"]);
		header("Location: /shop/payments");
		exit();

	}



	# /shop/confirmOrder
	else if($action[0] == "confirmOrder" && count($action) == 2) {

		$cart_reference = $action[1];
		$cart = $model->getCarts(["cart_reference" => $cart_reference]);
		if($cart) {

			$order = $model->newOrderFromCart(["newOrderFromCart", $cart["cart_reference"]]);
			if($order) {

				// Clear messages
				message()->resetMessages();

				$total_order_price = $model->getTotalOrderPrice($order["id"]);
				if($total_order_price["price"] > 0) {

					// redirect to leave POST state
					header("Location: /shop/payment-options/".$order["order_no"]);
					exit();

				}
				// 0-order, no payment required
				else {

					// redirect to leave POST state
					header("Location: /shop/receipt/new-order/".$order["order_no"]);
					exit();

				}

			}

		}
		else {

			message()->addMessage("Could not process order – please try again.", ["type" => "error"]);

			// redirect to leave POST state
			header("Location: /shop/cart");
			exit();

		}
		exit();
	}

	# /shop/payment-options/#order_no#
	else if($action[0] == "payment-options" && count($action) == 2) {

		$page->page(array(
			"templates" => "shop/payment-options.php"
		));
		exit();
	}

	# /shop/payment/#order_no#
	else if($action[0] == "payment" && count($action) == 2) {

		$page->page(array(
			"templates" => "shop/payment.php"
		));
		exit();
	}

	# /shop/payments (all open payments)
	else if($action[0] == "payments" && count($action) == 1) {

		$page->page(array(
			"templates" => "shop/payments.php"
		));
		exit();
	}



	# /shop/addToCart
	else if($action[0] == "addToCart" && security()->validateCsrfToken()) {

		// create new user
		$cart = $model->addToCart(array("addToCart"));

		// successful creation
		if($cart) {

			message()->addMessage("Item added");
			header("Location: cart");
			exit();
		}
		// something went wrong
		else {
			message()->addMessage("Colonizing Mars! Waw. The computer is so excited it cannot process your request right now. Try again later.", array("type" => "error"));
		}

	}

	# /shop/updateCartItemQuantity
	else if($action[0] == "updateCartItemQuantity" && security()->validateCsrfToken()) {

		message()->resetMessages();


		// create new user
		$cart = $model->updateCartItemQuantity($action);

		// successful creation
		if($cart) {

			if(!message()->hasMessages()) {
				message()->addMessage("Quantity updated");
			}
			header("Location: /shop/cart");
			exit();
		}
		// something went wrong
		else {
			message()->addMessage("Colonizing Mars! Waw. The computer is so excited it cannot process your request right now. Try again later.", array("type" => "error"));
		}

	}

	# /shop/updateCartItemQuantity
	else if($action[0] == "deleteFromCart" && security()->validateCsrfToken()) {

		// create new user
		$cart = $model->deleteFromCart($action);

		// successful creation
		if($cart) {

			message()->addMessage("Item deleted from cart");
			header("Location: /shop/cart");
			exit();
		}
		// something went wrong
		else {
			message()->addMessage("Colonizing Mars! Waw. The computer is so excited it cannot process your request right now. Try again later.", array("type" => "error"));
		}

	}

	# /shop/signup
	else if($action[0] == "register" && security()->validateCsrfToken()) {

		// create new user
		// $UC = new User();
		$result = $model->API_registerCustomer(array("register"));
		// debug([$result, message()->getMessages()]);
		// exit();
		//
		// $user = $UC->newUser(array("newUser"));


		// user exists
		// Detected bot signup
		if(isset($result["status"])) {

			// Identified as bot signup
			if($result["status"] == "BOT_SIGNUP") {

				// redirect to leave POST state
				header("Location: /quarantine");
				exit();

			}
			// User exists
			else if($result["status"] == "USER_EXISTS") {
				message()->addMessage("A user already exists with that email. Try logging in.", ["type" => "error"]);
			}
			// Missing terms
			else if($result["status"] == "MISSING_TERMS") {
				message()->addMessage("Terms must be accepted to complete the checkout.", ["type" => "error"]);
			}
			// Unknown error
			else {
				message()->addMessage("An unknown error orrured. Please try again.", ["type" => "error"]);
			}

			// return to checkout page with posted variables to pre-populate form
			$page->page(array(
				"templates" => "shop/checkout.php"
			));
			exit();

		}

		// something went wrong
		else if(!isset($result["user_id"])) {

			message()->addMessage("Blib, Blob, Bliiiiip. An unknown error occured. Please try again.", ["type" => "error"]);

			// return to checkout page with posted variables to pre-populate form
			$page->page(array(
				"templates" => "shop/checkout.php"
			));
			exit();

		}
		// signup was completed
		else {

			header("Location: /shop/verify");
			exit();

		}

	}

	# /shop/updateProfile
	else if($action[0] == "updateProfile" && security()->validateCsrfToken()) {

		// create new user
		$UC = new User();
		$user = $UC->update(array("update"));

		// redirect to leave POST state
		header("Location: /shop/checkout");
		exit();
	}

	# /shop/selectAddress
	else if($action[0] == "selectAddress" && security()->validateCsrfToken()) {

		if($model->updateCart(array("updateCart"))) {
			// redirect to leave POST state
			header("Location: /shop/checkout");
			exit();
		}
		else {
			$page->page(array(
				"templates" => "shop/address.php"
			));
			exit();
		}

	}

	# /shop/addAddress
	else if($action[0] == "addAddress" && count($action) == 2 && security()->validateCsrfToken()) {

		$UC = new User();
		$address = $UC->addAddress(array("addAddress"));
		if($address) {
			$_POST[$action[1]."_address_id"] = $address["id"];

			if($model->updateCart(array("updateCart"))) {

				// redirect to leave POST state
				header("Location: /shop/checkout");
				exit();
			}
		}

		$page->page(array(
			"templates" => "shop/address.php"
		));
		exit();
	}

	# /shop/address
	else if($action[0] == "address" && count($action) == 2) {

		$page->page(array(
			"templates" => "shop/address.php"
		));
		exit();
	}


	# /shop/verify
	else if($action[0] == "verify") {

		// /shop/verify/confirm
		if(count($action) == 2 && $action[1] == "confirm" && security()->validateCsrfToken()) {

			$username = session()->value("signup_email");
			$verification_code = getPost("verification_code");
	
			$UC = new User();
	
			// Verify and enable user
			$result = $UC->confirmUsername($username, $verification_code);

			// code is valid
			if($result) {

				// check if there is a cart
				$cart = $model->getCart();
				// cart exists
				if($cart) {
					$total_price = $model->getTotalCartPrice($cart["id"]);

					// if order has price
					if($total_price && $total_price["price"]) {
						// redirect to leave POST state
						// to checkout and confirm order
						message()->addMessage("You're now verified – please go ahead and confirm your order.", array("type" => "message"));
						header("Location: /shop/checkout");
						exit();
					}
					// order is zero priced
					else {
						// confirm free order directly (this will redirect to receipt)
						header("Location: /shop/confirm/".$cart["cart_reference"]);
						exit();
					}
				}
				// no cart - go to cart
				else {
					message()->addMessage("You're now verified", array("type" => "message"));
					header("Location: /shop/cart");
					exit();
				}

			}

			// code is not valid
			else {
				message()->addMessage("Incorrect verification code, try again!", ["type" => "error"]);
				header("Location: /shop/verify");
				exit();
			}

		}
		// /shop/verify/skip
		else if(count($action) == 2 && $action[1] == "skip") {

			header("Location: /shop/checkout");
			exit();

		}
		
		else {

			// /verify
			$page->page(array(
				"templates" => "shop/verify.php"
			));
			exit();

		}

	}


}

// plain signup directly
// /signup
$page->page(array(
	"templates" => "shop/cart.php"
));

?>
