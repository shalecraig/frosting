<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\View\Tests;

use Frosting\View\PhpRenderer;
use Frosting\View\FileSystemLoader;

/**
 * Description of PhpRendererTest
 *
 * @author Martin
 */
class PhpRendererTest extends \PHPUnit_Framework_TestCase
{
  private $renderer = null;
  
  public function setUp() 
  {
    $this->renderer = new PhpRenderer();
    $this->renderer->setFileLoader(new FileSystemLoader(array(__DIR__)));
  }
  
  public function testCanRender()
  {
    $this->assertTrue($this->renderer->canRender('/fixtures/toRender.php'));
    $this->assertTrue($this->renderer->canRender('/fixtures/toRender'));
    $this->assertFalse($this->renderer->canRender('notexistingFile.php'));
    $this->assertFalse($this->renderer->canRender('/fixtures/toRender.badExtension'));
  }
  
  public function testRender()
  {
    $this->assertEquals("content",$this->renderer->render('fixtures/toRender.php'));
  }
}
