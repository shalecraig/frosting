Frosting.Views = {};

Frosting.Views.ModelList = Backbone.View.extend({
    id : 'frosting_list',
    events : {
        'click .frosting_list_action' : 'executeAction',
        'click .frosting_batch_action' : 'executeBatchAction',
        'click .select-all' : 'selectAll'
    },
    properties: {},
    actions: {},
    hasAction: false,
    
    initialize: function(options) {
      Backbone.View.prototype.initialize.call(this,options);
      this.buildProperties();
    },
    getModels: function() {
      return this.options.models;
    },
    buildProperties: function() {
        this.properties = {};
        var self = this;
        /* list the properties and populate the header of the table */
        _.each(this.getModels(), function(item) {
            _.each(_.keys(item), function(property) {
                switch(true) {
                    case property!='_actions' && !self.properties[property]:
                        self.properties[property] = property;
                    break;
                    case property=='_actions' && item[property].length > 0:
                        self.hasAction = true;
                    break;
                }
            });
        });
    },
    buildHeader: function(table) {
        var self = this,
            header = $('<tr class="header"></tr>');
        if(this.hasAction) {
          header.append('<th class="select-all"><i class="icon-check"></i></th>');
        }
        _.each(this.properties, function(property) {
            if(self.isOrderable(property)) {
                var th = $('<th><i class="icon-caret-down" data-order="asc" data-name="'+property+'"></i><i class="icon-caret-up" data-order="desc" data-name="'+property+'"></i> '+property+'</th>');
                if(self.isOrdered(property) && self.isOrdered(property).direction=='asc') th.find('.icon-caret-down').addClass('selectedOrder');
                if(self.isOrdered(property) && self.isOrdered(property).direction=='desc') th.find('.icon-caret-up').addClass('selectedOrder');
                header.append(th);
            } else {
                header.append('<th>' + property + '</th>');
            }
        });
        if(this.hasAction) {
            header.append('<th><i class="icon-cog"></i> Actions</th>');
        }
        table.find('thead').append(header);
    },
    buildFooter: function(table) {
        var self = this,
            hasAction = false,
            footer = $('<tr></tr>'),
            td = $('<td colspan="'+(_.keys(this.properties).length+1)+'"> <i class="icon-chevron-left"></i>Batch actions <i class="icon-cogs"></i></td>');
        _.each(_.keys(this.actions).reverse(), function(action) {
            if(self.actions[action].allowBatch) {
              td.prepend('<a class="btn btn-mini frosting_batch_action" data-name="'+action+'" href="'+self.actions[action].inspectUrl+'"><i class="icon-chevron-right"></i> '+action+'</a>');
              hasAction = true;
            }
        });
        if(!hasAction) {
          return;
        }
        footer.append('<td></td>').append(td);
        table.find('tbody').append(footer);
    },
    isOrderable: function(property) {
        if(!this.options.searchCriterias) {
            return false;
        } else {
            if(_.find(this.options.searchCriterias.getCriterias(), function(crit){ return crit.name == property; })) {
                return true;
            } else {
                return false;
            }
        }
    },
    isOrdered: function(property) {
        if(!this.options.searchResult) {
            return false;
        } else {
            return _.find(this.options.searchResult.getOrders(), function(crit){ return crit.fieldName == property; });
        }
    },
    render: function () {
        var self = this;
        var action = {};
        var page = $('<div><h2><i class="icon-list"></i> '+ action.label +'</h2><div id="frosting_list_content"></div></div>');
        var table = $('<table class="frosting_table table table-bordered table-striped"><thead></thead><tbody></tbody></table>');
      
        this.buildHeader(table);

        /* add the items and their actions in the content of the table */
        _.each(this.getModels(), function(item) {
            var line = $('<tr class="line"></tr>');
            if(self.hasAction) {
              line.append('<td><input class="selection" name="ids[]" value="'+item.id+'" type="checkbox"></td>');
            }
            _.each(self.properties, function(property) {
                if(!item[property]) {
                  line.append('<td class="empty"><i class="icon-remove"></i></td>');
                  return;
                } 
                
                if(_.isObject(item[property])) {
                    var objectAsString = JSON.stringify(item[property]);
                    if(objectAsString.length > 40) {
                      objectAsString = objectAsString.substring(0,40) + '...';
                    }
                    line.append('<td><a class="frosting_object"><i class="icon-search"></i>' + objectAsString + '</a></td>');
                    var content = '<pre><code>' + JSON.stringify(item[property], null, '\t') + '</code></pre>';
                    line.find('.frosting_object').popover({
                        'content' : content,
                        'placement' : 'right'
                    });
                    return;
                }
                
                line.append('<td>' +  _.escape(item[property]) + '</td>');
            });
            
            if(item._actions && item._actions.length > 0) {
                var td = $('<td class="actions_list"><div class="btn-group"><a class="btn btn-mini dropdown-toggle" data-toggle="dropdown" href="#">Actions<span class="caret"></span></a><ul class="dropdown-menu"></ul></div></td>');
                _.each(item._actions, function(itemAction) {
                    if(!self.actions[itemAction.name]) self.actions[itemAction.name] = itemAction;
                    td.find('.dropdown-menu').append('<li><a data-name="'+itemAction.name+'" data-inspect="'+itemAction.inspectUrl+'" class="frosting_list_action" href="'+itemAction.url+'">'+itemAction.name+'</a></li>');
                });
                line.append(td);
            }
            table.find('tbody').append(line);
        });

        this.buildFooter(table);

        /* inject the table in the page */
        page.find('#frosting_list_content').html(table);
        self.$el.html(page);
        App.Frosting.get('mainContainer').html(self.$el);
        if(this.renderSearchForm) this.renderSearchForm();
        return this;
    },
    selectAll: function(e) {
        e.preventDefault();
        var target = $(e.currentTarget);
        if(target.hasClass('selected')) {
            $('.selection').attr('checked', false);
            target.removeClass('selected');
        } else {
            $('.selection').attr('checked', 'checked');
            target.addClass('selected');
        }
    },
    executeAction: function(e) {
        e.preventDefault();
        var target = $(e.currentTarget),
            action = {'name':  target.attr('data-name'),'url': target.attr('href'), 'inspectUrl': target.attr('data-inspect')};
        App.Frosting.getActionInspector().inspect(action,function(inspection) {
          App.Frosting.getFactory().create('action',action,inspection).prompt();
        });
    },
    executeBatchAction: function(e) {
        e.preventDefault();
        var target = $(e.currentTarget),
            urls = [];
        $('.line').each(function(index, line) {
            if($(line).find('.selection').is(':checked')) {
                urls.push($(line).find('.frosting_list_action[data-name="'+target.attr('data-name')+'"]').attr('href'));
            }
        });
        var action = {'name' : target.attr('data-name'), 'inspectUrl' : target.attr('href'), 'url' : urls};
        App.Frosting.getActionInspector().inspect(action, function(inspection) {
            App.Frosting.getFactory().create('action', action, inspection).prompt();
        });
    }
});


