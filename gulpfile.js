var argv = require('yargs').argv;
var isDevelopment = !!argv.dev;

var Cms = require('./index');
var gulp = require('gulp');

var builder = new Cms.Builder(gulp, __dirname, require, isDevelopment);

builder.addJsNamespace('admin', 'src/js/admin');

builder.configure();