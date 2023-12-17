# Testing

## Playwright: Automated Browser Testing

### Installation

1. Install NodeJS (https://nodejs.org/en/download/)
2. Change to Playwright directory `cd tests/e2e`
3. Install Playwright `npm ci && npx playwright install --with-deps`

### Usage

Run `npx playwright [options] [command]` e.g. `npx playwright test`

More on <https://playwright.dev/docs/running-tests>

### Contributing

#### Husky, ESLint, and Prettier

We use a combination of [Husky](https://github.com/typicode/husky), [ESLint](https://eslint.org/), and [Prettier](https://prettier.io/) within our repository to enforce consistent coding practices.
Husky is a tool that installs a pre-commit hook to run the linter before each commit attempt.
To install the pre-commit hook, run the following command:

```bash
npm run prepare
```

If needed, you can still bypass the commit hook by passing `--no-verify` in your git commit message.
