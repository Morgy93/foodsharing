import centered from '@storybook/addon-centered/vue';

import FsFooter from './Footer.vue';

export default {
  title: 'Footer'
}

export const germany = () => ({
  components: { FsFooter },
  template: `<fs-footer />`
});

export const austria = () => ({
  components: { FsFooter },
  template: `<fs-footer isFsDotAt="1"/>`
});
