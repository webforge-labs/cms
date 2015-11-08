/* globals __dirname */
var os = require('os');
var path = require('path');

module.exports = {
  cli: [__dirname, '..', '..', 'bin', 'cli.'+(os.platform() === 'win32' ? 'bat' : 'sh')].join(path.sep),
  domains: {
    'psc-laptop': 'cms.laptop.ps-webforge.net',
    'draco': 'staging.cms.ps-webforge.net',
    'psc-desktop': 'cms.desktop.ps-webforge.net'
  },
  browser: {
    waitDuration: 40000
  },
  debug: false
};