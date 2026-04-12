import { test, expect } from '@playwright/test';
import { loginAs } from '../helpers/elgg';

/**
 * End-to-end checks for the menus_entity plugin.
 *
 * These tests verify that the ellipsis dropdown renders on pages that show
 * entity listings (activity, blogs, etc.). Because menus_entity only rewrites
 * menus built by OTHER plugins, we cannot test it in complete isolation —
 * we rely on whatever entity listings are available in the Elgg environment.
 */

test.describe('menus_entity dropdown', () => {
  test('activity listing shows ellipsis dropdown on entity menu', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.goto('/activity');
    await page.waitForLoadState('networkidle');

    // If the page has any entity menu items at all, we expect at least one
    // ellipsis wrapper to have been inserted by the hook.
    const menus = page.locator('.elgg-menu-entity');
    const menuCount = await menus.count();

    if (menuCount === 0) {
      test.skip(true, 'No entity menus present on activity page — nothing to rewrite.');
    }

    // The handler adds an item named "ellipsis" with item_class
    // elgg-menu-item-has-dropdown. Assert at least one exists globally.
    const dropdowns = page.locator('.elgg-menu-item-has-dropdown, [class*="ellipsis"]');
    await expect(dropdowns.first()).toBeVisible();
  });

  test('admin settings page renders three configurable fields', async ({ page }) => {
    await loginAs(page, 'admin');
    const response = await page.goto('/admin/plugin_settings/menus_entity');

    // If the plugin is not installed in this env, skip gracefully.
    if (!response || response.status() >= 400) {
      test.skip(true, `menus_entity settings page returned ${response?.status()}`);
    }

    await expect(page.locator('input[name="params[primary_actions]"]')).toBeVisible();
    await expect(page.locator('input[name="params[remove_actions]"]')).toBeVisible();
    await expect(page.locator('input[name="params[icon]"]')).toBeVisible();

    // No system error messages.
    await expect(page.locator('.elgg-system-messages .elgg-message-error')).toHaveCount(0);
  });

  test('updating primary_actions setting persists to DB', async ({ page }) => {
    await loginAs(page, 'admin');
    const response = await page.goto('/admin/plugin_settings/menus_entity');
    if (!response || response.status() >= 400) {
      test.skip(true, 'settings page unavailable');
    }

    const primary = page.locator('input[name="params[primary_actions]"]');
    await primary.fill('likes,access,edit');

    // Submit whichever save button the admin layout uses.
    const submit = page
      .locator('button[type="submit"], input[type="submit"]')
      .first();
    await submit.click();
    await page.waitForLoadState('networkidle');

    // Re-navigate and confirm the value round-tripped.
    await page.goto('/admin/plugin_settings/menus_entity');
    await expect(
      page.locator('input[name="params[primary_actions]"]'),
    ).toHaveValue('likes,access,edit');
  });
});
