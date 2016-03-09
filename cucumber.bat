@echo off

if [%1]==[] (
  node_modules\.bin\cucumber-js.cmd features --format=pretty -r tests/js/cucumber/bootstrap.js --snippet-syntax=tests/js/cucumber/snippet-template.js %1 %2 %3 %4
) else (
  node_modules\.bin\cucumber-js.cmd features/%1.feature --format=pretty -r tests/js/cucumber/bootstrap.js --snippet-syntax=tests/js/cucumber/snippet-template.js %2 %3 %4 %5
)