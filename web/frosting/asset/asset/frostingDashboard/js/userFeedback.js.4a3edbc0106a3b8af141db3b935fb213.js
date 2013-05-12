Frosting.UserFeedback = Backbone.Model.extend({ 
  requests : 0,
  initialize: function() {
    Backbone.Model.prototype.initialize.apply(this, arguments);
    this.loading = $('#frosting_loading');
    this.legend = $('.frosting_legend');
    this.initializeListener();
  },
  initializeListener: function()
  {
    this.get('controller').requests.on(
      'newRequest',
      this.handleNewRequest,
      this
    );
  },
  handleNewRequest: function(requests,request) {
    request.on('start',this.handleRequestStart,this);
    request.on('complete',this.handleRequestComplete,this);
    request.on('success',this.handleRequestSuccess,this);
    request.on('error',this.handleRequestError,this);
  },
  handleRequestStart: function(request) {
    if(this.requests==0) this.loading.fadeIn(150);
    this.requests++;
    var options = request.getOptions();
    if(!options.legend) options.legend = 'loading ' + url;
    var legend = $('<p>' + options.legend + '...</p>');
    this.legend.append(legend);
    request.set('userFeedbackRequestLegend',legend);
  },
  
  handleRequestComplete: function(request) {
    this.requests--;
    if(this.requests==0) this.loading.fadeOut(150);
    request.get('userFeedbackRequestLegend').remove();
  },
  
  handleRequestSuccess: function(request, response) {
    noty(
      {
        text: 'The action "' + this.name + '" was executed properly.',
        timeout: 1500,
        layout: 'bottomRight'
      }
    );
  },
  
  handleRequestError: function(request) {
    
  }
});