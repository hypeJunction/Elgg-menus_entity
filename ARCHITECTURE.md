# menus_entity — Architecture (Elgg 7.x)

## Purpose

Dropdown entity menus plugin. Reorganises the `menu:entity` event result so
that configurable "primary" actions remain directly visible, and all other
actions collapse into an ellipsis (…) dropdown.

## Entry Point

`elgg-plugin.php` — declares the plugin, default settings, and one event
handler via the `events` key.

## Class Inventory

| Class | Role |
|-------|------|
| `hypeJunction\MenusEntity\Bootstrap` | Lifecycle stub (load/boot/init/ready/shutdown/activate/deactivate/upgrade). No logic — plugin is entirely declarative. |
| `hypeJunction\MenusEntity\SetupEntityMenu` | Invokable event handler for `register, menu:entity` (priority 999). |

## Event Registration

| Event | Type param | Handler | Priority |
|-------|------------|---------|----------|
| register | menu:entity | `SetupEntityMenu` | 999 |

## Handler Logic (`SetupEntityMenu`)

1. Reads `primary_actions` and `remove_actions` plugin settings.
2. Iterates the current `menu:entity` return value.
3. Removes items whose name is in `remove_actions`.
4. Items in `primary_actions` or without an href remain untouched.
5. All other items are re-parented to `ellipsis` and their section is reset
   to `default` (original section stored in `data.subsection` for
   `menus_api` compatibility).
6. `edit` and `delete` items receive special icon / subsection metadata.
7. If any item was moved to `ellipsis`, an ellipsis trigger `ElggMenuItem`
   is appended (icon driven by the `icon` setting, default `ellipsis-v`).

## Views

| View | Purpose |
|------|---------|
| `plugins/menus_entity/settings` | Admin settings form — `primary_actions`, `remove_actions`, `icon` |

## Settings

| Key | Default | Description |
|-----|---------|-------------|
| `primary_actions` | `access,likes,unlike,likes_count,published_status,membership,members` | Comma-separated action names kept outside the dropdown |
| `remove_actions` | `` | Comma-separated action names removed from the menu |
| `icon` | `ellipsis-v` | Font-Awesome icon name for the ellipsis trigger |

## Dependencies

- `hypejunction/menus_dropdown` (~7.0) — provides the dropdown rendering
  that consumes the `ellipsis` parent item.

## Migration Notes (6.x → 7.x)

- `elgg/elgg ~7.0.0`, `php >=8.3` in `composer.json`.
- `hypejunction/menus_dropdown` dependency bumped to `~7.0`.
- Docker test stack added for Elgg 7.x (docker/elgg7/) with PHP 8.3.
- No breaking changes: no CSS Crush syntax, no direct `ElggObject` instantiation, no removed Elgg APIs.
- No data migration needed.

## Migration Notes (5.x → 6.x)

- `elgg/elgg ~6.1.0`, `php >=8.1`, `ext-intl` added in `composer.json`.
- `hypejunction/menus_dropdown` dependency bumped from `~5.0` to `~6.0`.
- `Bootstrap` simplified to extend `DefaultPluginBootstrap` (all no-op method stubs removed).
- No JS files — no AMD→ESM conversion needed.
- Docker test stack added for Elgg 6.x (docker/elgg6/).
- No data migration needed.

## Migration Notes (4.x → 5.x)

- `elgg-plugin.php` `'hooks'` key renamed to `'events'`.
- `SetupEntityMenu`: `use Elgg\Hook` → `use Elgg\Event`; `__invoke(Hook $hook)` → `__invoke(Event $hook)`.
- Docker stack updated to `php:8.2-apache`, `mysql:8.0`, `elgg/elgg 5.1.12`.
- Integration tests: `elgg_trigger_plugin_hook()` → `elgg_trigger_event_results()`;
  value argument changed from `[]` to `new \Elgg\Menu\MenuItems([])` (Elgg 5.x
  core handlers call `->get()` on the value and expect a `MenuItems` collection).
- No data model changes; no `Elgg\Upgrade\Batch` required.

## Test Coverage

- **Unit** (`tests/phpunit/unit/`): structural tests — class shape, type hints, invokability.
- **Integration** (`tests/phpunit/integration/`): full Elgg bootstrap; triggers
  `register, menu:entity` via `elgg_trigger_event_results` and asserts menu item
  placement, ellipsis creation, icon/subsection metadata. 15 tests, 115 assertions.
- **Playwright** (`tests/playwright/`): browser-level smoke test of the entity menu.

Run integration tests inside the Docker stack:

```bash
docker compose -f docker/docker-compose.yml up -d
docker compose -f docker/docker-compose.yml exec elgg \
  php vendor/bin/phpunit -c mod/menus_entity/tests/phpunit.xml
```
