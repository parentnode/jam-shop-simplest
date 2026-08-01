<?php
global $action;
global $model;

$this->pageTitle("Payment");

$UC = new User();
$MC = new Member();

// get current user id
$user_id = session()->value("user_id");


$order_no = $action[1];
$remaining_order_price = false;

// Will only return orders from current user, so no need to check order ownership
$order = $model->getOrders(array("order_no" => $order_no));
$membership = $MC->getMembership();

if($order && $order["payment_status"] != 2 && $order["status"] != 3) {

	$remaining_order_price = $model->getRemainingOrderPrice($order["id"]);


	$payment_methods = $this->paymentMethods();

	// Get payment methods
	$user_payment_methods = $UC->getPaymentMethods(["extend" => true]);

}

?>
<div class="scene shopPayment i:payment">
	<h1>Payment</h1>

<? if($order && $remaining_order_price && $remaining_order_price["price"]): ?>


	<?= $HTML->serverMessages() ?>


	<dl class="amount">
		<dt class="amount">Due amount</dt>
		<dd class="amount"><?= formatPrice($remaining_order_price) ?></dd>
	</dl>


	<h2>For the payment of:</h2>

	<ul class="orders">
		<li>
			<h3>Order no: <?= $order["order_no"] . ($order["comment"] ? (" – " . $order["comment"]) : "") ?></h3>
			<ul class="orderitems">
			<? foreach($order["items"] as $order_item): ?>
				<li><?= $order_item["quantity"] ?> x <?= $order_item["name"] ?></li>
			<? endforeach; ?>
			</ul>
		</li>
	</ul>


	<div class="payment_method">
		<h2>Choose a payment method</h2>

		<? if($user_payment_methods): ?>
			<h3>Your payment methods</h3>
			<p>Choose one of your existing payment methods to continue processing this order.</p>
			<ul class="payment_methods">

			<? foreach($user_payment_methods as $user_payment_method): ?>

				<? if($user_payment_method && $user_payment_method["cards"]): ?>

					<? foreach($user_payment_method["cards"] as $card): ?>
				<li class="payment_method user_payment_method<?= $user_payment_method["classname"] ? " ".$user_payment_method["classname"] : "" ?>">
					<ul class="actions">
						<?= $HTML->oneButtonForm(
						"Pay order with card ending in " . $card["last4"], 
						"/shop/selectUserPaymentMethodForOrder",
						array(
							"inputs" => array(
								"order_id" => $order["id"], 
								"user_payment_method_id" => $user_payment_method["id"], 
								"payment_method_id" => $user_payment_method["payment_method_id"],
								"gateway_payment_method_id" => $card["id"]
							),
							"confirm-value" => false,
							"wait-value" => "Please wait",
							"dom-submit" => true,
							"class" => "primary",
							"name" => "continue",
							"wrapper" => "li.continue.".$user_payment_method["classname"],
						)) ?>
					</ul>
					<p><?= $user_payment_method["description"] ?></p>
				</li>
					<? endforeach; ?>

				<? else: ?>
				<li class="payment_method user_payment_method<?= $user_payment_method["classname"] ? " ".$user_payment_method["classname"] : "" ?>">
					<ul class="actions">
						<?= $HTML->oneButtonForm(
						"Pay order with " . $user_payment_method["name"], 
						"/shop/selectUserPaymentMethodForOrder",
						array(
							"inputs" => array(
								"order_id" => $order["id"], 
								"user_payment_method_id" => $user_payment_method["id"], 
								"payment_method_id" => $user_payment_method["payment_method_id"]
							),
							"confirm-value" => false,
							"wait-value" => "Please wait",
							"dom-submit" => true,
							"class" => "primary",
							"name" => "continue",
							"wrapper" => "li.continue.".$user_payment_method["classname"],
						)) ?>
					</ul>
					<p><?= $user_payment_method["description"] ?></p>
				</li>
				<? endif; ?>

			<? endforeach; ?>

			</ul>
		<? endif; ?>

		<? if($payment_methods): ?>
			<h3>Our <?= $user_payment_methods ? "other " : "" ?>payment options</h3>
			<p><?= $user_payment_methods ? "Or, p" : "P" ?>lease choose a payment method to continue processing this order.</p>
			<ul class="payment_methods">

			<? foreach($payment_methods as $payment_method): ?>
				<? if($payment_method["state"] === "public"): ?>

				<li class="payment_method<?= $payment_method["classname"] ? " ".$payment_method["classname"] : "" ?>">

					<ul class="actions">
						<?= $HTML->oneButtonForm(
						"Pay now with " . $payment_method["name"], 
						"/shop/selectPaymentMethodForOrder", 
						array(
							"inputs" => array(
								"order_id" => $order["id"], 
								"payment_method_id" => $payment_method["id"]
							),
							"confirm-value" => false,
							"wait-value" => "Please wait",
							"dom-submit" => true,
							"class" => "primary",
							"name" => "continue",
							"wrapper" => "li.continue.".$payment_method["classname"],
						)) ?>
					</ul>
					<p><?= $payment_method["description"] ?></p>

				</li>
				<? endif; ?>
			<? endforeach; ?>

			</ul>
		<? endif; ?>
	</div>

<? // No payments
elseif($order && $remaining_order_price && $remaining_order_price["price"] === 0): ?>

	<h2>Great news</h2>
	<p>You do not have any outstanding payments.</p>

<? // User logged in, but no order
elseif($user_id > 1): ?>

	<h2>Order could not be found</h2>
	<p>Check if you have any other <a href="/shop/payments">outstanding payments</a>.</p>

<? 
// User not logged in
else:

	$model = new User();
	$username = stringOr(getPost("username"));
	?>

	<h2>Looking to make a payment?</h2>
	<p>Please log in to your account.</p>


	<?= $model->formStart("?login=true", array("class" => "login labelstyle:inject")) ?>
		<?= $model->input("login_forward", ["type" => "hidden", "value" => $this->url]); ?>


		<?= $HTML->serverMessages() ?>


		<fieldset>
			<?= $model->input("username", array("required" => true, "value" => $username)); ?>
			<?= $model->input("password", array("required" => true)); ?>
		</fieldset>

		<ul class="actions">
			<?= $model->submit("Log in", array("class" => "primary", "wrapper" => "li.login")) ?>
		</ul>
	<?= $model->formEnd() ?>
	

<? endif;?>

</div>