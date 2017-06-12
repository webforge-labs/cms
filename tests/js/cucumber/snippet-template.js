module.exports = function () { 
 return {
    build: function build (functionName, pattern, parameters, comment) {
      parameters = parameters.slice(0,-1);

      var snippet =
        'this.' + functionName + '(' + pattern + ', function (' + parameters.join(', ') + ') {' + '\n' +
        '});' + '\n';
      return snippet;
    }
  };
};
