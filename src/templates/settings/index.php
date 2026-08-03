<?php
global $module_group_id;
global $module_id;

$module = module()->getModule($module_group_id, $module_id);
$controller = module()->getMainControllerPath($module_id);

?>
<div class="scene module i:module item-product i:itemProduct">
	<h1>The Simplest Shop</h1>
	<h2>Configuration</h2>

	<?= HTML()->renderSnippet("snippets/modules/actions-back.php") ?>

	<h3>Module description</h3>
	<?= HTML()->renderSnippet("snippets/modules/panel-info.php", [
		"module" => $module,
	]) ?>
	<?= HTML()->renderSnippet("snippets/modules/panel-version.php", [
		"module" => $module,
	]) ?>




	<?= HTML()->renderSnippet("snippets/modules/panel-upgrade.php", [
		"module" => $module,
	]) ?>
	<?= $HTML->renderSnippet("snippets/modules/panel-uninstall.php",  [
		"module" => $module,
	]) ?>

</div>
