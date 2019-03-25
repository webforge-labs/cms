module.exports = function(gulp, rootDir, rootRequire, isDevelopment, options) {
  // put all dependencies here to dependencies not to dev-depenendencies (because other projects will use the builder)
  var rename = require('gulp-rename');
  var sass = require('gulp-sass');
  var wait = require('gulp-wait');
  var sourcemaps = require('gulp-sourcemaps');
  var autoprefixer = require('gulp-autoprefixer');
  var gulpif = require('gulp-if');
  var fs = require('fs');
  var WebforgeBuilder = require('webforge-js-builder');
  var _ = require('lodash');

  var that = this;

  var cmsDir = require('path').resolve(__dirname, '..', '..');

  // we pass "our" require here on purpose (but i have forgotten why)
  this.jsBuilder = new WebforgeBuilder(gulp, { root: rootDir, dest: "public/assets", moduleSearchPaths: [cmsDir] }, require);

  this.autoprefixer = {};
  this.jsNamespaces = [];
  this.mainTasks = ['javascript', 'fonts', 'sass', 'images'];
  this.requirejs = {
    mainConfigFile: cmsDir+'/src/js/config.js', // adds some paths here. But notice: baseUrl will be overriden anyway through this config

    paths: {},

    modules: [
      {
        name: "cms/main",
        include: []
      },
      {
        name: "cms/login"
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
      .addConfigured('fonts', 'font-awesome');
    ;

    that.addJsNamespace('cms', cmsDir+'/src/js/cms');

    builder.add('fonts', 'titilium')
      .src(cmsDir+'/Resources/fonts/**/*');

    that.jsNamespaces.forEach(function(ns) {
      builder.add('js', ns.name)
        .src(ns.dir+'/**/*.*') // using online /**/* will produce dirname. directories in output (.. dont know?)
        .pipe(builder.dest, ns.name)
    });

    // this could be generated from yml files with a gulp task (but not yet)
    builder.add('js', 'translations')
      .src(cmsDir+'/src/php/Webforge/CmsBundle/Resources/js-translations/translations-compiled.json')
      .pipe(builder.dest, 'WebforgeCmsBundle');

    builder.add('js', 'i18next')
      .src(cmsDir+'/src/js/lib/i18next.amd-1.11.0.js')
      .pipe(rename, 'i18next.js');

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

    builder.add('js', 'dropins')
      .src(cmsDir+'/src/js/lib/dropbox-dropins.js');

    builder.add('js', 'marked')
      .src(cmsDir+'/src/js/lib/marked.js');

    builder.add('js', 'urlify')
      .src(cmsDir+'/src/js/lib/urlify.js');

    builder.add('js', 'uikit-src')
      .src(cmsDir+'/src/js/lib/uikit-src/**/*')
      .pipe(builder.dest, 'uikit-src');

    builder.add('js', 'uikit')
      .src(cmsDir+'/src/js/lib/uikit-module.js')
      .pipe(rename, 'uikit.js');

    builder.add('js', 'bootstrap-select')
      .src(builder.resolveModule('bootstrap-select')+'/bootstrap-select.js');

    builder.add('js', 'bootstrap-select-de_DE')
      .src(builder.resolveModule('bootstrap-select')+'/i18n/defaults-de_DE.js')
      .pipe(rename, 'bootstrap-select-de_DE.js');

    builder.add('js', 'bootstrap-markdown')
      .src(builder.resolveModule('bootstrap-markdown')+'/js/bootstrap-markdown.js');

    builder.add('js', 'bluebird')
      .src(builder.resolveModule('bluebird')+'/../browser/'+(isDevelopment ? 'bluebird.js' : 'bluebird.min.js'))
      .pipe(rename, 'bluebird.js');

    builder.add('js', 'etc-cms')
      /* don't wide this too much, the symfony yml config should not be in assets ... */
      .src('etc/cms/**/*.json')
      .pipe(builder.dest, 'etc/cms');

    // rubaxa sortable
    builder.add('js', 'sortablejs')
      .src(builder.resolveModule('sortablejs')+'/'+(isDevelopment ? 'Sortable.js' : 'Sortable.min.js'))
      .pipe(rename, 'sortable.js');

    gulp.task('images', ['clean'], function() {
      return gulp.src(cmsDir+'/Resources/img/**/*')
        .pipe(gulp.dest(builder.config.dest+'/img'));
    });

    var sassOptions = {
      includePaths: [
        cmsDir+'/src/scss',
        builder.resolveModule('bootstrap-sass')+'/../stylesheets',
        builder.resolveModule('font-awesome')+'/scss',
        builder.resolveModule('bootstrap-select')+'/../../sass',
        //builder.resolveModule('bootstrap-markdown')+'/../scss' // does not work, yet (author has no scss in npm package)
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
      },

      outputStyle: isDevelopment ? 'nested' : 'compressed'
    };

    var sassTask = function() {
      try {
        return gulp.src('src/scss/*.scss')
        .pipe(wait(100)) 
        .pipe(gulpif(isDevelopment, sourcemaps.init()))
        .pipe(
           sass(sassOptions)
             .on('error', sass.logError)
         )
        .pipe(autoprefixer(that.autoprefixerOptions))
        .pipe(gulpif(isDevelopment, sourcemaps.write('./')))
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
      gulp.watch(cmsDir+'/src/scss/**/*.scss', ['sass-only']);
      gulp.watch('Resources/tpl/**/*.mustache', ['build']);
      gulp.watch('node_modules/webforge-js-components/src/js/**/*', ['build']);
      gulp.watch('node_modules/webforge-js-components/Resources/tpl/**/*', ['build']);
    
      gulp.watch('src/php/Webforge/CmsBundle/Resources/js-translations/*.json', ['build']);
      gulp.watch('etc/**/*', ['build']);

      gulp.watch('Resources/img/**/*', ['build']);
    });
  };

  this.addJsNamespace = function(name, dir) {
    this.jsNamespaces.push({
      name: name,
      dir: dir
    });
  };

  this.addTabModule = function(name, options) {
    that.requirejs.modules.push(
      _.extend(
        {
          name: name,
          exclude: ['cms/main'] // because these are already loaded from the cms itself
        },
        options
      )
    );
  };

  this.addModule = function(name, options) {
    that.requirejs.modules.push(
      _.extend(
        {
          name: name,
          exclude: [] // we need to exclude common build layers here (but we havent one yet)
        },
        options
      )
    );
  };
};