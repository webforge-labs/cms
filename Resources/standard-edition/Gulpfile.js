var argv = require('yargs').argv;
var isDevelopment = !!argv.dev;

var Cms = require('webforge-cms');
var gulp = require('gulp');
var plugins = require('gulp-load-plugins')();
var cmsBuilder = new Cms.Builder(gulp, __dirname, require, isDevelopment);

cmsBuilder.addTabModule('admin/post/form', { include: [
  'cms/ko-components/multiple-files-chooser',
  'cms/ko-bindings/markdown-editor',
  'cms/ko-bindings/date-picker',
  'cms/modules/bootstrap-select'
]});
cmsBuilder.addTabModule('admin/post/list');

cmsBuilder.addModule('web/main');

cmsBuilder.addJsNamespace('admin', 'src/js/admin');
cmsBuilder.addJsNamespace('web', 'src/js/web');

//cmsBuilder.mainTasks.push('app-images');
cmsBuilder.configure();

var builder = cmsBuilder.jsBuilder;

gulp.task('app-images', ['clean'], function() {
  return gulp.src('Resources/img/**/*')
    .pipe(plugins.imagemin())
    .pipe(gulp.dest(builder.config.dest+'/img'));
});

builder.add('fonts', 'app-fonts')
 .src('Resources/fonts/**/*');

builder.add('js', 'tether')
  .src('node_modules/tether/dist/js/tether.js');

builder.add('js', 'bootstrap4')
 .src('Resources/bootstrap/dist/js/bootstrap.js')
 .pipe(plugins.wrap, {
   deps: ['jquery', 'tether'],
   params: ['jQuery', 'Tether'],
   exports: 'jQuery'
 })
.pipe(plugins.rename, 'bootstrap4.js');