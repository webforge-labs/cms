module.exports = function() {

  this.Then(/^I see "([^"]*)" as loggedin user$/, function (name) {
    this.getContext().css('[role="username"]:contains("'+name+'")').exists();
  });


}