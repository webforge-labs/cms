# Testing

## Step definitions

try to use as many role[xxx] selectors in the step definitions instead of normal selectors like classes or even html elements. Dont couple the step definitions to hard with the layout of the cms, so that refactorings would not require too much changing of all selectors in the step-definitions.

## webforge TestSuite (cucumber howto)

Instantiate your cucumber-world like this:

```js
module.exports = function() {
  var Cms = require('webforge-cms');

  var testSuite = new Cms.TestSuite({
    root: [__dirname, '..', '..', '..', '..', '..']
  });

  this.World = function MyProjectWorld() {
    // do custom stuff here
  };

  testSuite.cucumber.injectWorld(this.World);
};
```

## this.directory

In you cucumberStep you can then use:

```js
  this.Given(/^the file is in the directory:$/, function () {
    this.directory('some/relative/path/to/project-root/file.txt');
  });
```

root is the directory that you passed to `TestSuite: options.root`

## this.cli

returns a promise that is resolved with the stdout from the call to the project-commmand-line-interface. The project-command-line-interface should reside in `bin/cli.sh` and `bin/cli.bat` and could call the symfony cli for example.