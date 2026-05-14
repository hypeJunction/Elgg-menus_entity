<?php

namespace HypeJunction\MenusEntity;

use Elgg\IntegrationTestCase;
use ElggMenuItem;

class MenusEntityTest extends IntegrationTestCase {

    /**
     * {@inheritdoc}
     */
    public function up() {
        // elgg-plugin.php registers the hook declaratively — no init call needed.
    }

    /**
     * {@inheritdoc}
     */
    public function down() {
        // Reset plugin settings between tests
        $plugin = elgg_get_plugin_from_id('menus_entity');
        if ($plugin) {
            $plugin->unsetSetting('primary_actions');
            $plugin->unsetSetting('remove_actions');
            $plugin->unsetSetting('icon');
        }
    }

    /**
     * Helper: set a plugin setting
     */
    protected function setSetting(string $name, string $value): void {
        $plugin = elgg_get_plugin_from_id('menus_entity');
        if ($plugin) {
            $plugin->setSetting($name, $value);
        }
    }

    /**
     * Helper to create a simple ElggMenuItem
     */
    protected function makeItem(string $name, string $href = '#', string $section = 'default'): ElggMenuItem {
        return ElggMenuItem::factory([
            'name' => $name,
            'href' => $href,
            'text' => ucfirst($name),
            'section' => $section,
        ]);
    }

    /**
     * Helper to trigger the hook and return resulting items
     *
     * @param ElggMenuItem[] $items
     * @return ElggMenuItem[]
     */
    protected function triggerHook(array $items): array {
        $entity = $this->createObject();

        $params = [
            'entity' => $entity,
        ];

        $result = elgg_trigger_event_results('register', 'menu:entity', $params, new \Elgg\Menu\MenuItems($items));

        if ($result instanceof \Elgg\Menu\MenuItems) {
            return iterator_to_array($result);
        }

        return (array) $result;
    }

    /**
     * Helper to find an item by name in the result array
     */
    protected function findItem(array $items, string $name): ?ElggMenuItem {
        foreach ($items as $item) {
            if ($item instanceof ElggMenuItem && $item->getName() === $name) {
                return $item;
            }
        }
        return null;
    }

    public function testHookRegistered() {
        // Trigger the hook with empty items on a plugin entity to confirm it is wired up.
        $plugin = elgg_get_plugin_from_id('menus_entity');
        $this->assertNotNull($plugin, 'menus_entity plugin should be registered');

        $result = elgg_trigger_event_results('register', 'menu:entity', ['entity' => $plugin], new \Elgg\Menu\MenuItems([]));
        $this->assertInstanceOf(\Elgg\Menu\MenuItems::class, $result);
    }

    public function testPrimaryActionsRemainVisible() {
        $this->setSetting('primary_actions', 'likes,access');
        $this->setSetting('remove_actions', '');

        $items = [
            $this->makeItem('likes'),
            $this->makeItem('access'),
            $this->makeItem('edit'),
        ];

        $result = $this->triggerHook($items);

        $likes = $this->findItem($result, 'likes');
        $access = $this->findItem($result, 'access');

        $this->assertNotNull($likes, 'likes item should remain in menu');
        $this->assertNotNull($access, 'access item should remain in menu');

        // Primary items should NOT have parent_name set to ellipsis
        $this->assertNotEquals('ellipsis', $likes->getParentName(), 'likes should not be under ellipsis');
        $this->assertNotEquals('ellipsis', $access->getParentName(), 'access should not be under ellipsis');
    }

    public function testRemoveActionsAreRemoved() {
        $this->setSetting('primary_actions', '');
        $this->setSetting('remove_actions', 'likes');

        $items = [
            $this->makeItem('likes'),
            $this->makeItem('edit'),
        ];

        $result = $this->triggerHook($items);

        $likes = $this->findItem($result, 'likes');
        $this->assertNull($likes, 'likes item should be removed from menu');

        // edit should still be present
        $edit = $this->findItem($result, 'edit');
        $this->assertNotNull($edit, 'edit item should still be present');
    }

    public function testNonPrimaryItemsMovedToEllipsis() {
        $this->setSetting('primary_actions', 'likes');
        $this->setSetting('remove_actions', '');

        $items = [
            $this->makeItem('likes'),
            $this->makeItem('edit'),
            $this->makeItem('report'),
        ];

        $result = $this->triggerHook($items);

        $edit = $this->findItem($result, 'edit');
        $report = $this->findItem($result, 'report');

        $this->assertNotNull($edit, 'edit should be in results');
        $this->assertNotNull($report, 'report should be in results');

        $this->assertEquals('ellipsis', $edit->getParentName(), 'edit should be moved under ellipsis');
        $this->assertEquals('ellipsis', $report->getParentName(), 'report should be moved under ellipsis');
    }

