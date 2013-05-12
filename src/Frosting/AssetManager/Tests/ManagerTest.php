<?php

namespace Frosting\AssetManager\Tests;

use Frosting\AssetManager\Manager;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
  public function testScssFile()
  {
    $assetManager = Manager::factory(
      array(
        "imports" => array(__DIR__ . "/../frosting.json"),
        "services" => array(
          "assetManager" => array(
            "configuration" => array(
              "rootDirectory" => __DIR__ . "/fixtures/web"
            )
          )
        )
      )
    );

    $tag = $assetManager->getHtmTags(array('/css/test.scss'));
    $simpleXml = new \SimpleXMLElement($tag[0]);
    $expected = "table.hl td.ln {
  text-align: right; }
";
    $this->assertEquals($expected, $assetManager->getContent((string)$simpleXml['href']));
  }
}