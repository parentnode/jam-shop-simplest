<?php
global $action;
global $model;
$UC = new User();

$this->pageTitle("Checkout");


// get current user id
$user_id = session()->value("user_id");


// get current cart
$cart = $model->getCart();
// debug([$cart]);

$membership = false;
// attempt to find membership in cart
if($cart && $cart["items"]) {

	foreach($cart["items"] as $cart_item) {

		$item = items()->getItem([
			"id" => $cart_item["item_id"], 
			"extend" => true
		]);

	}

}

// User is logged in
if($user_id != 1) {


	// update user on cart
	$_POST["user_id"] = $user_id;
	$model->updateCart(["updateCart"]);
	$cart = $model->getCart();


	$user = $UC->getUser();


	// Only get payment methods if cart has items
	if($cart && $cart["items"]) {

		// Get the total cart price
		$total_cart_price = $model->getTotalCartPrice($cart["id"]);

		if($total_cart_price && $total_cart_price["price"] > 0) {

			// Get payment methods
			$payment_methods = $this->paymentMethods();

			// Get payment methods
			$user_payment_methods = $UC->getPaymentMethods(["extend" => true]);

		}

	}
	else {
		
	}


	// Get address info
	$delivery_address = $UC->getAddresses(["address_id" => $cart["delivery_address_id"]]);
	$billing_address = $UC->getAddresses(["address_id" => $cart["billing_address_id"]]);

}

// User not logged in yet
else {

	// enable re-population of fields
	$username = stringOr(getPost("username"));
	$firstname = stringOr(getPost("firstname"));
	$lastname = stringOr(getPost("lastname"));
	$email = stringOr(getPost("email"));
	$confirm_email = stringOr(getPost("confirm_email"));

	$mobile = stringOr(getPost("mobile"));
	$terms = stringOr(getPost("terms"));
	$maillist = stringOr(getPost("maillist"));

	$address_name = stringOr(getPost("address_name"));
	$address1 = stringOr(getPost("address1"));
	$postal = stringOr(getPost("postal"));
	$city = stringOr(getPost("city"));

	$state = stringOr(getPost("state"));
	$country = stringOr(getPost("country"));

}

// debug([$user_id, $cart, $membership]);

?>
<div class="scene checkout i:<?= $user_id == 1 ? "checkoutSignup" : "checkout" ?>">
	<h1>Checkout</h1>


	<?= HTML()->renderSnippet("snippets/messages.php") ?>


<?
	// User is not logged in yet
	if($user_id == 1):
?>


	<div class="login">
		<h2>Login</h2>
		<p>If you already have an account, then log in now.</p>
		<?= $UC->formStart("/shop/checkout?login=true", ["class" => "login labelstyle:inject"]) ?>
			<?= $UC->input("login_forward", ["type" => "hidden", "value" => "/shop/checkout"]); ?>
			<fieldset>
				<?= $UC->input("username", ["type" => "string", "label" => "Email or mobile number", "required" => true, "value" => $username, "pattern" => "[\w\.\-_]+@[\w\-\.]+\.\w{2,10}|([\+0-9\-\.\s\(\)]){5,18}", "hint_message" => "You can log in using either your email or mobile number.", "error_message" => "You entered an invalid email or mobile number."]); ?>
				<?= $UC->input("password", ["type" => "password", "label" => "Password", "required" => true, "hint_message" => "Type your password", "error_message" => "Your password should be between 8-20 characters."]); ?>
			</fieldset>

			<ul class="actions">
				<?= $UC->submit("Log in", ["class" => "primary", "wrapper" => "li.login"]) ?>
				<li class="forgot">Did you <a href="/login/forgot" target="_blank">forget your password</a>?</li>
			</ul>
		<?= $UC->formEnd() ?>
	</div>

