<?php
global $action;
global $model;

$this->pageTitle("Cart");

$user_id = session()->value("user_id");


// if cart reference was passed to cart controller

if(count($action) > 1) {
	session()->value("cart_reference", $action[1]);
}

$cart = $model->getCart();
if($cart) {
	$total_cart_price = $model->getTotalCartPrice($cart["id"]);
}

?>
<div class="scene cart i:cart">
	<h1><?= translate("Your cart") ?></h1>


	<?= HTML()->renderSnippet("snippets/messages.php") ?>


	<div class="all_items">
		<h2>Cart contents</h2>
<?		if($cart && $cart["items"]): ?>
		<ul class="items">
<?			foreach($cart["items"] as $cart_item):
				$item = items()->getItem([
					"id" => $cart_item["item_id"], 
					"extend" => true
				]);
				$price = $model->getCartItemPrice($cart_item, $cart);
			?>
			<li class="item id:<?= $item["id"] ?>">
				<h3>

					<?= $model->formStart("/shop/updateCartItemQuantity/".$cart["cart_reference"]."/".$cart_item["id"], ["class" => "updateCartItemQuantity labelstyle:inject"]) ?>
						<fieldset>
							<?= $model->input("quantity", [
								"type" => "integer",
								"min" => 1,
								"value" =>  $cart_item["quantity"],
								"hint_message" => "State the quantity of this item"
							]) ?>
						</fieldset>
					<?= $model->formEnd() ?>

					<span class="x">x </span>
					<span class="name"><?= $item["name"] ?> </span>
					<span class="a">á </span>
					<span class="unit_price">
						<?= formatPrice([
							"price" => $price["cart_price"], 
							"vat" => $price["cart_vat"], 
							"currency" => $cart["currency"], 
							"country" => $cart["country"]
						]) ?>
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

				<ul class="actions">
					<?= $HTML->oneButtonForm("Delete", "/shop/deleteFromCart/".$cart["cart_reference"]."/".$cart_item["id"], [
						"wrapper" => "li.delete",
						"static" => true
					]) ?>
				</ul>
			</li>
<?			endforeach; ?>

			<li class="shipping">
				<p>
					<span class="name">Shipping</span>
					<span class="total_price">
<?				if(1): ?>
						<a href="/shop/checkout">Checkout</a> to  enter adress and calculate shipping</span>
<?				else: ?>
						state shipping price
<?				endif; ?>
				</p>
			</li>
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
			$product_controller = module()->getMainControllerPath("productcollection");
			if(!$product_controller) {
				$product_controller = module()->getMainControllerPath("product");
			}
			if($product_controller): ?>
			<br />Check out our <a href="<?= $product_controller ?>">products</a> now.
<?			endif; ?>
		</p>

		<ul class="items">
			<li class="total">
				<h3>
					<span class="name">Total</span>
					<span class="total_price">
						<?= formatPrice([
							"price" => 0, 
							"currency" => $this->currency()
						]) ?>
					</span>
				</h3>
			</li>
		</ul>
<?		endif; ?>
	</div>

<? if($cart && $cart["items"]) :?>
	<div class="checkout">
		<h2>Continue to checkout</h2>
		<p>When you are ready, click <em>Checkout</em> to proceed.</p>

		<ul class="actions">
			<?= HTML()->link("Checkout", "/shop/checkout", [
				"class" => "button primary",
				"wrapper" => "li.continue",
			]) ?>
		</ul>
	</div>
<? endif; ?>
</div>
