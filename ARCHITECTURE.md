# menus_entity — Architecture (Elgg 4.x)

## Purpose

Dropdown entity menus plugin. Reorganises the `menu:entity` hook result so
that configurable "primary" actions remain directly visible, and all other
actions collapse into an ellipsis (…) dropdown.

## Entry Point

`elgg-plugin.php` — declares the plugin, default settings, and one hook
handler via the `hooks` key.

## Class Inventory

| Class | Role |
|-------|------|
| `hypeJunction\MenusEntity\Bootstrap` | Lifecycle stub (load/boot/init/ready/shutdown/activate/deactivate/upgrade). No logic — plugin is entirely declarative. |
| `hypeJunction\MenusEntity\SetupEntityMenu` | Invokable hook handler for `register, menu:entity` (priority 999). |

## Hook / Event Registration

| Type | Name | Type param | Handler | Priority |
|------|------|------------|---------|----------|
| hook | register | menu:entity | `SetupEntityMenu` | 999 |

## Hook Handler Logic (`SetupEntityMenu`)

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

- `hypejunction/menus_dropdown` (~3.0 || ~4.0) — provides the dropdown
  rendering that consumes the `ellipsis` parent item.

## Migration Notes (3.x → 4.x)

- Converted from `start.php` + `activate.php` + `autoloader.php` to
  `elgg-plugin.php` Bootstrap pattern.
- Hook handler class (`SetupEntityMenu`) was extracted from the procedural
  callback.
- No data model changes; no `Elgg\Upgrade\Batch` required.
- `Elgg\Hook` interface is still the correct type hint for `register,
  menu:entity` handlers in Elgg 4.x (returnvalue hook, not a pure event).
  Do not change to `Elgg\Event` until migrating to Elgg 5.x.

## Test Coverage

- **Unit** (`tests/phpunit/unit/`): structural tests — class shape, type
  hints, invokability.
- **Integration** (`tests/phpunit/integration/`): full Elgg bootstrap;
  triggers `register, menu:entity` via `elgg_trigger_plugin_hook` and
  asserts menu item placement, ellipsis creation, icon/subsection metadata.
- **Playwright** (`tests/playwright/`): browser-level smoke test of the
  entity menu on the live Elgg site.

Run integration tests inside the Docker stack:

```bash
docker compose -f docker/docker-compose.yml up -d
docker compose -f docker/docker-compose.yml exec elgg \
  php vendor/bin/phpunit -c mod/menus_entity/tests/phpunit.xml
```
