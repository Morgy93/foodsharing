import { expect, test } from "@playwright/test";

test.describe("user", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto("/");
  });

  type Cookie = {
    name: string;
    expires: number;
  };

  function getSessionCookie(cookies: Cookie[]) {
    return cookies.find((cookie) => cookie.name === "PHPSESSID");
  }

  test.fixme("can login", async ({ page }) => {
    await page.getByRole("button", { name: "Einloggen Einloggen" }).click();
    await page.getByLabel("Einloggen", { exact: true }).click();
    await expect(
      page.getByRole("heading", { name: "Hallo Bot! Schön, dass du" }),
    ).toBeVisible();

    const sessionCookie = getSessionCookie(await page.context().cookies());
    expect(sessionCookie.expires).toBe(-1);
  });

  test.fixme("can login permanent", async ({ page }) => {
    await page.getByRole("button", { name: "Einloggen Einloggen" }).click();
    await page.getByLabel("Dauerhaft eingeloggt bleiben").check();
    await page.getByLabel("Einloggen", { exact: true }).click();
    await expect(
      page.getByRole("heading", { name: "Hallo Bot! Schön, dass du" }),
    ).toBeVisible();

    const sessionCookie = getSessionCookie(await page.context().cookies());
    expect(sessionCookie.expires).toBeGreaterThan(0);

    await page
      .getByRole("button", { name: "Profilbild 👋 Hallo Bot!" })
      .click();
    await page.getByRole("menuitem", { name: " Abmelden" }).click();
    await page.getByRole("button", { name: "Einloggen Einloggen" }).click();
    await expect(page.getByLabel("Dauerhaft eingeloggt bleiben")).toBeChecked();
  });
});
