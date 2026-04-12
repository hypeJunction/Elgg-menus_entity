<?php

namespace HypeJunction\MenusEntity;

use Elgg\UnitTestCase;
use hypeJunction\MenusEntity\SetupEntityMenu;

/**
 * Structural unit tests for SetupEntityMenu.
 *
 * Behavioural coverage lives in the integration suite because the handler
 * calls elgg_get_plugin_setting and elgg_view_icon, which require the full
 * Elgg bootstrap. Unit tests only verify class shape.
 */
class SetupEntityMenuUnitTest extends UnitTestCase {

	public function up() {
	}

	public function down() {
	}

	public function testHandlerIsInvokable() {
		$handler = new SetupEntityMenu();
		$this->assertTrue(is_callable($handler), 'SetupEntityMenu must be invokable');
	}

	public function testInvokeAcceptsElggHook() {
		$reflection = new \ReflectionMethod(SetupEntityMenu::class, '__invoke');
		$params = $reflection->getParameters();
		$this->assertCount(1, $params, '__invoke must take exactly one argument');

		$type = $params[0]->getType();
		$this->assertNotNull($type, '__invoke argument must be type-hinted');
		$this->assertSame('Elgg\\Hook', ltrim((string) $type, '?'));
	}

	public function testBootstrapClassExists() {
		$this->assertTrue(class_exists(\hypeJunction\MenusEntity\Bootstrap::class));
	}
}
