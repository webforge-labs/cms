var argv = require('yargs').argv;
var isDevelopment = !!argv.dev;

var Cms = require('./index');
var gulp = require('gulp');

var builder = new Cms.Builder(gulp, __dirname, require, isDevelopment, {
  browserSync: {
    proxy: 'cms.laptop.ps-webforge.net'
  }
});

builder.addJsNamespace('admin', 'src/js/admin');

builder.configure();