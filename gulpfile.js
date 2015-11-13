var gulp = require('gulp');
var rename = require('gulp-rename');
var sass = require('gulp-sass');
var fs = require('fs');

var WebforgeBuilder = require('webforge-js-builder');
var builder = new WebforgeBuilder(gulp, { root: __dirname, dest: "www/assets" });

var argv = require('yargs').argv;
var isDevelopment = !!argv.dev;

builder
  .registerTask('clean')
  .registerTask('fonts')
  .registerTask('requirejs-config', { file: isDevelopment ? 'src/js/config-dev.js' : 'src/js/config.js'} )
  .registerTask('javascript', {
    combine: !isDevelopment,
    requirejs: {
      
      mainConfigFile: 'src/js/config.js', // adds some paths here. But notice: baseUrl will be overriden anyway through this config

      modules: [
        {
          name: "app/main"
        },
        {
          name: "app/login"
        },
      ],
    }
  })
//  .registerTask('templates', { path: 'app/Resources/tpl' })

  .registerTask('less', {
    src: 'src/less/app.less',
    includes: ['node_modules/bootstrap/less', 'node_modules/font-awesome/less']
  })

  .addConfigured('js', 'bootstrap')
  .addConfigured('js', 'jquery')
  .addConfigured('js', 'knockout')
  .addConfigured('js', 'knockoutMapping')
//  .addConfigured('js', 'cookie-monster', { shimney: true })
//  .addConfigured('js', 'json', { shimney: true})
  .addConfigured('js', 'lodash', { shimney: true })
  .addConfigured('js', 'hogan', { version: '3.0.2' })
  .addConfigured('js', 'superagent', { shimney: true })
  .addConfigured('js', 'moment')
  .addConfigured('js', 'amplify', { shimney: true})
  .addConfigured('fonts', 'font-awesome')
;

builder.add('fonts', 'titilium')
  .src('Resources/fonts/**/*');

builder.add('js', 'app')
  .src('src/js/app/**/*.js')
  .pipe(builder.dest, 'app')

//builder.add('js', 'lib')
//  .src('src/js/lib/**/*.js')
//  .pipe(builder.dest, 'lib')

builder.add('js', 'webforge-js-components')
  .src('node_modules/webforge-js-components/src/js/Webforge/**/*.js')
  .pipe(builder.dest, 'Webforge')

builder.add('js', 'webforge-js-components-modules')
  .src('node_modules/webforge-js-components/src/js/default-modules/**/*.js')
  .pipe(builder.dest, 'modules')

builder.add('js', 'modules')
  .src('src/js/modules/**/*.js')
  .pipe(builder.dest, 'modules')

// this could be generated from yml files with a gulp task (but not yet)
builder.add('js', 'translations')
  .src('src/php/Webforge/CmsBundle/Resources/js-translations/translations-compiled.json')
  .pipe(builder.dest, 'WebforgeCmsBundle');

builder.add('js', 'i18next')
  .src('src/js/lib/i18next.amd-1.11.0.js')
  .pipe(rename, 'i18next.js');

builder.add('js', 'requirejs-text')
  .src('src/js/lib/requirejs-text.js')
  .pipe(rename, 'text.js');

builder.add('js', 'requirejs-json')
  .src('src/js/lib/requirejs-json.js')
  .pipe(rename, 'json.js');

builder.add('js', 'jquery-nestable')
  .src('src/js/lib/jquery.nestable.js')
  .pipe(rename, 'jquery-nestable.js');

gulp.task('images', ['clean'], function() {
  gulp.src('Resources/img/**/*')
    .pipe(gulp.dest(builder.config.dest+'/img'));
});

var sassOptions = {
  includePaths: [
    './src/scss',
    './node_modules/bootstrap-sass/assets/stylesheets',
    './node_modules/font-awesome/scss'
  ],

  // this is a hack to have some selective components from bootstrap overriden without copying the whole bootstrap.scss with all its @imports
  importer: function(url, prev, done) {
    if (url.indexOf('bootstrap/') === 0) {
      var component = url.substr('boostrap/'.length+1);
      var file = __dirname+'/src/scss/bootstrap/_'+component+'.scss';
      try {
        var stats = fs.lstatSync(file);

        if (stats.isFile()) {
          return { file: file };
        }
      } catch (ex) {
        // file does not exist
      }
    }

    return sass.compiler.NULL; // do nothing
  }
};

gulp.task('sass', function () {
  gulp.src('src/scss/cms.scss')
    .pipe(sass(sassOptions).on('error', sass.logError))
    .pipe(gulp.dest(builder.config.dest+'/css'));
});

gulp.task('build', ['javascript', 'fonts', 'sass', 'images'], function() {});

gulp.task('watch', ['build'], function() {
  gulp.watch('src/scss/**/*.scss', ['sass']);
  gulp.watch('Resources/tpl/**/*.mustache', ['build']);
  gulp.watch('node_modules/webforge-js-components/src/js/**/*', ['build']);
  gulp.watch('node_modules/webforge-js-components/Resources/tpl/**/*', ['build']);
  gulp.watch('src/js/lib/**/*.js', ['build']);
  gulp.watch('src/js/modules/**/*.js', ['build']);
  gulp.watch('src/js/config-*.js', ['build']);
  gulp.watch('src/php/Webforge/CmsBundle/Resources/js-translations/*.json', ['build']);

  gulp.watch('Resources/img/**/*', ['build']);
});

gulp.task('cucumber', function(done) {
  var cukedZombie = require('cuked-zombie');
  var files = [];

  if (argv.filter) {
    files.push('features/'+ argv.filter +'.feature');
  }

  var options = {
    steps: 'tests/js/cucumber/bootstrap.js',
    format: 'pretty'
  }

  if (argv.tags) {
    options.tags = argv.tags;
    files = ['features'];
  }

  var callback = function(error) {
    // if we pass the error to gulp it will show a mega trace
    done();
  };

  cukedZombie.runCucumber(files, options, callback);
});