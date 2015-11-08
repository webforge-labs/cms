@echo off

node_modules\.bin\cucumber-js.cmd features/%1.feature --format=pretty -r tests/js/cucumber/bootstrap.js