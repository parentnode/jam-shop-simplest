<?php
global $action;
global $model;
?>
<div class="scene shopReceipt i:scene">

	<h1>Payment error</h1>
	<h2>Sorry</h2>

	<? if(message()->hasMessages()): ?>
	<?= $HTML->serverMessages() ?>
	<? else: ?>
	<p>We failed to process your payment request. Please try again or <a href="mailto:payment@think.dk?subject=Payment%20error">contact us</a> to resolve the issue.</p>
	<? endif; ?>

</div>