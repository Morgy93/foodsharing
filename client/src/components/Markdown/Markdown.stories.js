import { withKnobs, text, boolean, number } from "@storybook/addon-knobs";
import centered from '@storybook/addon-centered/vue';

import Markdown from './Markdown.vue';

export default {
  title: 'Markdown',
  decorators: [withKnobs, centered]
}

export const basic = () => ({
  components: { Markdown },
  props: {
    text: {
      default: text('Markdown content', '# This is a heading\n* List 1 with *italic text*\n* List 2\n* List 3 with **bold text**')
    }
  },
  template: '<markdown :source=text />'
});
