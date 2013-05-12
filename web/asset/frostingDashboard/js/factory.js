Frosting.Factory = Backbone.Model.extend({ 
    create: function (type) {
      throw {message:'Factory call create for type [' + type + '] you must override function'};
    }
});

Frosting.Factories = {};

Frosting.Factories.Default = Frosting.Factory.extend({
  create: function (type) {
    var args = Array.prototype.slice.call(arguments,1);
    switch(type) {
      case 'action':
        if(Frosting.Actions[args[0].name]) {
          return new Frosting.Actions[args[0].name](args[0],args[1]);
        }
        return new Frosting.Action.Action(args[0],args[1]);
      break;
      case 'actionForm':
        return new Frosting.Action.Form(args[0]);
      break;
    }
  }
});

Frosting.Factories.CompositeFactory = Frosting.Factory.extend({ 
    factories: [],
    
    create: function (type) {
      var object;
      //console.log('Composite factory create ' + type);
      for (var i=0;i<this.factories.length;i++) {
        object = this.factories[i].create.apply(this.factories[i],arguments);
        if(object) {
          return object;
        }
      }
      return null;
    },
    
    appendFactory: function(factory) {
      this.factories.push(factory)
    },
    
    prependFactory: function(factory) {
      this.factories.unshift(factory);
    },
    
    setFactories: function(factories) {
      this.factories = factories;
    },
    
    getFactories: function() {
      return this.factories;
    }
});

