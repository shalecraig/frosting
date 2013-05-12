Frosting.Model = {};
  
Frosting.Model.SearchResult = Backbone.Model.extend({
  getResult: function()
  {
    return this.attributes.result;
  },
  getAmountOfPage: function()
  {
    return this.attributes.amountOfPage;
  },
  getAmountPerPage: function()
  {
    return this.attributes.searchCriteria.amountPerPage;
  },
  getCurrentPage: function()
  {
    return this.attributes.searchCriteria.pageNumber;
  },
  getModelClass: function()
  {
    return this.attributes.modelClass;
  },
  getFilters: function() {
    if(this.attributes.searchCriteria.filters) {
      return this.attributes.searchCriteria.filters;
    } else return false;
  },
  getOrders: function() {
    if(this.attributes.searchCriteria.orderBy) {
      return this.attributes.searchCriteria.orderBy;
    } else return false;
  }
});

Frosting.Model.SearchCriterias = Backbone.Model.extend({
  getCriterias: function()
  {
    return this.attributes.metaData.model.searcheableColumns;
  },
  getAmountPerPage: function()
  {
    return this.attributes.metaData.amountPerPage;
  }
});