var Frosting = {};
_.extend(Frosting, Backbone.Events);
window.frosting = true; // false to disable frosting initialisation at startup

Frosting.Controller = Backbone.Model.extend({ 
    factory: null,
    responseAdapter: null,
    
    initialize: function(options) {
        this.requests = new Frosting.Requests();
        if(!this.get('menuContainer')) {
            this.set({'menuContainer' : $('#topbar')});
        }
        if(!this.get('mainContainer')) {
            this.set({'mainContainer' : $('#main')});
        }
        
        this.userFeedback = new Frosting.UserFeedback({controller:this});
    },
    
    load: function() {
      this.getFactory().create('action',{ name: 'applications' }).prompt();
    },
    
    getFactory: function() {
      if(this.factory == null) {
        this.factory = new Frosting.Factories.CompositeFactory();
        this.factory.appendFactory(new Frosting.Factories.Default());
      }
      
      return this.factory;
    },
    
    setResponseAdapter: function(adapter) {
      this.responseAdapter = adapter;
    },
    
    getResponseAdapter: function() {
      return this.responseAdapter;
    },
    
    getActionInspector: function() {
      if(this.actionInspector == null) {
        this.actionInspector = new Frosting.Action.Inspector();
      }
     
      return this.actionInspector;
    }
});

