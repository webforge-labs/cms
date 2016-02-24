module.exports = function(gulp, rootDir, rootRequire, isDevelopment) {
  var rename = require('gulp-rename');
  var sass = require('gulp-sass');
  var fs = require('fs');
  var WebforgeBuilder = require('webforge-js-builder');

  var that = this;

  var cmsDir = require('path').resolve(__dirname, '..', '..');

  this.jsBuilder = new WebforgeBuilder(gulp, { root: rootDir, dest: "www/assets", moduleSearchPaths: [cmsDir] }, require);

  this.configure = function() {
    var builder = that.jsBuilder;

    builder
      .registerTask('clean')
      .registerTask('fonts')
      .registerTask('requirejs-config', { file: isDevelopment ? cmsDir+'/src/js/config-dev.js' : cmsDir+'/src/js/config.js'} )
      .registerTask('javascript', {
        combine: !isDevelopment,
        requirejs: {
          
          mainConfigFile: cmsDir+'/src/js/config.js', // adds some paths here. But notice: baseUrl will be overriden anyway through this config

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
      .addConfigured('js', 'bootstrap')
      .addConfigured('js', 'jquery')
      .addConfigured('js', 'knockout', { debug: isDevelopment })
      .addConfigured('js', 'knockoutMapping')
    //  .addConfigured('js', 'cookie-monster', { shimney: true })
    //  .addConfigured('js', 'json', { shimney: true})
      .addConfigured('js', 'lodash', { shimney: true })
      .addConfigured('js', 'hogan', { version: '3.0.2' })
      .addConfigured('js', 'superagent', { shimney: true })
      .addConfigured('js', 'moment')
      .addConfigured('js', 'amplify', { shimney: true})
      .addConfigured('js', 'webforge-js-components')
      .addConfigured('js', 'knockout-collection')
      .addConfigured('js', 'requirejs-text')
      .addConfigured('js', 'requirejs-json')
    ;

    builder.addConfigured('fonts', 'font-awesome');

    builder.add('fonts', 'titilium')
      .src(cmsDir+'/Resources/fonts/**/*');

    builder.add('js', 'app')
      .src(cmsDir+'/src/js/app/**/*.js')
      .pipe(builder.dest, 'app')

    builder.add('js', 'modules')
      .src(cmsDir+'/src/js/modules/**/*.js')
      .pipe(builder.dest, 'modules')

    // this could be generated from yml files with a gulp task (but not yet)
    builder.add('js', 'translations')
      .src(cmsDir+'/src/php/Webforge/CmsBundle/Resources/js-translations/translations-compiled.json')
      .pipe(builder.dest, 'WebforgeCmsBundle');

    builder.add('js', 'i18next')
      .src(cmsDir+'/src/js/lib/i18next.amd-1.11.0.js')
      .pipe(rename, 'i18next.js');

    builder.add('js', 'requirejs-text')
      .src(cmsDir+'/src/js/lib/requirejs-text.js')
      .pipe(rename, 'text.js');

    builder.add('js', 'requirejs-json')
      .src(cmsDir+'/src/js/lib/requirejs-json.js')
      .pipe(rename, 'json.js');

    builder.add('js', 'jquery-nestable')
      .src(cmsDir+'/src/js/lib/jquery.nestable.js')
      .pipe(rename, 'jquery-nestable.js');

    gulp.task('images', ['clean'], function() {
      return gulp.src(cmsDir+'/Resources/img/**/*')
        .pipe(gulp.dest(builder.config.dest+'/img'));
    });

    var sassOptions = {
      includePaths: [
        cmsDir+'/src/scss',
        builder.resolveModule('bootstrap-sass')+'/../stylesheets',
        builder.resolveModule('font-awesome')+'/scss'
      ],

      // this is a hack to have some selective components from bootstrap overriden without copying the whole bootstrap.scss with all its @imports
      importer: function(url, prev, done) {
        if (url.indexOf('bootstrap/') === 0) {
          var component = url.substr('boostrap/'.length+1);
          var file = cmsDir+'/src/scss/bootstrap/_'+component+'.scss';
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

    gulp.task('sass', ['clean'], function() {
      try {
        return gulp.src('src/scss/*.scss')
        .pipe(
           sass(sassOptions)
             .on('error', sass.logError)
         )
        .pipe(gulp.dest(builder.config.dest+'/css'))
      } catch (exc) {
        console.log(exc);
      }
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
  };
};