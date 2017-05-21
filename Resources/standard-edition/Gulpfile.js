var argv = require('yargs').argv;
var isDevelopment = !!argv.dev;

var Cms = require('webforge-cms');
var gulp = require('gulp');
var rename = require('gulp-rename');
var wrap = require('gulp-wrap-amd');
var cmsBuilder = new Cms.Builder(gulp, __dirname, require, isDevelopment);

cmsBuilder.addTabModule('admin/post/form', { include: [
  'cms/ko-components/multiple-files-chooser',
  'cms/ko-bindings/markdown-editor',
  'cms/ko-bindings/date-picker',
  'cms/modules/bootstrap-select',
  'admin/content-manager-blocks'
]});
cmsBuilder.addTabModule('admin/post/list');

cmsBuilder.addTabModule('admin/quote/form', { include: [
  'cms/ko-bindings/markdown-editor',
  'cms/ko-bindings/date-picker'
]});
cmsBuilder.addTabModule('admin/quote/list');

cmsBuilder.addModule('web/main');

cmsBuilder.addJsNamespace('admin', 'src/js/admin');
cmsBuilder.addJsNamespace('web', 'src/js/web');

//cmsBuilder.mainTasks.push('app-images');
cmsBuilder.configure();

var builder = cmsBuilder.jsBuilder;


//gulp.task('app-images', ['clean'], function() {
//  return gulp.src('Resources/img/**/*')
//    .pipe(gulp.dest(builder.config.dest+'/img'));
//});

//builder.add('fonts', 'app-fonts')
// .src('Resources/fonts/**/*');

builder.add('js', 'tether')
  .src('node_modules/tether/dist/js/tether.js');

builder.add('js', 'bootstrap4')
 .src('Resources/bootstrap/dist/js/bootstrap.js')
 .pipe(wrap, {
   deps: ['jquery', 'tether'],
   params: ['jQuery', 'Tether'],
   exports: 'jQuery'
 })
.pipe(rename, 'bootstrap4.js');