var hostname = require('os').hostname();

exports.config = {
    /**
     * server configurations
     */
    host: '127.0.0.1',
    port: 4444,

    /**
     * specify test files
     */
    specs: [
        './features/*.feature'
    ],
    exclude: [
    ],

    maxInstances: 1,

    /**
     * capabilities
     */
    capabilities: [
        {
            browserName: 'phantomjs'
        }
    ],
    /*
     {
        browserName: 'chrome'
    }, {
        browserName: 'firefox'
    }
    */

    /**
     * test configurations
     */
    baseUrl: 'host-dependend',

    logLevel: 'silent',
    coloredLogs: true,
    screenshotPath: '.screenshots',
    waitforTimeout: 10000,
    framework: 'cucumber',

    reporters: ['spec'],
    reporterOptions: {
        outputDir: './reports'
    },

    cucumberOpts: {
        require: [
          './tests/js/webdriverio/step-definitions.js',
          require('../../../index').stepDefinitionsFile('basics'),
          require('../../../index').stepDefinitionsFile('ui')
        ]
    }
};

if (hostname === 'psc-laptop') {
  exports.config.baseUrl = 'http://cms.laptop.ps-webforge.net';
} else if (hostname === 'psc-desktop') {
  exports.config.baseUrl = 'http://cms.desktop.ps-webforge.net';
} else if (hostname === 'travis-ci') {
  exports.config.baseUrl = 'http://localhost:8080';
  exports.config.reporters = ['spec', 'junit'];
  exports.config.cucumberOpts.failFast = false;
  exports.config.services = ['selenium-standalone'];
} else {
  throw new Error('configure for your host ('+hostname+') the baseUrl');
}