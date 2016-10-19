@echo off

node_modules\webforge-testing\node_modules\.bin\wdio tests\js\webdriverio\wdio.cucumber.conf.js %*

REM if [%1]==[] (
REM   node_modules\.bin\cucumber-js.cmd features --format=pretty -r tests/js/cucumber/bootstrap.js --snippet-syntax=tests/js/cucumber/snippet-template.js %1 %2 %3 %4
REM ) else (
REM   node_modules\.bin\cucumber-js.cmd features/%1.feature --format=pretty -r tests/js/cucumber/bootstrap.js --snippet-syntax=tests/js/cucumber/snippet-template.js %2 %3 %4 %5
REM )