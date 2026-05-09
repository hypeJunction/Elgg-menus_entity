<a name="7.0.0"></a>
# 7.0.0 (2026-05-09)

### Breaking Changes

* **elgg:** raise minimum to Elgg 7.x (PHP 8.3+). Plugins on Elgg 6.x must stay on menus_entity 6.x.

### Migration (6.x → 7.x)

* **composer:** `elgg/elgg ~7.0.0`, PHP `>=8.3`; `hypejunction/menus_dropdown` bumped to `~7.0`.
* **docker:** test stack added for Elgg 7.x (docker/elgg7/).
* No PHP or CSS breaking changes. No data migration required.

<a name="6.0.0"></a>
# 6.0.0 (2026-05-09)

### Breaking Changes

* **elgg:** raise minimum to Elgg 6.x (PHP 8.1+). Plugins on Elgg 5.x must stay on menus_entity 5.x.

### Migration (5.x → 6.x)

* **composer:** `elgg/elgg ~6.1.0`, PHP `>=8.1`, added `ext-intl`; `hypejunction/menus_dropdown` bumped to `~6.0`.
* **bootstrap:** `Bootstrap` simplified to extend `DefaultPluginBootstrap` (no-op method stubs removed).
* **docker:** test stack added for Elgg 6.x (docker/elgg6/).
* No data migration required.

<a name="5.0.0"></a>
# 5.0.0 (2026-05-08)

### Breaking Changes

* **elgg:** raise minimum to Elgg 5.x (PHP 8.2+). Plugins on Elgg 4.x must stay on menus_entity 4.x.

### Migration (4.x → 5.x)

* **events:** `elgg-plugin.php` `hooks` key renamed to `events`.
* **handler:** `SetupEntityMenu` type hint updated from `Elgg\Hook` to `Elgg\Event`.
* **tests:** integration tests updated to use `elgg_trigger_event_results()` and pass `MenuItems` as value argument.
* **docker:** stack updated to `php:8.2-apache`, `mysql:8.0`, `elgg/elgg 5.1.12`.

### Dependency Updates

* `elgg/elgg ^5.0`, PHP `>=8.2`, `hypejunction/menus_dropdown ~5.0`, version bumped to `5.0.0`

---

<a name="4.0.0"></a>
# 4.0.0 (2026-04-12)

### Breaking Changes

* **elgg:** raise minimum to Elgg 4.x (PHP 7.4+). Plugins on Elgg 3.x must stay on menus_entity 2.x.

### Migration (3.x → 4.x)

* **bootstrap:** deleted `start.php`, `manifest.xml`, and `autoloader.php`. Plugin metadata now lives in `composer.json` + `elgg-plugin.php` only.
* **bootstrap class:** introduced `hypeJunction\MenusEntity\Bootstrap` extending `Elgg\PluginBootstrap`; registers the plugin's single event listener.
* **hook handler:** `SetupEntityMenu` converted to an invokable class registered declaratively at priority 999 in `elgg-plugin.php` under `hooks → register → menu:entity`.
* **autoload:** switched composer autoload from `classmap` to `psr-0` with `classes/` root.
* **tests:** PHPUnit integration tests updated to use `$plugin->setSetting()`/`unsetSetting()` (Elgg 4.x API).

### Dependency Updates

* `elgg/elgg ^4.0`, `composer/installers ^2.0`, PHP `>=7.4`, version bumped to `4.0.0`

---

<a name="2.0.0"></a>
# [2.0.0](https://github.com/hypeJunction/Elgg-menus_entity/compare/1.0.1...v2.0.0) (2017-03-02)


### Bug Fixes

* **hooks:** clear log warnings ([8de1230](https://github.com/hypeJunction/Elgg-menus_entity/commit/8de1230))

### Features

* **deps:** upgrade for Elgg 2.3 ([93628c3](https://github.com/hypeJunction/Elgg-menus_entity/commit/93628c3))



<a name="1.0.1"></a>
## [1.0.1](https://github.com/hypeJunction/Elgg-menus_entity/compare/1.0.0...v1.0.1) (2016-03-20)


### Bug Fixes

* **manifest:** fix requres ([a08bea3](https://github.com/hypeJunction/Elgg-menus_entity/commit/a08bea3))



<a name="1.0.0"></a>
# 1.0.0 (2016-01-27)


### Features

* **releases:** initial commit ([4d53298](https://github.com/hypeJunction/Elgg-menus_entity/commit/4d53298))



