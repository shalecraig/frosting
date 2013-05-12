<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\View;

use Frosting\IService\FrontController\IResponseAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Frosting\IService\View\IViewRendererService;

/**
 * Description of HtmlResponseAdapter
 *
 * @author Martin
 */
class HtmlResponseAdapter implements IResponseAdapter
{
  /**
   * @var Frosting\IService\View\IViewRendererService
   */
  private $viewRenderer;
  
  /**
   * @param \Frosting\IService\View\IViewRendererService $viewRenderer
   * 
   * @Inject
   */
  public function initialize(IViewRendererService $viewRenderer)
  {
    $this->viewRenderer = $viewRenderer;
  }
  
  public function adaptResponse($contentType, Request $request, Response $response,array $result) 
  {
    if($contentType != "text/html") {
      return false;
    }
    $service = $request->request->get('_service');
    $controller = $service['name'] . '/' . $service['method']; 

    if(!$this->viewRenderer->canRender($controller)) {
      return false;
    }
    
    $response->setContent($this->viewRenderer->render($controller,$result));
    return true;
  }  
}