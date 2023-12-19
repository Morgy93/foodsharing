import { expect, test } from "@playwright/test";

test.describe("user", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto("/");
  });

  test("can register", async ({ page }) => {
    await page.getByRole("link", { name: "Mitmachen Mitmachen" }).click();
    await page.getByRole("link", { name: "Jetzt registrieren" }).click();

    await page.getByRole("textbox", { name: "E-Mail-Adresse" }).click();
    await page
      .getByRole("textbox", { name: "E-Mail-Adresse" })
      .fill("user@foodsharing-test.de");
    await page.getByLabel("Passwort (mindestens 8").fill("12345678");
    await page.getByLabel("Passwortwiederholung").fill("12345678");
    await page.getByRole("button", { name: "weiter" }).click();

    await page.getByText("weiblich").click();
    await page.getByLabel("Dein Vorname").fill("Vorname");
    await page.getByLabel("Dein Nachname").fill("Nachname");
    await page.getByRole("button", { name: "weiter" }).click();

    await page.getByText("Kein Datum gewählt").click();
    await page.getByLabel("12/01/").click();
    await page.getByRole("button", { name: "weiter" }).click();

    await page.getByPlaceholder("Beispiel +49 179").fill("+49 123456789");
    await page.getByRole("button", { name: "weiter" }).click();

    await page.getByRole("checkbox", { name: "acceptGdpr" }).check();
    await page.getByRole("checkbox", { name: "acceptLegal" }).check();
    await page.getByRole("button", { name: "Anmeldung absenden" }).click();

    await page.getByRole("button", { name: "Einloggen", exact: true }).click();

    await page
      .getByRole("textbox", { name: "E-Mail-Adresse" })
      .fill("user@foodsharing-test.de");
    await page.getByRole("textbox", { name: "Passwort" }).fill("12345678");
    await page.getByRole("button", { name: "Einloggen", exact: true }).click();

    await expect(
      page.getByRole("heading", { name: "Hallo Vorname! Schön, dass du" }),
    ).toBeVisible();
  });
});
