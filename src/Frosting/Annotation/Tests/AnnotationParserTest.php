<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Annotation\Tests;

use Frosting\IService\Annotation\Tests\AnnotationParserServiceTest;
use Frosting\Annotation\AnnotationParser;

/**
 * Description of AnnotationParserTest
 *
 * @author Martin
 */
class AnnotationParserTest extends AnnotationParserServiceTest
{
  protected function getAnnotationParserService($configuration) 
  {
    return new AnnotationParser($configuration);
  }
}
