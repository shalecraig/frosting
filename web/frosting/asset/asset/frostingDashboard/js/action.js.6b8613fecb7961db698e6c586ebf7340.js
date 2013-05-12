Frosting.Action = {};
  
Frosting.Action.Inspector = Backbone.Model.extend( {
    inspections: [],
    inspect: function(actionData,callback) {
      var self = this;
      if(this.inspections[actionData.inspectUrl]) {
        callback(this.inspections[actionData.inspectUrl]);
        return;
      }
      
      if(!actionData.inspectUrl) {
        this.inspections[actionData.inspectUrl] = new Frosting.Action.Inspection({});
        callback(this.inspections[actionData.inspectUrl]);
        return;
      }
      
      App.Frosting.requests.get(
        actionData.inspectUrl, 
        {'legend': 'Inspecting the ' + actionData.name + ' action'}
      ).done(function(response) {
        var inspection = new Frosting.Action.Inspection(response.getData());
        if(!inspection.getNoCache()) {
          self.inspections[actionData.inspectUrl] = inspection;
        }      
        callback(inspection);
        return;
      });
    }
})  

Frosting.Action.Inspection = Backbone.Model.extend( {
    parameters: [],
    returnType: null,
    initialize: function(data) {
      _.extend(this, data);
      if(!this.metaData) {
        this.metaData = {};
      }
    },
    getParameters: function() {
      return this.parameters;
    },
    getMetaData: function() {
      return this.parameters[0].metaData;
    },   
    getReturnType: function() {
      return this.returnType;
    },
    getAllowBatch: function() {
      return this.metaData.allowBatch;
    },
    getNoCache: function() {
      return Boolean(this.metaData.noCache);
    }
});
  
Frosting.Action.Form = Backbone.View.extend({
  render: function() {
      var self = this;
      var action = this.getAction();
      var form = $('\
  <form>\n\
    <div class="row well frosting_form">\n\
      <div class="frosting_form_header">\n\
        <h3><i class="icon-wrench"></i> '+action.label+' action</h3>\n\
      </div>\n\
      <div class="frosting_form_body" />\n\
      <div class="frostin_form_footer" />\n\
        <hr />\n\
        <input type="submit" class="btn btn-success btn-large icon-ok-sign" value="Execute">\n\
      </div>\n\
    </div>\n\
  </form>');
    
      var span;
      _.each(this.createInputs(action.getInspection().getParameters()), function(input,iteration) {
        if(iteration%5==0) {
          span = $('<div class="span3" />');
          form.find('.frosting_form_body').append(span);
        }
        span.append(input);
      });

      form.validate({
          submitHandler: function(e) {
            action.execute(form.serializeObject());
            return false;
          }
      });

      $(".modal-body").html(form);
      $('#modal').modal({'backdrop': true}).show();

      return this;
  },
  //Convert a string 
  toArray: function(value,separator) {
    separator = separator || ',';
    return $.grep(
      $.map(
        value.split(separator), 
        $.trim
      ),
      function(value) {
        return value.length;
      }
    );
  },
  createInputs: function (parameters) {
    var inputs = [];
    var self = this;
    _.each(parameters, function(parameter) {
        if(!parameter.description) parameter.description = parameter.name;
        var div = $('<div class="frosting_input"></div>');
        div.append('<label>'+parameter.description+'</label>');
        switch(true) {
          case parameter.type=='boolean':
            var param = $('<input type="checkbox" name="'+parameter.name+'">');
            if(parameter.default) {
              param.attr('checked', 'checked');
            }
          break;

          case parameter.type=='array' && parameter.metaData.keys instanceof Array:
            var keys = parameter.metaData.keys;
            var subInputs = self.createInputs(parameter.metaData.keys);
            _.each(subInputs, function(subInput) {
              var input = subInput.children("*[name]")[0];
              input.name = parameter.name + '[' + input.name + ']';
            });
            /*var param = $('<fieldset><legend>'+parameter.description+'</legend></fieldset>');
            _.each(subInputs,function(input) {
              param.append(input);
            });*/
            _.each(subInputs, function(subInput) {
              inputs.push(subInput);
            });
            return;
          break;

          case parameter.type=='array':
            var defaultValue = parameter.default || '';
            var param = $('<textarea name="'+parameter.name+'">' + defaultValue + '</textarea>');
          break;
          default:
            var param = $('<input type="text" name="'+parameter.name+'">');
            if(parameter.default) {
              param.attr('value', parameter.default);
            }
          break;
        }

        if(parameter.required) param.addClass('required');
        if(parameter.default) {
          param.attr('data-default', parameter.default);
        }
        param.attr('data-type', parameter.type);
        switch(parameter.type) {
            case 'int': param.addClass('digits'); break;
            case 'float': param.addClass('number'); break;
        }
        div.append(param);
        inputs.push(div);
    });
    
    return inputs;
  },
  getAction: function() {
    return this.model;
  }
});  
 
