<?php

if (is_null(elgg_get_plugin_setting('primary_actions', 'menus_entity'))) {
	$items = array(
		'access',
		'likes',
		'unlike',
		'likes_count',
		'published_status',
		'membership',
		'members',
	);
	elgg_set_plugin_setting('primary_actions', implode(',', $items), 'menus_entity');
}

if (is_null(elgg_get_plugin_setting('icon', 'menus_entity'))) {
	elgg_set_plugin_setting('icon', 'ellipsis-v', 'menus_entity');
}