Frosting.Views.SearchResult = Frosting.Views.ModelList.extend({
    events : {
        'click .frosting_list_action' : 'executeAction',
        'click .frosting_batch_action' : 'executeBatchAction',
        'click .select-all' : 'selectAll',
        'click .icon-caret-down' : 'orderBy',
        'click .icon-caret-up' : 'orderBy',
        'click .selectedOrder' : 'resetOrder'
    },
    initialize: function(options) {
        Backbone.View.prototype.initialize.call(this, options);
        this.buildProperties();
    },
    renderSearchForm: function() {
        this.options.parent = this;
        this.options.searchForm = new Frosting.Action.SearchForm({model:this.options}).render();
        this.options.pagination = new Frosting.Action.Pagination({model:this.options}).render();
    },
    orderBy: function(e) {
        e.preventDefault();
        var target = $(e.currentTarget),
            criterias = {
                'pageNumber' : this.options.searchResult.getCurrentPage(), 
                'amountPerPage' :  this.options.searchResult.getAmountPerPage(),
                'orderBy' : [{'fieldName': target.attr('data-name'), 'direction': target.attr('data-order')}]
            };
        if(this.options.searchForm.getFilters().length!=0) {
            criterias.filters = this.options.searchForm.getFilters();
        }
        this.options.action.execute({'searchCriteria' : criterias});
    },
    resetOrder: function(e) {
        e.preventDefault();
        var criterias = {
                'pageNumber' : this.options.searchResult.getCurrentPage(), 
                'amountPerPage' :  this.options.searchResult.getAmountPerPage()
            };
        if(this.options.searchForm.getFilters().length!=0) {
            criterias.filters = this.options.searchForm.getFilters();
        }
        this.options.action.execute({'searchCriteria' : criterias});
        return false;
    },
    getOrders: function() {
      var orders = [];

      _.each(this.options.searchResult.getOrders(), function(order) {
        orders.push({'fieldName': order.fieldName, 'direction': order.direction});
      });

      return orders;
    },
    getModels: function() {
        return this.options.searchResult.getResult();
    }
});