Frosting.Action.SearchForm = Frosting.Action.Form.extend({
    tagName: 'form',
    className: 'form-inline frosting_form frosting_search_form',
    events: {
      'click .icon-plus-sign' : 'addSearchLine',
      'click .icon-minus-sign' : 'removeSearchLine'
    },
    render: function() {
      var self = this;
      this.$el.html('<div class="lines"></div>');
      this.$el.append('<div class="begin_search"><i class="icon-plus-sign"></i></div> <input type="submit" class="btn btn-success icon-ok-sign" value="Search">');

      if(this.model.searchResult.getFilters() && this.model.searchResult.getFilters().length > 0) {
        _.each(this.model.searchResult.getFilters(), function(filter) {
          self.addSearchLine(null, filter);
        });
        this.$el.find('.begin_search').hide();
      }

      this.$el.validate({
          submitHandler: function(e) {
            var criterias = {
              'pageNumber' : self.model.searchResult.getCurrentPage(), 
              'amountPerPage' :self.model.searchResult.getAmountPerPage(),
              'filters' : self.getFilters()
            };
            if(self.model.parent.getOrders().length!=0) {
                criterias.orderBy = self.model.parent.getOrders();
            }
            self.model.action.execute({'searchCriteria' : criterias});
            return false;
          }
      });

      $('#frosting_list_content').before(this.$el);
      return this;
    },
    getFilters: function() {
      var filters = [];

      _.each(this.$el.find('.search_line'), function(line) {
        var line = $(line),
            value = line.find('input[type=text]').val();
        if(line.find('select[name=operator]').val()=='in' || line.find('select[name=operator]').val()=='nin') {
          value = value.split(',');
        }
        filters.push({'fieldName': line.find('select[name=fieldName]').val(), 'value': value, 'operator': line.find('select[name=operator]').val()});
      });

      return filters;
    },
    addSearchLine: function(event, filter) {
      var line = $('<div class="search_line"></div>'),
          select = $('<select name="fieldName"></select>'),
          operator = $('<select name="operator"></select>'),
          operators = [
            {'name': 'Equal (=)', 'value': 'eq'},
            {'name': 'Not equal (!=)', 'value': 'ne'},
            {'name': 'Lower than (<)', 'value': 'lt'},
            {'name': 'Lower or equal (<=)', 'value': 'lte'},
            {'name': 'Greater than (>)', 'value': 'gt'},
            {'name': 'Greater or equal (>=)', 'value': 'gte'},
            {'name': 'In array of value', 'value': 'in'},
            {'name': 'Not in array', 'value': 'nin'},
            {'name': 'Like', 'value': 'like'}
          ];

      _.each(this.model.searchCriterias.getCriterias(), function(criteria) {
        if(filter && filter.fieldName==criteria.name) {
          var selected = 'selected';
        } else {
          var selected = '';
        }
        select.append('<option value="'+criteria.name+'" '+selected+'>'+criteria.name+'</option>');
      });
      line.append(select);

      _.each(operators, function(op) {
          if(filter && filter.operator==op.value) {
            var selected = 'selected';
          } else {
            var selected = '';
          }
          operator.append('<option value="'+op.value+'" '+selected+'>'+op.name+'</option>');
      });
      line.append(operator);

      if(filter) {
        var value = filter.value;
      } else {
        var value = '';
      }
      line.append('<input type="text" name="value" value="'+value+'" /> <i class="icon-plus-sign"></i> <i class="icon-minus-sign"></i>');
      $('.begin_search').hide();

      this.$el.find('.lines').append(line);
    },
    removeSearchLine: function(e) {
      $(e.currentTarget).parent('.search_line').remove();
      if(this.$el.find('.search_line').length==0) {
        $('.begin_search').show();
      }
    }
});

