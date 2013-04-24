<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Framework\Test;

use Frosting\Framework\ConfigurationFileLoader;

/**
 * Description of Frosting
 *
 * @author Martin
 */
class ConfigurationFileLoaderTest extends \PHPUnit_Framework_TestCase
{
  private $configurationFileLoader;
  
  private function getConfigurationFileLoader()
  {
    if(is_null($this->configurationFileLoader)) {
      $this->configurationFileLoader = new ConfigurationFileLoader();
    } 
    
    return $this->configurationFileLoader;
  }
  
  public function providerFiles()
  {
    return array(
      array(__DIR__ . "/fixtures/test1.json",array("result"=>true)),
      array(__DIR__ . "/fixtures/testImport1.json",array("result"=>true)),
      array(__DIR__ . "/fixtures/testImport2.json",array("result"=>true,"subDirectory"=>true)),
      array(__DIR__ . "/fixtures/eval.json",array("eval"=>"eval")),
      array(__DIR__ . "/fixtures/importOverride.json",array("overriden"=>true)),
    );
  }
  
  /**
   * @dataProvider providerFiles
   * @param string $file
   * @param mixed $expectedResult
   */
  public function testLoad($file, $expectedResult)
  {
    $result = $this->getConfigurationFileLoader()->load($file);
    $this->assertEquals($expectedResult,$result);
  }
}