    public function testEditItemGetsSpecialStyling() {
        $this->setSetting('primary_actions', '');
        $this->setSetting('remove_actions', '');

        $items = [
            $this->makeItem('edit'),
        ];

        $result = $this->triggerHook($items);

        $edit = $this->findItem($result, 'edit');

        $this->assertNotNull($edit, 'edit item should exist');
        $this->assertEquals('pencil', $edit->getData('icon'), 'edit should have pencil icon');
        $this->assertEquals('admin', $edit->getData('subsection'), 'edit should be in admin subsection');
        $this->assertEquals('ellipsis', $edit->getParentName(), 'edit should be under ellipsis');
    }

    public function testDeleteItemGetsSpecialStyling() {
        $this->setSetting('primary_actions', '');
        $this->setSetting('remove_actions', '');

        $items = [
            $this->makeItem('delete'),
        ];

        $result = $this->triggerHook($items);

        $delete = $this->findItem($result, 'delete');

        $this->assertNotNull($delete, 'delete item should exist');
        $this->assertEquals('remove', $delete->getData('icon'), 'delete should have remove icon');
        $this->assertEquals('admin', $delete->getData('subsection'), 'delete should be in admin subsection');
        $this->assertEquals('ellipsis', $delete->getParentName(), 'delete should be under ellipsis');
    }

    public function testEllipsisMenuItemCreated() {
        $this->setSetting('primary_actions', 'likes');
        $this->setSetting('remove_actions', '');

        $items = [
            $this->makeItem('likes'),
            $this->makeItem('edit'),
        ];

        $result = $this->triggerHook($items);

        $ellipsis = $this->findItem($result, 'ellipsis');

        $this->assertNotNull($ellipsis, 'ellipsis parent item should be created');
        $this->assertEquals('#', $ellipsis->getHref(), 'ellipsis href should be #');
        $this->assertEquals(9999, $ellipsis->getPriority(), 'ellipsis priority should be 9999');
    }

    public function testEllipsisNotCreatedWhenNoDropdownItems() {
        // When all items are primary, no ellipsis should be added
        $this->setSetting('primary_actions', 'likes,access');
        $this->setSetting('remove_actions', '');

        $items = [
            $this->makeItem('likes'),
            $this->makeItem('access'),
        ];

        $result = $this->triggerHook($items);

        $ellipsis = $this->findItem($result, 'ellipsis');
        $this->assertNull($ellipsis, 'ellipsis should not be created when all items are primary');
    }

    public function testItemsWithoutHrefSkipped() {
        $this->setSetting('primary_actions', '');
        $this->setSetting('remove_actions', '');

        // Create an item without href (empty string = falsy)
        $noHrefItem = ElggMenuItem::factory([
            'name' => 'separator',
            'href' => false,
            'text' => 'Separator',
        ]);

        $items = [
            $noHrefItem,
            $this->makeItem('edit'),
        ];

        $result = $this->triggerHook($items);

        $separator = $this->findItem($result, 'separator');
        $this->assertNotNull($separator, 'no-href item should remain in menu');
        $this->assertNotEquals('ellipsis', $separator->getParentName(), 'no-href item should not be moved under ellipsis');
    }

    public function testCustomIconSetting() {
        $this->setSetting('primary_actions', 'likes');
        $this->setSetting('remove_actions', '');
        $this->setSetting('icon', 'cog');

        $items = [
            $this->makeItem('likes'),
            $this->makeItem('edit'),
        ];

        $result = $this->triggerHook($items);

        $ellipsis = $this->findItem($result, 'ellipsis');
        $this->assertNotNull($ellipsis, 'ellipsis item should be created');

        // The icon is rendered via elgg_view_icon, so we check the text contains the icon name
        $text = $ellipsis->getText();
        $this->assertStringContainsString('cog', $text, 'ellipsis should use custom icon from settings');
    }

    public function testSettingsViewRenders() {
        $plugin = elgg_get_plugin_from_id('menus_entity');
        if (!$plugin) {
            $this->markTestSkipped('menus_entity plugin not found');
        }

        $output = elgg_view('plugins/menus_entity/settings', [
            'entity' => $plugin,
        ]);

        $this->assertNotEmpty($output, 'Settings view should produce output');
        $this->assertStringContainsString('params[primary_actions]', $output, 'Settings should contain primary_actions field');
        $this->assertStringContainsString('params[remove_actions]', $output, 'Settings should contain remove_actions field');
        $this->assertStringContainsString('params[icon]', $output, 'Settings should contain icon field');
    }

    public function testNonPrimaryItemsSectionResetToDefault() {
        $this->setSetting('primary_actions', '');
        $this->setSetting('remove_actions', '');

        $item = $this->makeItem('report', '#', 'extras');

        $items = [$item];

        $result = $this->triggerHook($items);

        $report = $this->findItem($result, 'report');
        $this->assertNotNull($report);
        $this->assertEquals('default', $report->getSection(), 'Non-primary items should have section reset to default');
        $this->assertEquals('extras', $report->getData('subsection'), 'Original section should be stored as subsection');
    }
}
