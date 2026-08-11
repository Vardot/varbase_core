'use strict';

const { Given, When } = require('@cucumber/cucumber');

const {
  friendly,
  gotoUrl,
  waitForPageLoad,
} = require('@vardot/varbase-e2e/tests/step-definitions/varbase-e2e');

/**
 * Run a step body and rethrow any failure as a tester-friendly error.
 *
 * @param {Function} body
 *   Async function performing the step.
 * @param {string} message
 *   Human-readable description for failures.
 */
async function attempt(body, message) {
  try {
    await body();
  }
  catch (err) {
    throw friendly(message, err);
  }
}

/**
 * Drop back to an anonymous session by clearing every cookie.
 *
 * Example: Given I am an anonymous visitor
 */
Given(/^(?:I |we )?am an anonymous visitor$/, async function () {
  await attempt(async () => {
    await this.context.clearCookies();
  }, 'Could not clear the session to become anonymous');
});

/**
 * Create a Basic page through the node-add form (Varbase Page content type).
 *
 * Example: When I create a basic page titled "Varbase Core test page"
 */
When(/^(?:I |we )?create a basic page titled "([^"]*)"$/, async function (title) {
  await attempt(async () => {
    await gotoUrl(this.page, `${this.parameters.launchUrl}/node/add/page`);
    await this.page.locator('#edit-title-0-value').fill(title);
    await this.page.locator('#edit-submit').click();
    await waitForPageLoad(this.page, this.minWaitTime && this.minWaitTime.page);
  }, `Could not create a basic page titled "${title}"`);
});

/**
 * Open an administration page and assert it is reachable.
 *
 * Uses the varbase-e2e smart-wait helpers (gotoUrl + waitForPageLoad) so heavy
 * Varbase admin pages are fully settled before the assertion, and reports any
 * access-denied / not-found / fatal-error page with a tester-friendly message.
 *
 * Example: When I open the administration page "/admin/config"
 */
When(/^I open the administration page "([^"]*)"$/, async function (path) {
  await attempt(async () => {
    await gotoUrl(this.page, `${this.parameters.launchUrl}${path}`);
    await waitForPageLoad(this.page, (this.minWaitTime && this.minWaitTime.page) || 10000);
    const bad = await this.page.locator(
      'h1:has-text("Access denied"), h1:has-text("Page not found"), h1:has-text("The website encountered an unexpected error")'
    ).count();
    if (bad > 0) {
      throw new Error(`The page "${path}" returned an access-denied, not-found or error response`);
    }
  }, `Could not open the administration page "${path}"`);
});