<?		if($cart && $cart["items"]): ?>
	
	<div class="signup">
		<h2>Or enter your details</h2>
		<p>This will create an account so you can continue checkout.</h2>
		<?= $UC->formStart("/shop/register", ["class" => "signup labelstyle:inject"]) ?>

			<h3>Contact info</h3>
			<fieldset class="names">
				<?= $UC->input("firstname", ["value" => $firstname, "required" => true, "class" => "firstname"]); ?>
				<?= $UC->input("lastname", ["value" => $lastname, "required" => true, "class" => "lastname"]); ?>
			</fieldset>

			<fieldset class="emails">
				<?= $UC->input("email", ["value" => $email, "required" => true, "value" => $email, "hint_message" => "Write your email.", "error_message" => "You entered an invalid email.", "class" => "email"]); ?>
				<?= $UC->input("confirm_email", ["value" => $confirm_email, "required" => true, "value" => $email, "hint_message" => "Write your email.", "error_message" => "You entered an invalid email.", "class" => "confirm_email"]); ?>
			</fieldset>

			<fieldset class="mobile_password">
				<?= $UC->input("mobile", ["value" => $mobile, "class" => "mobile"]); ?>
				<?= $UC->input("password", ["hint_message" => "Choose a password - or leave it blank and we'll generate one for you.", "error_message" => "Your password must be between 8 and 20 characters.", "class" => "password"]); ?>
			</fieldset>

			<h3>Delivery address</h3>
			<fieldset>
				<?= $UC->input("address_label", ["value" => "Home", "type" => "hidden"]) ?>
				<?= $UC->input("address1", ["value" => $address1]) ?>
			</fieldset>
			<fieldset class="postal_city">
				<?= $UC->input("postal", ["value" => $postal, "class" => "postal"]) ?>
				<?= $UC->input("city", ["value" => $city, "class" => "city"]) ?>
			</fieldset>
			<fieldset>
				<?= $UC->input("state", ["value" => $state]) ?>
				<?= $UC->input("country", [
					"type" => "select",
					"options" => $UC->toOptions($this->countries(), "id", "name", ["add" => ["" => "Country"]]),
					"value" => $country
				]) ?>
			</fieldset>

			<h3>Terms and newsletter</h3>
			<fieldset>
				<?= $UC->input("terms", ["value" => $terms]); ?>
				<?= $UC->input("maillist_name", ["type" => "hidden", "value" => "General"]); ?>
				<?= $UC->input("maillist", ["type" => "checkbox", "value" => $maillist, "label" => "Yes, I want to receive the newsletter."]); ?>
			</fieldset>

			<ul class="actions">
				<?= $UC->submit("Continue", ["class" => "primary", "wrapper" => "li.signup"]) ?>
			</ul>
		<?= $model->formEnd() ?>

	</div>

	<div class="why_account">
		<h3>Why do I need an account?</h3>
		<p>
			As a customer, an account is a natural extension of your purchase. It is where your information is stored during the processing of your order.
			The account will also give you access to follow your order on this site.
		</p>
		<p>
			When we receive payments we are legally required to store your information for 5 years – for accounting documentation and 
			as a means to prevent whitewashing and other types of fraud.
			For your convenience we officially associate this information with an account – to enable full transparency
			about what data is stored on our site. If this information is not associated with an account, it will still be stored – only out of your reach.
		</p>
		<p>
			Whether you use the account or not is up to you. You can cancel your account anytime you like and we will anonymize as much of 
			your profile data as legally possible. Also it is perhaps worth noting that there is no 
			newsletter associated with the account, unless you opt into that seperately.
		</p>
	</div>

<?		else: ?>

	<div class="emptycart">
		<h2>Your cart is empty</h2>
<?
		$product_controller = module()->getMainControllerPath("product");
		if($product_controller): ?>
		<p>Check out our <a href="<?= $product_controller ?>">products</a>.</p>
<?			endif; ?>
	</div>

<?		endif; ?>


<?
	// user is already logged in, show checkout overview
	else:
