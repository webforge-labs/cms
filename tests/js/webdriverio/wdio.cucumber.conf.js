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
          require('../../../index').stepDefinitionsFile('ui'),
          require('../../../index').stepDefinitionsFile('mail')
        ]
    },

    beforeFeature: function (feature) {
      browser.windowHandleSize({width:1024,height:900});
      browser.timeouts('page load', 60000);
      browser.timeouts('script', 2010);
    },

    afterFeature: function() {
      var logs = browser.log('browser');

      if (logs.state === 'success' && logs.value.length) {
        console.log('from browser:');
        console.log(logs.value);
      }
    },

    afterStep: function (stepResult) {
      var step = stepResult.getStep();

      var stati = {
        'passed': '√',
        'failed': 'x',
        'skipped': 'S',
        'undefined': 'undef',
        'ambiguous': 'A'
      };

      console.log(stati[stepResult.getStatus()]+' '+step.getName());

      if (stepResult.getStatus() == 'failed') {
        var date = new Date();
        var timestamp = date.toJSON().replace(/:/g, '-')
        var filename = `ERROR_wbfrg_testing_${timestamp}.png`;
        client.saveScreenshot(client.options.screenshotPath+'/'+filename);
      } else if (stepResult.getStatus() == 'ambiguous') {
        throw new Error('Step definition '+stepResult.getStep().getName()+' is ambigious');
      }
    }
};

if (process.env.SYMFONY_ENV === 'dev') {
  exports.config.baseUrl = process.env.SYMFONY_BASEURL;
  exports.config.reporters = ['spec'];
  exports.config.cucumberOpts.failFast = true;
  exports.config.services = ['phantomjs'];
  exports.config.screenshotPath = './.screenshots';
} else if (process.env.SYMFONY_BASEURL) {
  exports.config.baseUrl = process.env.SYMFONY_BASEURL;
  exports.config.reporters = ['spec', 'junit'];
  exports.config.cucumberOpts.failFast = false;
  exports.config.services = ['phantomjs'];
  exports.config.screenshotPath = './reports/screenshots';
} else if (hostname === 'travis-ci') {
  exports.config.baseUrl = 'http://localhost:8080';
  exports.config.reporters = ['spec', 'junit'];
  exports.config.cucumberOpts.failFast = false;
  exports.config.services = ['selenium-standalone'];
} else {
  throw new Error('configure for your host ('+hostname+') the baseUrl');
}