define(['Webforge/Translator', 'json!WebforgeCmsBundle/translations-compiled.json'], function(Translator, resources) {

  return new Translator('de', resources);

  /*
  grunt.registerMultiTask('convert-translations', function() {
    var _ = require('lodash');

    var options = this.options({
      encoding: 'utf8',
      indent: '  '
    });

    var resources = {};
    var target;
    this.files.forEach(function (files) {
      files.src.forEach(function (src) {
        if (!grunt.file.exists(src)) {
          grunt.log.warn('Source file "' + src + '" not found.');
        } else {
          var translations = grunt.file.readYAML(src, { encoding: options.encoding });
          var match = require('path').basename(src).match(/^(.*)\.([a-zA-Z]+?)\.yml$/);

          if (!match) {
            return grunt.log.fatal('Source file "' + src + '" cannot be parsed as: <catalogue>.<language>.yml');
          }

          var catalogue = match[1];
          var language = match[2];

          if (!resources[language]) {
            resources[language] = {};
          }

          if (!resources[language][catalogue]) {
            resources[language][catalogue] = {};
          }

          resources[language][catalogue] = _.merge(resources[language][catalogue], translations);
        }
      });

      target = files.dest;
    });

    if (!_.isString(target)) {
      return grunt.log.fatal('provide target as a single file to write all catalogues to.');
    }

    grunt.file.write(target, JSON.stringify(resources, null, options.indent));
    grunt.log.writeln('written to: '+target);
  });
  */
});