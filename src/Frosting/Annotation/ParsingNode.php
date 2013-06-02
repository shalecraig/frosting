<?php

namespace Frosting\Annotation;

use ArrayObject;

class ParsingNode extends ArrayObject
{
  public function getContext()
  {
    return $this['context'];
  }
  
  public function getContextName()
  {
    return $this['contextName'];
  }
  
  public function getAnnotation()
  {
    return $this->annotation;
  }
}
