import io from 'socket.io-client'

// eslint-disable-next-line camelcase
import { session_id, GET } from '@/script'

import msg from '@/msg'
import conv from '@/conv'
import bellsStore from '@/stores/bells'
import conversationStore, { convertMessage } from '@/stores/conversations'

export default {
  connect: function () {
    var socket = io.connect(window.location.host, { path: '/chat/socket.io' })
    socket.on('connect', function () {
      console.log('WebSocket connected.')
      socket.emit('register', session_id())
    })

    socket.on('conv', async function (data) {
      if (data.m === 'push') {
        const obj = JSON.parse(data.o)
        await conversationStore.updateFromPush(obj)
        const message = convertMessage(obj.message)
        obj.message = message
        if (GET('page') === 'msg') {
          msg.push(obj)
        } else {
          conv.push(obj)
        }
      }
    })

    socket.on('info', function (data) {
      if (data.m === 'badge') {
        // info.badge('info', data.o.count)
      }
    })

    socket.on('bell', function (data) {
      if (data.m === 'update') {
        bellsStore.loadBells()
      }
    })
  }
}
