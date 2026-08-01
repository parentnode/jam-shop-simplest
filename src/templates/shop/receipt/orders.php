<?php
global $action;
global $model;

$this->pageTitle("Receipt");

$orders = [];
$order_no_list = false;
$receipt_type = false;
$total_orders_price = 0;
$total_remaining_price = 0;

// get current user id
$user_id = session()->value("user_id");


$order_nos = $action[2];
if($order_nos) {
	
	$order_no_list = explode(",", $order_nos);

	foreach($order_no_list as $order_no) {

		$order = $model->getOrders(array("order_no" => $order_no));
		if($order) {

			$orders[] = $order;

			$remaining_order_price = $model->getRemainingOrderPrice($order["id"]);
			$total_remaining_price += $remaining_order_price["price"];

			$total_order_price = $model->getTotalOrderPrice($order["id"]);
			$total_orders_price += $total_order_price["price"];

		}

	}

}

// receipt type indicated in url
if(isset($action[3])) {
	$receipt_type = $action[3];
}



?>
<div class="scene shopReceipt i:scene">

<? if($order_no_list !== false && count($order_no_list) === count($orders)): ?>

	<h1>Thank you for supporting change</h1>
	<div class="order_info">

		<h2>
			Your orders are confirmed <br />
			<? if($total_remaining_price > 0): ?>
			– payment is being processed
			<? else: ?>
			– and paid
			<? endif; ?>
		</h2>

		<ul class="orders">
			<li>
				<h3>Orders:</h3>
				<ul class="orderitems">
				<? foreach($order_no_list as $order_no): ?>
					<li><?= $order_no ?></li>
				<? endforeach; ?>
				</ul>
			</li>
		</ul>


		<h3>
			<span class="name">Total</span>
			<span class="total_price">
				<?= formatPrice(["price" => $total_orders_price, "currency" => $order["currency"]]) ?>
			</span>
		</h3>

	</div>

	<?
	if($total_remaining_price === 0):

		foreach($orders as $order):

			$has_ticket = false;
			foreach($order["items"] as $order_item):

				$query = new Query();
				$sql = "SELECT * FROM ".SITE_DB.".user_item_tickets WHERE order_item_id = ".$order_item["id"];

				if($query->sql($sql)) {

					if(!$has_ticket): ?>
					<h2>Download your ticket(s)</h2>

					<ul class="ticketdownload">

					<? endif;
					$has_ticket = true;
					$tickets = $query->results();

					foreach($tickets as $key => $ticket): ?>
						<li class="ticket"><?= $order_item["name"] ?> - <a href="/download/<?= $ticket["item_id"] ?>/<?= $ticket["ticket_no"] ?>/<?= $ticket["ticket_no"] ?>.pdf"><?= $ticket["ticket_no"] ?><?= (count($tickets) > 1 ? " (".($key+1)."/".count($tickets).")" : "") ?></a></li>
					<? endforeach; ?>

					</ul>
				<? }
	
			endforeach;

		endforeach;

	endif;
	?>

	<? if($total_remaining_price > 0): ?>
	<div class="payment_information">

		<? if($receipt_type == "bank-transfer"): ?>
		<h2>Pay with Bank transfer</h2>

		<p>Make a payment to our bankaccount, using the information below:</p>

		<dl>
			<dt class="amount">Amount</dt>
			<dd class="amount"><?= formatPrice(["price" => $total_remaining_price, "currency" => $order["currency"]]) ?></dd>

			<dt class="recipient">Recipient</dt>
			<dd class="recipient">think.dk ApS</dd>

			<dt class="reference">Reference</dt>
			<dd class="reference"><?= $order_nos ?></dd>

			<dt class="bank">Bank</dt>
			<dd class="bank">Fælleskassen</dd>

			<dt class="account">Account no</dt>
			<dd class="account">8411 4145172</dd>

			<dt class="iban">IBAN</dt>
			<dd class="iban">DK3184110004145172</dd>

			<dt class="swift">SWIFT/BIC</dt>
			<dd class="swift">FAELDKK1</dd>
		</dl>


		<? elseif($receipt_type == "paypal"): ?>


		<h2>Pay with PayPal</h2>
		<p>
			Make a payment to our PayPal account, using the information below:
		</p>

		<dl>
			<dt class="amount">Amount</dt>
			<dd class="amount"><?= formatPrice(["price" => $total_remaining_price, "currency" => $order["currency"]]) ?></dd>

			<dt class="recipient">PayPal account</dt>
			<dd class="recipient">payment@think.dk</dd>

			<dt class="reference">Reference</dt>
			<dd class="reference"><?= $order_nos ?></dd>
		</dl>


		<? elseif($receipt_type == "mobilepay"): ?>


		<h2>Pay with MobilePay</h2>
		<p>
			Make a payment to our MobilePay account, using the information below:
		</p>

		<dl>
			<dt class="amount">Amount</dt>
			<dd class="amount"><?= formatPrice(["price" => $total_remaining_price, "currency" => $order["currency"]]) ?></dd>

			<dt class="recipient">MobilePay account</dt>
			<dd class="recipient">127888</dd>

			<dt class="reference">Reference</dt>
			<dd class="reference"><?= $order_nos ?></dd>
		</dl>


		<? elseif($receipt_type == "cash"): ?>


		<h2>Cash payment</h2>
		<p>Just bring <?= formatPrice(["price" => $total_remaining_price, "currency" => $order["currency"]]) ?> in cash next time you come to the Center.</p>


		<? endif; ?>

	</div>
	<? endif; ?>

<? elseif($user_id > 1):?>

	<h1>Thank you for supporting change.</h1>
	<p>We were not able to match any orders with this request. Please <a href="mailto:payment@think.dk?subject=Payment%20receipt%20error&body=Order%20No:%20<?= $order_nos ?>">contact us</a> to resolve the issue.</p>

<? else: ?>

	<h1>Looking for your receipt?</h1>
	<p>You must be <a href="/login?login_forward=<?= $this->url ?>">logged in</a> to your account to view receipts.</p>

<? endif; ?>


</div>