Frosting.Action.Action = Backbone.Model.extend({
    initialize: function(action, inspection) {
        _.extend(this,action);
        if(!(inspection instanceof Frosting.Action.Inspection)) {
          inspection = new Frosting.Action.Inspection(inspection);
        }
        this.inspection = inspection;
    },
    prompt: function() {
        if(this.getInspection().getReturnType() && this.getInspection().getReturnType().indexOf("ISearchResult") != -1) {
          this.execute({'searchCriteria' : {
            'pageNumber' : 0, 
            'amountPerPage' : this.inspection.getMetaData().amountPerPage.default
          }});
        } else if(this.inspection.getParameters().length == 0 && this.inspection.getParameters().getReturnType==null) {
            this.execute({});
        } else {
            App.Frosting.getFactory().create('actionForm',{model:this}).render();
        }
    },
    execute: function(parameters) {
      var self = this;
      var options = {
        'legend': 'Executing the ' + self.getName() + ' action',
        'params': parameters
      };
      if(_.isString(this.url)) {
        App.Frosting.requests.get(
          this.url, 
          options
        ).done(function(response) {
          self.handleResponse(response);
        });
      } else {
        App.Frosting.requests.get(
          this.url.shift(), 
          options
        ).done(function(response) {
          self.handleResponse(response);
          if(self.url.length>0) self.execute(parameters);
        });
      }
    },
    getUrl: function() {
        return this.url;
    },
    getName: function() {
        return this.name;
    },
    handleResponse: function(response) {
      if(this.getInspection().getReturnType() && this.getInspection().getReturnType().indexOf("[]") != -1) {
        new Frosting.Views.ModelList({models:response.getData()}).render();
        var noConfirmation = true;
      }
      
      if(this.getInspection().getReturnType() && this.getInspection().getReturnType().indexOf("ISearchResult") != -1) {
        new Frosting.Views.SearchResult({
          action : this,
          searchResult: new Frosting.Model.SearchResult(response.getData()),
          searchCriterias : new Frosting.Model.SearchCriterias(this.getInspection().getParameters()[0])
        }).render();
        var noConfirmation = true;
      } 
      
      $('#modal').modal('hide');
    },
    getInspection: function() {
      return this.inspection;
    }
});

Frosting.Actions = {};

Frosting.Actions.applications = Frosting.Action.Action.extend({
    initialize: function () {
        Frosting.Action.Action.prototype.initialize.call(
            this,
            { url: '/applications', name: 'applications', hideSuccess: true },
            { parameters: [] }
        );
    },
    handleResponse: function(response) {
      Frosting.applications = [];
      _.each(response.getData(), function(application) {
        Frosting.applications.push(application);
        if(application._actions) {
          _.each(application._actions, function(action) {
            if(action.name && action.name=='loadMenu') {
              App.Frosting.getFactory().create('action', action, {'application': application.name}).prompt();
            }
          });
        }
      });
    }
});

Frosting.Actions.loadMenu =  Frosting.Action.Action.extend({
  handleResponse: function(response) {
    var self = this;
    if(!Frosting.menus) Frosting.menus = {'app': 0, 'menus': []};
    _.each(response.getData(), function(action) { action.application = self.inspection.application; });
    Frosting.menus.menus.push(response.getData());
    Frosting.menus.app++;
    if(Frosting.menus.app==Frosting.applications.length) {
      App.Frosting.menu = new Frosting.Menu(_.flatten(Frosting.menus.menus));
      App.Frosting.menu.buildApplicationSelector();
    }
    /*if(!App.Frosting.menu) {
      App.Frosting.menu = new Frosting.Menu(response.getData(), this.inspection.application);
      App.Frosting.menu.buildApplicationSelector();
    } else {
      App.Frosting.menu.addActions(response.getData(), this.inspection.application);
    }*/
  }
});