const lightCodeTheme = require('prism-react-renderer/themes/github');
const darkCodeTheme = require('prism-react-renderer/themes/dracula');
const simplePlantUML = require("@akebifiky/remark-simple-plantuml");

module.exports = {
  title: 'Foodsharing DEV',
  tagline: 'Together for more code rescue',
  url: 'https://devdocs.foodsharing.network',
  baseUrl: '/',
  onBrokenLinks: 'throw',
  onBrokenMarkdownLinks: 'warn',
  favicon: 'img/foodsharing_fork.svg',

  organizationName: 'foodsharing-dev',
  projectName: 'foodsharing',
  i18n: {
    defaultLocale: 'en',
    locales: ['en'],
  },
  presets: [
    [
      '@docusaurus/preset-classic',
      {
        docs: {
          sidebarPath: require.resolve('./sidebars.js'),
          remarkPlugins: [simplePlantUML],
          lastVersion: 'current',
          versions: {
            current: {
              label: 'Current',
              path: 'current',
            },
          },
          editUrl: 'https://gitlab.com/foodsharing-dev/foodsharing/-/blob/master/docs/',
        },
        theme: {
          customCss: require.resolve('./src/css/custom.css'),
        },
      }
    ],
  ],
  themeConfig: {
    navbar: {
      title: 'Foodsharing',
      logo: {
        alt: 'Foodsharing LOGO',
        src: 'img/foodsharing_fork.svg',
      },
      items: [
        {
          type: 'doc',
          docId: 'intro',
          position: 'left',
          label: 'Guide',
        },
        {
          type: 'docsVersionDropdown',
          position: 'left',
        },
        {
          href: 'https://devblog.foodsharing.de',
          label: 'Dev Blog',
          position: 'right'
        },
        {
          href: 'https://gitlab.com/foodsharing-dev/foodsharing',
          label: 'Gitlab',
          position: 'right',
        },
        {
          label: 'Slack',
          href: 'https://slackin.yunity.org',
          position: 'right',
        },
      ],
    },
    footer: {
      style: 'dark',
      links: [
        {
          title: 'Foodsharing Plattforms',
          items: [
            {
              label: 'Foodsharing',
              href: 'https://www.foodsharing.de',
            },
            {
              label: 'Foodsharing (Beta)',
              href: 'https://beta.foodsharing.de',
            },
            {
              label: 'Foodsharing Wiki',
              href: 'https://wiki.foodsharing.de',
            },
          ],
        },
        {
          title: 'More',
          items: [
            {
              label: 'Dev Blog',
              href: 'https://devblog.foodsharing.de',
            },
            {
              label: 'Slack (Chat)',
              href: 'https://slackin.yunity.org',
            },
          ],
        },
      ],
      copyright: `Foodsharing e.V. Built with Docusaurus.`,
    },
    tableOfContents: {
      minHeadingLevel: 2,
      maxHeadingLevel: 5,
    },
    prism: {
      theme: lightCodeTheme,
      darkTheme: darkCodeTheme,
    },
  },
};
