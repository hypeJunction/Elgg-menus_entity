<?php

namespace hypeJunction\MenusEntity;

use Elgg\Hook;
use ElggMenuItem;

class SetupEntityMenu {

	/**
	 * Reorganize entity menu into primary items and an ellipsis dropdown.
	 *
	 * @param Hook $hook "register","menu:entity"
	 *
	 * @return \Elgg\Menu\MenuItems|ElggMenuItem[]|null
	 */
	public function __invoke(Hook $hook) {

		$return = $hook->getValue();

		$setting_primary = \elgg_get_plugin_setting('primary_actions', 'menus_entity', '');
		$primary_actions = \elgg_string_to_array($setting_primary);

		$setting_remove = \elgg_get_plugin_setting('remove_actions', 'menus_entity', '');
		$remove_actions = \elgg_string_to_array($setting_remove);

		$ellipsis = false;

		// Convert to array for by-reference iteration
		$items = $return instanceof \Traversable ? iterator_to_array($return) : (array) $return;

		foreach ($items as $key => $item) {
			if (!$item instanceof ElggMenuItem) {
				continue;
			}

			if (in_array($item->getName(), $remove_actions)) {
				unset($items[$key]);
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
				case 'edit':
					$item->setText(\elgg_echo('edit'));
					$item->setData('icon', 'pencil');
					$item->setData('subsection', 'admin');
					break;

				case 'delete':
					$item->setText(\elgg_echo('delete'));
					$item->setData('icon', 'remove');
					$item->setData('subsection', 'admin');
					break;
			}
		}

		if ($ellipsis) {
			$icon = \elgg_get_plugin_setting('icon', 'menus_entity');
			if (!$icon) {
				$icon = 'ellipsis-v';
			}
$items[] = ElggMenuItem::factory([
				'name' => 'ellipsis',
				'href' => '#',
				'text' => \elgg_view_icon($icon),
				'item_class' => 'elgg-menu-item-has-dropdown',
				'data-my' => 'right top',
				'data-at' => 'right bottom+5px',
				'priority' => 9999,
				'section' => 'default',
			]);
		}

		$items = array_values(array_filter($items));

		if ($return instanceof \Elgg\Menu\MenuItems) {
			return new \Elgg\Menu\MenuItems($items);
		}

		return $items;
	}
}
