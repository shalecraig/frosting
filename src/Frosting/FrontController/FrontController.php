<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\FrontController;

use Frosting\IService\Invoker\IInvokerService;
use Frosting\IService\DependencyInjection\IServiceContainer;
use Symfony\Component\HttpFoundation\Request;
use Frosting\Routing\Router;
use Symfony\Component\HttpFoundation\Response;
use Frosting\IService\EventDispatcher\IEventDispatcherService;

/**
 * Description of FrontController
 *
 * @author Martin
 */
class FrontController 
{
  /**
   * @var \Frosting\IService\Invoker\IInvokerService 
   */
  private $invoker;
  
  /**
   * @var \Frosting\IService\DependencyInjection\IServiceContainer
   */
  private $serviceContainer;
  
  /**
   * @var \Frosting\Routing\Router
   */
  private $routing;
  
  /**
   *
   * @var \Frosting\IService\FrontController\IResponseAdapter[] 
   */
  private $responseAdapters;
  
  /**
   *
   * @var \Frosting\IService\EventDispatcher\IEventDispatcherService 
   */
  private $eventDispatcher;
  
  /**
   * @param \Frosting\IService\Invoker\IInvokerService $invoker
   * @param \Frosting\IService\DependencyInjection\IServiceContainer $serviceContainer
   * @param \Frosting\Routing\Router $routing
   * 
   * @Inject
   */
  public function initialize(
    IServiceContainer $serviceContainer,
    IInvokerService $invoker,
    Router $routing,
    IEventDispatcherService $eventDispatcher
  )
  {
    $this->invoker = $invoker;
    $this->serviceContainer = $serviceContainer;
    $this->routing = $routing;
    $this->eventDispatcher = $eventDispatcher;
  }
 
  public function handleRequest(Request $request)
  {
    $result = $this->routing->match($request->getPathInfo());
    $request->request->add($result);
    $this->execute(
      $result['_service']['name'], 
      $result['_service']['method'],
      $request
    );
  }
  
  public function execute($serviceName, $methodName, Request $request)
  {
    $response = new Response();    
    $parameters = array_merge($request->query->all(),$request->request->all());
    $service = $this->serviceContainer->getServiceByName($serviceName);
    $executionResult = $this->invoker->invoke(
      array($service,$methodName),
      $parameters,
      array($request,$response)
    );
    $result = array('result'=>$executionResult);
  /*  $controller = $serviceName . '/' . $methodName;
    $this->eventDispatcher->dispatch(
      'Request.postExecution', 
      $request, 
      array('result'=>$result,'response'=>$response)
    );*/
     
    $this->completeResponse($request, $response, $result);
    $response->prepare($request);
    $response->send();
  }
  
  private function completeResponse(Request $request, Response $response, $result)
  {
    foreach($request->getAcceptableContentTypes() as $contentType) {
      foreach($this->responseAdapters as $adapter) {
        if($adapter->adaptResponse($contentType, $request, $response, $result)) {
          $response->headers->set('Content-Type',$contentType);
          return;
        }
      }
    }
  }
  
  /**
   * @param \Frosting\IService\FrontController\IResponseAdapter[] $adapters
   * 
   * @Inject(adapters="@responseAdapter")
   */
  public function setResponseAdapters(array $adapters = array())
  {
    $this->responseAdapters = $adapters;
  }
}
