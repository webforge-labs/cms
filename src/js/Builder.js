module.exports = function(gulp, rootDir, rootRequire, isDevelopment) {
  // put all dependencies here to dependencies not to dev-depenendencies (because other projects will use the builder)
  var rename = require('gulp-rename');
  var sass = require('gulp-sass');
  var fs = require('fs');
  var WebforgeBuilder = require('webforge-js-builder');

  var that = this;

  var cmsDir = require('path').resolve(__dirname, '..', '..');

  // we pass "our" require here on purpose (but i have forgotten why)
  this.jsBuilder = new WebforgeBuilder(gulp, { root: rootDir, dest: "www/assets", moduleSearchPaths: [cmsDir] }, require);

  this.jsNamespaces = [];
  this.mainTasks = ['javascript', 'fonts', 'sass', 'images'];
  this.requirejs = {
    mainConfigFile: cmsDir+'/src/js/config.js', // adds some paths here. But notice: baseUrl will be overriden anyway through this config

    paths: [],
    modules: [
      {
        name: "cms/main"
      },
      {
        name: "cms/login"
      },
      {
        name: "cms/navigation"
      }
    ]
  };

  this.configure = function() {
    var builder = that.jsBuilder;

    builder
      .registerTask('clean')
      .registerTask('fonts')
      .registerTask('requirejs-config', { file: isDevelopment ? cmsDir+'/src/js/config-dev.js' : cmsDir+'/src/js/config.js'} )
      .registerTask('javascript', { combine: !isDevelopment, requirejs: that.requirejs })
    //  .registerTask('templates', { path: 'cms/Resources/tpl' })
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

    builder.add('js', 'cms')
      .src(cmsDir+'/src/js/cms/**/*.js')
      .pipe(builder.dest, 'cms')

    that.jsNamespaces.forEach(function(ns) {
      builder.add('js', ns.name)
        .src(ns.dir+'/**/*.js')
        .pipe(builder.dest, ns.name)
    });

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

    builder.add('js', 'knockout-dragdrop')
      .src(builder.resolveModule('knockout-dragdrop')+'/knockout.dragdrop.js')
      .pipe(rename, 'knockout-dragdrop.js');

    builder.add('js', 'jquery-nestable')
      .src(cmsDir+'/src/js/lib/jquery.nestable.js')
      .pipe(rename, 'jquery-nestable.js');
    
    builder.add('js', 'notify')
      .src(builder.resolveModule('bootstrap-notify')+'/bootstrap-notify.js')
      .pipe(rename, 'bootstrap-notify.js');

    builder.add('js', 'datepicker')
      .src(builder.resolveModule('bootstrap-datepicker')+'/bootstrap-datepicker.js')
      .pipe(rename, 'bootstrap-datepicker.js');

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
          var component = url.substr('bootstrap/'.length+1);
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

    var sassTask = function() {
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
    };

    gulp.task('sass', ['clean'], sassTask);
    gulp.task('sass-only', [], sassTask);

    gulp.task('build', this.mainTasks, function() {});

    gulp.task('watch', ['build'], function() {
      gulp.watch('src/scss/**/*.scss', ['sass-only']);
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

  this.addJsNamespace = function(name, dir) {
    this.jsNamespaces.push({
      name: name,
      dir: dir
    });
  }
};