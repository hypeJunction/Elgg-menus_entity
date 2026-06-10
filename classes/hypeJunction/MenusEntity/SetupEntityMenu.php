<?php

namespace hypeJunction\MenusEntity;

use Elgg\Event;
use Elgg\Menu\MenuItems;
use ElggMenuItem;

/**
 * SetupEntityMenu class.
 */
class SetupEntityMenu {

	/**
	 * Reorganize entity menu into primary items and an ellipsis dropdown.
	 *
	 * In Elgg 7.x the menu event value is an \Elgg\Menu\MenuItems collection
	 * (ArrayAccess + IteratorAggregate), NOT a plain PHP array. We operate on
	 * the collection directly and always return a MenuItems instance so that
	 * downstream core handlers (e.g. Elgg\Menus\Entity::registerTrash, which
	 * calls $return->remove('delete')) never receive a raw array.
	 *
	 * @param Event $hook "register","menu:entity"
	 *
	 * @return MenuItems
	 */
	public function __invoke(Event $hook): MenuItems {

		$return = $hook->getValue();

		// Defensively normalise to a MenuItems collection. The event value is a
		// MenuItems collection in Elgg 7.x, but an earlier handler might have
		// returned a raw array; rebuild a collection so collection methods work.
		if (!$return instanceof MenuItems) {
			$items = $return instanceof \Traversable ? iterator_to_array($return) : (array) $return;
			$return = new MenuItems(array_values(array_filter($items, static function ($item) {
				return $item instanceof ElggMenuItem;
			})));
		}

		$setting_primary = \elgg_get_plugin_setting('primary_actions', 'menus_entity', '');
		$primary_actions = \elgg_string_to_array($setting_primary);

		$setting_remove = \elgg_get_plugin_setting('remove_actions', 'menus_entity', '');
		$remove_actions = \elgg_string_to_array($setting_remove);

		$ellipsis = false;
		$remove = [];

		// Walk the collection by reference; mutate items in place and collect
		// the names of items that should be removed (cannot unset during walk).
		$return->walk(function ($item) use ($primary_actions, $remove_actions, &$ellipsis, &$remove) {
			if (!$item instanceof ElggMenuItem) {
				return;
			}

			if (in_array($item->getName(), $remove_actions)) {
				$remove[] = $item->getID();
				return;
			}

			if (in_array($item->getName(), $primary_actions) || !$item->getHref()) {
				return;
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
		});

		foreach ($remove as $id) {
			$return->remove($id);
		}

		if ($ellipsis) {
			$icon = \elgg_get_plugin_setting('icon', 'menus_entity');
			if (!$icon) {
				$icon = 'ellipsis-v';
			}

			$return[] = ElggMenuItem::factory([
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

		return $return;
	}
}
