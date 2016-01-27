<?php

/**
 * Dropdown Menu
 *
 * @author Ismayil Khayredinov <info@hypejunction.com>
 * @copyright Copyright (c) 2015, Ismayil Khayredinov
 */
require_once __DIR__ . '/autoloader.php';

elgg_register_event_handler('init', 'system', 'menus_entity_init');

/**
 * Initialize the plugin
 * @return void
 */
function menus_entity_init() {

	elgg_register_plugin_hook_handler('register', 'menu:entity', 'menus_entity_setup', 999);
}

/**
 * Reorganize entity menu
 *
 * @param string         $hook   "register"
 * @param string         $type   "menu:entity"
 * @param ElggMenuItem[] $return Menu
 * @param array          $params Hook params
 * @return ElggMenuItem[]
 */
function menus_entity_setup($hook, $type, $return, $params) {

	$setting_primary = elgg_get_plugin_setting('primary_actions', 'menus_entity', '');
	$primary_actions = string_to_tag_array($setting_primary);

	$setting_remove = elgg_get_plugin_setting('remove_actions', 'menus_entity', '');
	$remove_actions = string_to_tag_array($setting_remove);
	
	$ellipsis = false;
	
	foreach ($return as &$item) {
		if (!$item instanceof ElggMenuItem) {
			continue;
		}

		if (in_array($item->getName(), $remove_actions)) {
			$item = null;
			continue;
		}
		
		if (in_array($item->getName(), $primary_actions) || !$item->getHref()) {
			continue;
		}


		$ellipsis = true;
		$item->setParentName('ellipsis');
		
		// combine all menus into one section
		// subsection data is used by menus_api, if enabled
		$item->setData('subsection', $item->getSection());
		$item->setSection('default');

		switch ($item->getName()) {
			case 'edit' :
				$item->setText(elgg_echo('edit'));
				$item->setData('icon', 'pencil');
				$item->setData('subsection', 'admin');
				break;

			case 'delete' :
				$item->setText(elgg_echo('delete'));
				$item->setData('icon', 'remove');
				$item->setData('subsection', 'admin');
				break;
		}
	}

	if ($ellipsis) {
		$icon = elgg_get_plugin_setting('icon', 'menus_entity');
		if (!$icon) {
			$icon = 'ellipsis-v';
		}
		$return[] = ElggMenuItem::factory(array(
			'name' => 'ellipsis',
			'href' => '#',
			'text' => elgg_view_icon($icon),
			'item_class' => 'elgg-menu-item-has-dropdown',
			'data-my' => 'right top',
			'data-at' => 'right bottom+5px',
			'priority' => 9999,
			'section' => 'default',
		));
	}

	return array_filter($return);
}
