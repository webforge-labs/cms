module.exports = function() {

  require('./setup')(this);

  require('./file-manager-step-definitions').apply(this);
  require('./content-manager-step-definitions').apply(this);

};