Frosting.Menu = Backbone.View.extend({
    menu: [],
    actions: {},
    initialize: function(tree) {
        this.container = App.Frosting.get('menuContainer');
        this.buildMenu(tree);
        var self = this;
        $('.frosting_action').live('click', function(e) { self.loadAction(e, self); });
        $('.application_selector a').live('click', function(e) { self.selectApplication(e, self); });

        this.paginateInterval = 0;
        $('.paginate_down, .paginate_up').live('mousedown', function(e) {
            if($(e.currentTarget).hasClass('paginate_down')) var direction = 'down';
            else var direction = 'up';
            self.moveDropdown(e, self, direction);
            self.paginateInterval = setInterval(function() { self.moveDropdown(e, self, direction); },50);
        }).live('mouseup mouseleave', function(e) {
            clearInterval(self.paginateInterval);
            $(e.currentTarget).parents('.frosting_menu').addClass('open');
        });
    },
    buildMenu: function(tree) {
        var self = this, previous = null;
        _.each(tree, function(menu) {
            menu.action.application = menu.application;
            if(!self.actions[menu.action.name]) {
                self.actions[menu.action.name] = menu.action;
            } else {
                if(_.isArray(self.actions[menu.action.name])) {
                    self.actions[menu.action.name].push(menu.action);
                } else {
                    self.actions[menu.action.name] = [self.actions[menu.action.name], menu.action];
                }
            }

            var positionTokens = _.compact(menu.position.split('/'));
            menu.id = positionTokens.join('-') + menu.label.toID(true);
            //This is just for root menu
            if(!positionTokens.length) {
                menu.root = true;
                self.menu.push(menu);
                return;
            }
            
            _.each(positionTokens, function(token, index) {
              if(index==0) previous = self.menu;
              var present = _.find(previous, function(node){
                return node.label == token;
              });

              if(!present) {
                var node =  {
                  'label': token, 
                  'childrens': index==(positionTokens.length-1) ? [menu] : [],
                  'id': positionTokens.slice(0,index +1).join('-'),
                  'root': index==0
                };

                previous.push(node);
                previous = node.childrens;
                return;
              }

              if(index!=(positionTokens.length-1)) {
                previous = present.childrens;
                return;
              }

              var exist = _.find(present.childrens, function(action) {
                return action.id == menu.id;
              });
              if(!exist) present.childrens.push(menu);
            });
        });
        this.displayMenu(null, this.menu);
        this.checkDropdownLength();
        if(Frosting.toExecute) {
            this.inspectAction(Frosting.toExecute);
            Frosting.toExecute = false;
        }
    },
    buildApplicationSelector: function() {
        Frosting.currentApplication = Frosting.applications[0];
        var selector = $('<div class="frosting_menu application_selector btn-group right animated"><button class="btn button">'+Frosting.currentApplication.name+'</button><button class="btn button dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button><ul class="dropdown-menu"></ul></div>');
        _.each(Frosting.applications, function(app) {
            $(selector).find('ul').append('<li><a href="'+app.name+'">'+app.name+'</a></li>');
        });
        this.container.append(selector);
    },
    selectApplication: function(e, self) {
        e.preventDefault();
        Frosting.currentApplication = _.find(Frosting.applications, function(app) { return app.name == $(e.currentTarget).attr('href'); });
        $('.application_selector').find('.btn').first().html(Frosting.currentApplication.name);
    },
    displayMenu: function(parent, items, node) {
        var self = this;
        _.each(items, function(item) { self.displayItem(parent, item, node); });
    },
    displayItem: function(parent, item, elm, self) {
        if(item.root) {
            if(item.childrens) {
                var node = $('<li class="dropdown frosting_menu animated"><a data-toggle="dropdown" class="button dropdown-toggle" href="#">'+item.label+' <b class="caret"></b></a><ul id="tab-'+item.id+'" class="dropdown-menu dropdown-root"></ul></li>');
            } else {
                if(_.isArray(item.action)) var name = item.action[0].name; else var name = item.action.name;
                var node = $('<li class="dropdown frosting_menu animated"><a data-app="'+item.application+'" class="button frosting_action" href="'+name+'">'+item.label+'</a></li>');
            }
            this.container.append(node);
        } else {
            if(item.childrens) {
                var node = $('<li class="dropdown-submenu frosting_menu animated"><a href="#">'+item.label+'</a><ul id="tab-'+item.id+'" class="dropdown-menu"></ul></li>');
            } else {
                if(_.isArray(item.action)) var name = item.action[0].name; else var name = item.action.name;
                var node = $('<li class="frosting_menu animated"><a data-app="'+item.application+'" class="frosting_action" href="'+name+'">'+item.label+'</a></li>');
            }
            $('#tab-' + parent.id).append(node);
        }
        if(item.childrens) this.displayMenu(item, item.childrens, node);
    },
    checkDropdownLength: function() {
        var self = this;
        $('.dropdown-root').each(function(iteration, dropdown) {
            var dropdown = $(dropdown);
            self.paginateDropdown(dropdown);
        });
    },
    paginateDropdown: function(dropdown) {
        var perPage = 20,
            li = dropdown.children('li');
        if(li.length>perPage) {
            li.hide();
            if(!dropdown.attr('data-position')) $(dropdown).attr('data-position', 0);
            
            if(dropdown.children('.paginate').length==0) {
                dropdown.append('<div class="paginate"><button class="btn btn-mini paginate_down" type="button"><i class="icon-caret-down"></i></button><button class="btn btn-mini paginate_up" type="button"><i class="icon-caret-up"></i></button></div>');
            }

            if((parseInt(dropdown.attr('data-position'),10)+perPage)>=li.length) {
                $('.paginate_down').hide();
                clearInterval(this.paginateInterval);
            } else $('.paginate_down').show();

            if(parseInt(dropdown.attr('data-position'),10)==0) {
                $('.paginate_up').hide();
                clearInterval(this.paginateInterval);
            } else $('.paginate_up').show();

            var currentPage = li.splice($(dropdown).attr('data-position'), perPage);
            _.each(currentPage, function(elem) { $(elem).show(); });
        }
    },
    moveDropdown: function(e, self, direction) {
        var dropdown = $(e.currentTarget).parents('.dropdown-menu');
        if(direction=='up') dropdown.attr('data-position', parseInt(dropdown.attr('data-position'), 10) - 1);
        else dropdown.attr('data-position', parseInt(dropdown.attr('data-position'), 10) + 1);
        self.paginateDropdown(dropdown);
        dropdown.parents('.frosting_menu').addClass('open');
        return false;
    },
    addActions: function(tree, application) {
        this.buildMenu(tree, application);
    },
    loadAction: function(e, self) {
        e.preventDefault();
        self.inspectAction($(e.currentTarget).attr('href'));
    },
    inspectAction: function(action, currentApplication) {
        if(!action.direct) var action = this.actions[action];
        if(_.isArray(action)) {
            if(!currentApplication) var currentApplication = Frosting.currentApplication.name;
            var current = _.find(action, function(act) { return act.application == currentApplication; });
            if(current) {
                App.Frosting.getActionInspector().inspect(current, function(inspection) {
                    App.Frosting.getFactory().create('action', current, inspection).prompt();
                });
                App.router.navigate("!f/" + current.name, {trigger: false});
            } else {
                this.applicationsChangePrompt(action);
            }
        } else {
            App.Frosting.getActionInspector().inspect(action, function(inspection) {
                App.Frosting.getFactory().create('action', action, inspection).prompt();
            });
            App.router.navigate("!f/" + action.name, {trigger: false});
        }
    },
    applicationsChangePrompt: function(action) {
        var form = $('<form><div class="row well frosting_form"><h3><i class="icon-warning-sign"></i> Not available</h3> Sorry this action is not available on the application you have selected, please select another one</div></form>'),
            selector = $('<div class="frosting_menu prompt_application_selector btn-group right "><button class="btn button">Select an application</button><button class="btn button dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button><ul class="dropdown-menu"></ul></div>'),
            self = this;
        _.each(action, function(app) {
            $(selector).find('ul').append('<li><a href="'+app.application+'">'+app.application+'</a></li>');
        });
        form.find('.row').append(selector);
        $('.prompt_application_selector a').live('click', function(e) {
            self.inspectAction(action[0].name, $(e.currentTarget).attr('href'));
            return false;
        });
        $(".modal-body").html(form);
        $('#modal').modal({'backdrop': true}).show();
    }
});


