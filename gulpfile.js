var argv = require('yargs').argv;
var isDevelopment = !!argv.dev;
require('dotenv').config();

var Cms = require('./index');
var gulp = require('gulp');

var builder = new Cms.Builder(gulp, __dirname, require, isDevelopment, {
  browserSync: {
    proxy: process.env.DOMAIN,
    open: false,
    reloadOnRestart: true
  }
});

builder.addJsNamespace('admin', 'src/js/admin');

builder.configure();