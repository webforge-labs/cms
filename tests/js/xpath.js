var XPath = function () {
  return this;
};

var XPathBuilder = require('xpath-builder');
var x = XPathBuilder.dsl();

var Query = function() {
  var that = this;
  that.expression = XPathBuilder.dsl();

  that.find = function(tag) {
    // .class
    if (tag.indexOf('.') === 0) {
      that.expression = that.expression.descendant('*').where(x.concat(" ", x.attr('class'), " ").contains(" "+tag.substr(1)+" "));

    // #id
    } else if (tag.indexOf('#') === 0) {
      that.expression = that.expression.descendant('*').where(x.attr("id").equals(tag.substr(1)));

    // tag.class
    } else if (tag.match(/^\w+\.\w/)) {
      var split = tag.split(/\./);
      that.expression = that.expression.descendant(split[0]).where(x.concat(" ", x.attr('class'), " ").contains(" "+split[1]+" "));

    // tag
    } else {
      that.expression = that.expression.descendant(tag);
    }

    return that;
  };

  that.contains = function(text) {
    that.expression = that.expression.where(x.current().normalize().contains(text));

    return that;
  };

  that.parent = function(tagName) {
    that.expression = that.expression.axis('parent', tagName);

    return that;
  };

  that.role = function(role) {
    that.expression = that.expression.where(x.attr('role').equals(role));

    return that;
  };

  that.findRole = function(role) {
    return that.find('*').role(role);
  };

  that.tap = function(withExpression) {
    that.expression = withExpression.call(that.expression, that.expression);

    return that;
  };

  that.where = function(arg) {
    that.expression = that.expression.where(arg);

    return that;
  };

  that.toString = function() {
    return that.expression.toString();
  };
};

/*
XPath.prototype.whereClass = function(className) {
  return 
};

XPath.prototype.descendantWithClass = function(tag, className) {
  return this.descendant(tag).where(this.concat(" ", this.attr('class'), " ").contains(" "+className+" "));
}

XPath.prototype.whereRole = function(role) {
  return this.where(this.attr('role').equals(role));
};

XPath.prototype.whereContains = function(text) {
  return this.where(this.current().normalize().contains(text));
};
*/
module.exports = function(tag) {
  var query = new Query();

  if (tag !== undefined) {
    return query.find(tag);
  }

  return query;
};

module.exports.x = x;
module.exports.Query = Query;