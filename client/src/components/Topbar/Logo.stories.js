import centered from '@storybook/addon-centered/vue';

import Logo from './Logo.vue';

export default {
  title: 'Topbar/Logo',
  decorators: [centered]
}

export const basic = () => ({
  components: { Logo },
  template: '<logo />'
});
