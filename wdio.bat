@echo off

IF EXIST node_modules\webforge-testing\node_modules\.bin\wdio.cmd (
  node_modules\webforge-testing\node_modules\.bin\wdio tests\js\webdriverio\wdio.cucumber.conf.js %*
) ELSE (
  node_modules\.bin\wdio tests\js\webdriverio\wdio.cucumber.conf.js %*
)