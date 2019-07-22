const path = require('path')
const clientRoot = path.resolve(__dirname)

function lib (filename) {
  return path.join(clientRoot, 'lib', filename)
}

const production = process.env.NODE_ENV === 'production'

Object.assign(module.exports, convert({

  leaflet: {
    dependencies: [
      'leaflet/dist/leaflet.css'
    ]
  },

  'leaflet.awesome-markers': {
    resolve: require.resolve('leaflet.awesome-markers/dist/leaflet.awesome-markers.js'),
    imports: {
      L: 'leaflet'
    },
    dependencies: [
      require.resolve('leaflet.awesome-markers/dist/leaflet.awesome-markers.css')
    ]
  },

  'leaflet.markercluster': {
    imports: {
      L: 'leaflet'
    },
    dependencies: [
      require.resolve('leaflet.markercluster/dist/MarkerCluster.css'),
      require.resolve('leaflet.markercluster/dist/MarkerCluster.Default.css')
    ]
  },

  'jquery-migrate': {
    // production version does not show all the JQMIGRATE warnings/traces
    resolve: require.resolve(production ? 'jquery-migrate/dist/jquery-migrate.min.js' : 'jquery-migrate'),
    // disableAMD resolves https://github.com/jquery/jquery-migrate/issues/273
    // maybe can remove if newer versions of jquery-migrate fix the issue
    disableAMD: true,
    imports: {
      jQuery: 'jquery'
    }
  },

  'jquery-slimscroll': {
    imports: {
      jQuery: 'jquery'
    }
  },

  'jquery-contextmenu': {
    imports: {
      jQuery: 'jquery'
    }
  },

  'timeago/jquery.timeago': {
    disableAMD: true,
    imports: {
      jQuery: 'jquery'
    }
  },

  'corejs-typeahead': {
    dependencies: [
      'css/typeahead.css'
    ]
  },

  'typeahead-address-photon': {
    imports: {
      jQuery: 'jquery',
      Bloodhound: 'corejs-typeahead'
    },
    exports: 'this.PhotonAddressEngine'
  },

  'jquery-ui-addons': {
    resolve: lib('jquery-ui-addons.js'),
    imports: {
      jQuery: 'jquery',
      'window.jQuery': 'jquery'
    },
    dependencies: [
      'jquery-ui'
    ]
  },

  'jquery-fancybox': {
    resolve: lib('fancybox/jquery.fancybox.pack.js'),
    imports: {
      jQuery: 'jquery'
    }
  },

  'jquery-dynatree': {
    resolve: lib('dynatree/jquery.dynatree.js'),
    imports: {
      jQuery: 'jquery'
    },
    dependencies: [
      lib('dynatree/skin/ui.dynatree.css')
    ]
  },

  'jquery-tablesorter': {
    resolve: lib('tablesorter/jquery.tablesorter.js'),
    imports: {
      jQuery: 'jquery'
    }
  },

  'tablesorter-pagercontrols': {
    resolve: lib('tablesorter/jquery.tablesorter.pager.js'),
    imports: {
      jQuery: 'jquery'
    }
  },

  tablesorter: {
    resolve: lib('tablesorter/jquery.tablesorter.js')
  },

  'jquery-tagedit-auto-grow-input': {
    resolve: lib('tagedit/js/jquery.autoGrowInput.js')
  },

  'jquery-tagedit': {
    resolve: lib('tagedit/js/jquery.tagedit.js')
  },

  'jquery.tinymce': {
    resolve: lib('tinymce/jquery.tinymce.min')
  }

}))

function convert (entries) {
  if (!global._counter) global._counter = 0
  const rules = []
  const aliases = {}

  for (const [name, options] of Object.entries(entries)) {
    const importsLoaderOptions = []

    const {
      resolve,
      disableAMD = false,
      imports = {},
      dependencies = [],
      exports
    } = options

    const test = resolve || require.resolve(name)

    if (resolve) {
      aliases[name] = resolve
    }

    if (disableAMD) {
      importsLoaderOptions.push('define=>false')
    }

    for (const [k, v] of Object.entries(imports)) {
      importsLoaderOptions.push(`${k}=${v}`)
    }

    for (const dependency of dependencies) {
      importsLoaderOptions.push(`_${global._counter++}=${dependency}`)
    }

    if (exports) {
      rules.push({
        test,
        use: `exports-loader?${exports}`
      })
    }

    rules.push({
      test,
      use: {
        loader: 'imports-loader',
        options: importsLoaderOptions.join(',')
      }
    })
  }

  return { rules, alias: aliases }
}
