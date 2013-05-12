<?php

require_once(__DIR__ . '/../vendor/autoload.php');

$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();


\Frosting\Framework\Frosting::factory(__DIR__ . '/../frosting.json')
  ->getServiceContainer()
  ->getServiceByName("frontController")
  ->handleRequest($request);