?>


	<div class="contact">
		<h2>Your details <a href="/shop/profile">(Edit)</a></h2>
		<dl class="list">
			<dt>Full name</dt>
			<dd><?= $user["firstname"] ?> <?= $user["lastname"] ?></dd>
			<dt>Email</dt>
			<dd><?= $user["email"] ?></dd>
			<dt>Mobile</dt>
			<dd><?= $user["mobile"] ?></dd>
		</dl>
	</div>

	<div class="delivery">
		<h2>Delivery address <a href="/shop/address/delivery">(Edit)</a></h2>

<?		if($cart["delivery_address_id"]): ?>
		<dl class="list">
			<dt>Name</dt>
			<dd><?= $delivery_address["address_name"] ?></dd>
<?			if($delivery_address["att"]): ?>
			<dt>Att</dt>
			<dd><?= $delivery_address["att"] ?></dd>
<?			endif; ?>
			<dt>Address</dt>
			<dd><?= $delivery_address["address1"] ?></dd>
<?			if($delivery_address["address2"]): ?>
			<dt></dt>
			<dd><?= $delivery_address["address2"] ?></dd>
<?			endif; ?>
			<dt>Postal and city</dt>
			<dd><?= $delivery_address["postal"] ?> <?= $delivery_address["city"] ?></dd>
<?			if($delivery_address["state"]): ?>
			<dt>State</dt>
			<dd><?= $delivery_address["state"] ?></dd>
<?			endif; ?>
			<dt>Country</dt>
			<dd><?= $delivery_address["country"] ?></dd>
		</dl>

<?		else: ?>

		<p>You must <a href="/shop/address/delivery">add a delivery address</a> to continue.</p>

		<ul class="actions">
			<?= HTML()->link("Add delivery address", "/shop/address/delivery", [
				"class" => "button primary",
				"wrapper" => "li.continue",
			]) ?>
		</ul>

<?		endif; ?>
	</div>

	<div class="all_items">
		<h2>Cart contents <a href="/shop/cart">(Edit)</a></h2>
<?		if($cart && $cart["items"]): ?>
		<ul class="items">
<?
			foreach($cart["items"] as $cart_item):

				$item = items()->getItem([
					"id" => $cart_item["item_id"], 
					"extend" => true
				]);
				$price = $model->getCartItemPrice($cart_item, $cart);
?>
			<li class="item id:<?= $item["id"] ?>">
				<h3>
					<span class="quantity"><?= $cart_item["quantity"] ?></span>
					<span class="x">x </span>
					<span class="name"><?= $item["name"] ?> </span>
					<span class="a">á </span>
					<span class="unit_price">
						<?= formatPrice([
								"price" => $price["cart_price"], 
								"vat" => $price["cart_vat"], 
								"currency" => $cart["currency"], 
								"country" => $cart["country"]
							], 
							["vat" => false]
						) ?>
					</span>
					<span class="total_price">
						<?= formatPrice([
								"price" => $price["cart_price"]*$cart_item["quantity"], 
								"vat" => $price["cart_vat"]*$cart_item["quantity"], 
								"currency" => $cart["currency"], 
								"country" => $cart["country"]
							], 
							["vat" => false]
						) ?>
					</span>
				</h3>
			</li>

<?			endforeach; ?>

			<li class="total">
				<p>
					<span class="name">Hereof Moms</span>
					<span class="total_vat">
						(<?= formatPrice([
							"price" => $total_cart_price["vat"], 
							"currency" => $total_cart_price["currency"]
						]) ?>)
					</span>
				</p>
				<h3>
					<span class="name">Total</span>
					<span class="total_price">
						<?= formatPrice($total_cart_price, ["vat" => false]) ?>
					</span>
				</h3>
			</li>
		</ul>

<?		else: ?>
		<p>
			You don't have any items in your cart yet.
<?
			$product_controller = module()->getMainControllerPath("product");
			if($product_controller): ?>
			<br />Check out our <a href="<?= $product_controller ?>">products</a> now.
