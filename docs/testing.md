# Testing

## structure

Note that the core from the CmsBundle should be located in src\php\Webforge\CmsBundle. With all its resources which are needed for a project to run.  
This repository is itself a Project, which is using the cms to make it acceptance-testable. E.g. everything in `app/` `etc/` and `src\php\AppBundle` should be related to creating a frame to test the cms and it's concepts.


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