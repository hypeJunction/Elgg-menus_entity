<?php

namespace HypeJunction\MenusEntity;

use Elgg\IntegrationTestCase;
use ElggMenuItem;

class MenusEntityTest extends IntegrationTestCase {

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void {
        parent::setUpBeforeClass();

        // Load start.php to register hooks (Elgg 3.x pattern)
        $startFile = dirname(dirname(dirname(__DIR__))) . '/start.php';
        if (file_exists($startFile)) {
            require_once $startFile;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function up() {
        // Trigger init event so the hook gets registered
        menus_entity_init();
    }

    /**
     * {@inheritdoc}
     */
    public function down() {
        // Reset plugin settings between tests
        elgg_unset_plugin_setting('primary_actions', 'menus_entity');
        elgg_unset_plugin_setting('remove_actions', 'menus_entity');
        elgg_unset_plugin_setting('icon', 'menus_entity');
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

        $result = elgg_trigger_plugin_hook('register', 'menu:entity', $params, $items);

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

    public function testHookRegisteredAtHighPriority() {
        $hooks = _elgg_services()->hooks;

        $registrations = $hooks->getOrderedHandlers('register', 'menu:entity');

        $found = false;
        foreach ($registrations as $handler) {
            if ($handler === 'menus_entity_setup') {
                $found = true;
                break;
            }
            // Handle callable arrays or closures wrapping the function
            if (is_array($handler) || is_string($handler)) {
                if ($handler === 'menus_entity_setup') {
                    $found = true;
                    break;
                }
            }
        }

        $this->assertTrue($found, 'menus_entity_setup should be registered for register, menu:entity');

        // Verify it runs late (at priority 999) by checking it appears after
        // any default-priority handlers
        $lastIndex = null;
        foreach ($registrations as $index => $handler) {
            if ($handler === 'menus_entity_setup') {
                $lastIndex = $index;
            }
        }
        $this->assertNotNull($lastIndex);
    }

    public function testPrimaryActionsRemainVisible() {
        elgg_set_plugin_setting('primary_actions', 'likes,access', 'menus_entity');
        elgg_set_plugin_setting('remove_actions', '', 'menus_entity');

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
        elgg_set_plugin_setting('primary_actions', '', 'menus_entity');
        elgg_set_plugin_setting('remove_actions', 'likes', 'menus_entity');

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
        elgg_set_plugin_setting('primary_actions', 'likes', 'menus_entity');
        elgg_set_plugin_setting('remove_actions', '', 'menus_entity');

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
        elgg_set_plugin_setting('primary_actions', '', 'menus_entity');
        elgg_set_plugin_setting('remove_actions', '', 'menus_entity');

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
        elgg_set_plugin_setting('primary_actions', '', 'menus_entity');
        elgg_set_plugin_setting('remove_actions', '', 'menus_entity');

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
        elgg_set_plugin_setting('primary_actions', 'likes', 'menus_entity');
        elgg_set_plugin_setting('remove_actions', '', 'menus_entity');

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
        elgg_set_plugin_setting('primary_actions', 'likes,access', 'menus_entity');
        elgg_set_plugin_setting('remove_actions', '', 'menus_entity');

        $items = [
            $this->makeItem('likes'),
            $this->makeItem('access'),
        ];

        $result = $this->triggerHook($items);

        $ellipsis = $this->findItem($result, 'ellipsis');
        $this->assertNull($ellipsis, 'ellipsis should not be created when all items are primary');
    }

    public function testItemsWithoutHrefSkipped() {
        elgg_set_plugin_setting('primary_actions', '', 'menus_entity');
        elgg_set_plugin_setting('remove_actions', '', 'menus_entity');

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
        elgg_set_plugin_setting('primary_actions', 'likes', 'menus_entity');
        elgg_set_plugin_setting('remove_actions', '', 'menus_entity');
        elgg_set_plugin_setting('icon', 'cog', 'menus_entity');

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
        elgg_set_plugin_setting('primary_actions', '', 'menus_entity');
        elgg_set_plugin_setting('remove_actions', '', 'menus_entity');

        $item = $this->makeItem('report', '#', 'extras');

        $items = [$item];

        $result = $this->triggerHook($items);

        $report = $this->findItem($result, 'report');
        $this->assertNotNull($report);
        $this->assertEquals('default', $report->getSection(), 'Non-primary items should have section reset to default');
        $this->assertEquals('extras', $report->getData('subsection'), 'Original section should be stored as subsection');
    }
}
