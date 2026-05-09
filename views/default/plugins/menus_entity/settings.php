<?php

$entity = elgg_extract('entity', $vars);
?>

<div>
	<label><?php echo elgg_echo('menus:entity:settings:primary_actions') ?></label>
	<?php
	echo elgg_view('input/text', [
		'name' => 'params[primary_actions]',
		'value' => $entity->primary_actions,
	]);
	?>
</div>
<div>
	<label><?php echo elgg_echo('menus:entity:settings:remove_actions') ?></label>
	<?php
	echo elgg_view('input/text', [
		'name' => 'params[remove_actions]',
		'value' => $entity->remove_actions,
	]);
	?>
</div>
<div>
	<label><?php echo elgg_echo('menus:entity:settings:icon') ?></label>
	<?php
	echo elgg_view('input/text', [
		'name' => 'params[icon]',
		'value' => $entity->icon,
	]);
	?>
</div>
