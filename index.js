module.exports = {
  Builder: require('./src/js/Builder'),
  TestSuite: require('./src/js/TestSuite'),
  stepDefinitionsFile: function(name) {
    return __dirname+'/tests/js/cms-step-definitions/'+name+'.js';
  }
}