<?			endif; ?>
		</p>

<?		endif; ?>
	</div>


<? 
		// Only show payment options if cart has items
		if($cart && $cart["items"] && $cart["delivery_address_id"] && $total_cart_price && $total_cart_price["price"] !== 0):
?>
	<div class="payment_method">
		<h2>Choose a payment method</h2>

<?			if($user_payment_methods): ?>

			<h3>Your payment methods</h3>
			<p>Choose one of your existing payment methods to continue processing this order.</p>
			<ul class="payment_methods">

<?
				foreach($user_payment_methods as $user_payment_method):

					if($user_payment_method && $user_payment_method["cards"]):

						foreach($user_payment_method["cards"] as $card):
?>
				<li class="payment_method user_payment_method<?= $user_payment_method["classname"] ? " ".$user_payment_method["classname"] : "" ?>">
					<ul class="actions">
						<?= $HTML->oneButtonForm(
						"Confirm order and pay with card ending in " . $card["last4"], 
						"/shop/confirmCartAndSelectUserPaymentMethod", [
							"inputs" => [
								"cart_id" => $cart["id"], 
								"user_payment_method_id" => $user_payment_method["id"], 
								"payment_method_id" => $user_payment_method["payment_method_id"],
								"gateway_payment_method_id" => $card["id"]
							],
							"confirm-value" => false,
							"wait-value" => "Please wait",
							"dom-submit" => true,
							"class" => "primary",
							"name" => "continue",
							"wrapper" => "li.continue.".$user_payment_method["classname"],
						]) ?>
					</ul>
					<p><?= $user_payment_method["description"] ?></p>
				</li>
<?
						endforeach;

					else:
?>
				<li class="payment_method user_payment_method<?= $user_payment_method["classname"] ? " ".$user_payment_method["classname"] : "" ?>">
					<ul class="actions">
						<?= $HTML->oneButtonForm(
						"Confirm order and pay", 
						"/shop/confirmCartAndSelectUserPaymentMethod",
						[
							"inputs" => [
								"cart_id" => $cart["id"], 
								"user_payment_method_id" => $user_payment_method["id"], 
								"payment_method_id" => $user_payment_method["payment_method_id"]
							],
							"confirm-value" => false,
							"wait-value" => "Please wait",
							"dom-submit" => true,
							"class" => "primary",
							"name" => "continue",
							"wrapper" => "li.continue.".$user_payment_method["classname"],
						]) ?>
					</ul>
					<p><?= $user_payment_method["description"] ?></p>
				</li>
<?
					endif;
				endforeach;
?>
			</ul>
<?			
			endif;


			if($payment_methods):
?>
			<h3>Our <?= $user_payment_methods ? "other " : "" ?>payment options</h3>
			<p><?= $user_payment_methods ? "Or, p" : "P" ?>lease choose a payment method to continue processing this order.</p>
			<ul class="payment_methods">
<?
				foreach($payment_methods as $payment_method):
					if($payment_method["state"] === "public"):
?>
				<li class="payment_method<?= $payment_method["classname"] ? " ".$payment_method["classname"] : "" ?>">

					<ul class="actions">
						<?= $HTML->oneButtonForm(
						"Confirm order and pay", 
						"/shop/confirmCartAndSelectPaymentMethod", 
						[
							"inputs" => [
								"cart_id" => $cart["id"], 
								"payment_method_id" => $payment_method["id"]
							],
							"confirm-value" => false,
							"wait-value" => "Please wait",
							"dom-submit" => true,
							"class" => "primary",
							"name" => "continue",
							"wrapper" => "li.continue.".$payment_method["classname"],
						]) ?>
					</ul>
					<p><?= $payment_method["description"] ?></p>

				</li>
<?
					endif;
				endforeach;
?>
			</ul>
<?			endif; ?>
	</div>
<?		endif; ?>


<?	endif; ?>

</div>
