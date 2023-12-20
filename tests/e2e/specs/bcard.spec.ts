import { test, expect } from "@playwright/test";

test.describe("business card", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto("/?page=bcard");
  });

  test("has login", async ({ page }) => {
    await expect(
      page.getByRole("button", { name: "Einloggen", exact: true }),
    ).toBeVisible();
  });
});
