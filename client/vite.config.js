import { resolve } from 'path'
import { unlinkSync, existsSync } from 'fs'

const symfonyPlugin = {
  name: 'symfony',
  configResolved (config) {
    if (config.env.DEV && config.build.manifest) {
      const buildDir = resolve(config.root, config.build.outDir, 'manifest.json')
      existsSync(buildDir) && unlinkSync(buildDir)
    }
  },
  configureServer (devServer) {
    const { watcher, ws } = devServer
    watcher.add(resolve('templates/**/*.twig'))
    watcher.on('change', function (path) {
      if (path.endsWith('.twig')) {
        ws.send({
          type: 'full-reload',
        })
      }
    })
  },
}

export default {
  plugins: [symfonyPlugin],
  server: {
    watch: {
      disableGlobbing: false,
    },
  },
  root: './public',
  base: '/',
  build: {
    manifest: true,
    emptyOutDir: true,
    rollupOptions: {
      input: ['./assets/app.js'],
    },
  },
}
