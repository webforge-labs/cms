@echo off

node_modules\cuked-zombie\node_modules\.bin\cucumber-js.cmd features/%1.feature --format=pretty -r tests/js/cucumber/bootstrap.js --snippet-syntax=tests/js/cucumber/snippet-template.js %2 %3 %4 %5