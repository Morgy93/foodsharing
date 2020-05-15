
import { addParameters } from '@storybook/vue'
import { create } from '@storybook/theming'


import { addons } from '@storybook/addons';
import { themes } from '@storybook/theming';

addons.setConfig({
  theme: create({
    base: 'light',
    brandTitle: 'Foodsharing Storybook',
    brandUrl: 'https://gitlab.com/foodsharing-dev/foodsharing',
  })
});
