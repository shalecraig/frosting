$.support.cors = true;

Frosting.Response = {};

Frosting.Response.Adapter = Backbone.Model.extend({
  createResponse: function(data, request) {
    throw new {message: 'You must override the createResponse function in the adapter'};
  }
});

Frosting.Response.Response = Backbone.Model.extend({
  setRequest: function(request) {
    this.request = request;
    return this;
  },
  getData: function() {
    return this.get('data');
  },
  getRequest: function() {
    return this.request;
  }
})

WoozworldResponseAdapter = Frosting.Response.Adapter.extend({
  createResponse: function(data) {
    return new Frosting.Response.Response({data:data.execution.data});
  }
})

Frosting.Request = Backbone.Model.extend({
  getUrl: function() {
    return this.get('url');
  },
  getOptions: function() {
    return this.get('options');
  },
  getParameters: function() {
    return this.get('options').params;
  }
});

Frosting.Requests = Backbone.Model.extend({
    initialize: function() {
        jQuery.ajaxSetup({ 
            headers: { Accept : "application/json; charset=utf-8" },
            dataType: "json",
            xhrFields: { withCredentials: true }
        });
    },
    get: function(url, options) {
        if(!options) var options = {};
        options.type = "GET";
        return this.request(url, options);
    },
    post: function(url, options) {
        if(!options) var options = {};
        options.type = "POST";
        return this.request(url, options);
    },
    request: function(url, options) {
        options.promise = new $.Deferred();
        if(!options.params) options.params = null;
        this.trigger('start',url,options);

        var request = new Frosting.Request({options:options,url:url});

        this.trigger('newRequest',this,request);
        
        request.trigger('start',request);
        
        $.ajax({ 
            url: url,
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(options.params),
            success: function(data) {
              var response = App.Frosting.getResponseAdapter().createResponse(data).setRequest(request);
              request.trigger("success",request, response);
              options.promise.resolve(response);
            },
            error: function(error) {
              options.promise.reject(error); 
              request.trigger("error",request, error);
            },
            complete: function() {
              request.trigger("complete", request);
            }
        });

        return options.promise.promise();
    }
});