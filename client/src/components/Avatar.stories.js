import centered from '@storybook/addon-centered/vue';
import { withKnobs, select, boolean } from '@storybook/addon-knobs';

import Avatar from './Avatar.vue';

export default {
  title: 'Avatar',
  decorators: [withKnobs, centered]
}

export const basic = () => ({
  components: { Avatar },
  props: {
    isSleeping: {
      default: boolean('Sleeping', false)
    },
    size: {
      default: select('Size', { '35': 35, '50': 50, '130': 130})
    }
  },
  template: `<avatar :sleepStatus="isSleeping" :size="size"/>`
});