Frosting.Action.Pagination = Backbone.View.extend({
    className: "pagination frosting_pagination",
    events: {
        "click .page" : "switchPage",
        "submit form": "enterPage"
    },
    initialize: function() {
        this.page = parseInt(this.model.searchResult.getCurrentPage(), 10) + 1;
        this.pages = this.model.searchResult.getAmountOfPage();
        this.itemPerPage = this.model.searchResult.getAmountPerPage();
    },
    render: function() {
        var form = $('<form><div class="input-append"><input type="text" value="'+this.page+'" /><span class="add-on">/'+this.pages+'</span></div></form>'),
            buttons = $('<div class="btn-group"></div>');

        if(this.page!=1) {
            buttons.append('<button class="page btn button" href="'+(this.page-1)+'">« Previous</button>');
        }

        if(this.pages>7) {
            for(i=(this.page-3);i<(this.page+4);i++) {
                if(i>0 && i<this.pages) {
                    if(i==this.page) { 
                        buttons.append('<button class="active btn button">'+i+'</button>');
                    } else { 
                        buttons.append('<button class="page btn button" href="'+i+'">'+i+'</button>'); 
                    }
                }
            }
        } else {
            for(i=1;i<=this.pages;i++) {
                if(i==this.page) { 
                    buttons.append('<button class="active btn button">'+i+'</button>');
                } else { 
                    buttons.append('<button class="page btn button" href="'+i+'">'+i+'</button>'); 
                }
            }
        }

        if(this.page<this.pages) {
            buttons.append('<button class="page btn button" href="'+(this.page+1)+'">Next »</button>');
        }

        $(this.el).html(form);
        $(this.el).append(buttons);
        $('#frosting_list_content').after($(this.el));
    },
    switchPage: function(e) {
        e.preventDefault();
        var criterias = {
            'pageNumber' : parseInt($(e.currentTarget).attr("href"), 10)-1, 
            'amountPerPage' : this.itemPerPage
        };
        if(this.model.searchForm.getFilters().length!=0) {
            criterias.filters = this.model.searchForm.getFilters();
        }
        if(this.model.parent.getOrders().length!=0) {
            criterias.orderBy = this.model.parent.getOrders();
        }
        this.model.action.execute({'searchCriteria' : criterias});
    },
    enterPage: function(e) {
        e.preventDefault();
        var criterias = {
            'pageNumber' : $(this.el).find('input').val()-1, 
            'amountPerPage' : this.itemPerPage
        };
        if(this.model.searchForm.getFilters().length!=0) {
            criterias.filters = this.model.searchForm.getFilters();
        }
        if(this.model.parent.getOrders().length!=0) {
            criterias.orderBy = this.model.parent.getOrders();
        }
        this.model.action.execute({'searchCriteria' : criterias});
    }
});