(function($) {
  return $.fn.serializeObject = function() {
    var json, patterns, push_counters,
      _this = this;
    json = {};
    push_counters = {};
    patterns = {
      validate: /^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
      key: /[a-zA-Z0-9_]+|(?=\[\])/g,
      push: /^$/,
      fixed: /^\d+$/,
      named: /^[a-zA-Z0-9_]+$/
    };
    this.build = function(base, key, value) {
      base[key] = value;
      return base;
    };
    this.push_counter = function(key) {
      if (push_counters[key] === void 0) {
        push_counters[key] = 0;
      }
      return push_counters[key]++;
    };
    $.each($(this).serializeArray(), function(i, elem) {
      var k, keys, merge, re, reverse_key;
      if (!patterns.validate.test(elem.name)) {
        return;
      }
      keys = elem.name.match(patterns.key);
      try {
      format_element(elem);
      } catch(e) {
        console.log(e);
      }
      merge = elem.value;
      reverse_key = elem.name;
      if(!reverse_key) {
        return json;
      }
      while ((k = keys.pop()) !== void 0) {
        if (patterns.push.test(k)) {
          re = new RegExp("\\[" + k + "\\]$");
          reverse_key = reverse_key.replace(re, '');
          merge = _this.build([], _this.push_counter(reverse_key), merge);
        } else if (patterns.fixed.test(k)) {
          merge = _this.build([], k, merge);
        } else if (patterns.named.test(k)) {
          merge = _this.build({}, k, merge);
        }
      }
      return json = $.extend(true, json, merge);
    });
    return json;
  };
})(jQuery);

function format_element(elem)
{
  //We escape the name to suppor name like -> values[name]
  var input = $("[name=" + elem.name.replace(/[!"#$%&'()*+,.\/:;<=>?@\[\\\]^`{|}~]/g, '\\$&') + "]");
                
  /*if(!input.attr('name')) {
    return;
  }*/

  var value = elem.value;
                
  if(input.attr('data-type')) {
    switch(true) {
      case input.attr('data-type').indexOf('[]') != -1:
      case input.attr('data-type') == 'array':
        value = $.grep(
          $.map(
            value.split(','), 
            $.trim
          ),
          function(value) {
            return value.length;
          }
        );
        break;
      case input.attr('data-type') == 'boolean' && input.attr('type') != 'checkbox':
        value = Boolean(value);
        break;
      case input.attr('data-type') == 'boolean':
        value = Boolean(input.attr('checked'));
        break;
    }
  }
         
  elem.value = value;
         
  if(input.attr('data-default') && (input.attr('data-default') == value)) {
    elem.name = null;
  }
}