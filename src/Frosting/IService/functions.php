<?php

// code from php at moechofe dot com (array_merge comment on php.net)
/*
   * array arrayDeepMerge ( array array1 [, array array2 [, array ...]] )
   *
   * Like array_merge
   *
   *  arrayDeepMerge() merges the elements of one or more arrays together so
   * that the values of one are appended to the end of the previous one. It
   * returns the resulting array.
   *  If the input arrays have the same string keys, then the later value for
   * that key will overwrite the previous one. If, however, the arrays contain
   * numeric keys, the later value will not overwrite the original value, but
   * will be appended.
   *  If only one array is given and the array is numerically indexed, the keys
   * get reindexed in a continuous way.
   *
   * Different from array_merge
   *  If string keys have arrays for values, these arrays will merge recursively.
   */
function array_deep_merge()
{
  $args = func_get_args();
  $count = count($args);
  if ($count == 0) {
    return false;
  }
  
  if ($count == 1) {
    return $args[0];
  }
  
  if ($count > 2) {
    $args[1] = array_deep_merge($args[0], $args[1]);
    array_shift($args);
    return call_user_func_array('array_deep_merge', $args);
  }
  
  //If both are not array we return the last occurence found
  if (!is_array($args[0]) || !is_array($args[1])) {
    return $args[1];
  }
  
  $return = array();
  
  foreach (array_unique(array_merge(array_keys($args[0]), array_keys($args[1]))) as $key) {
    $isKey0 = array_key_exists($key, $args[0]);
    $isKey1 = array_key_exists($key, $args[1]);
    
    if ($isKey0 && $isKey1) {
      if (is_int($key)) {
        $return[] = $args[0][$key];
        $return[] = $args[1][$key];
        continue;
      }
      $value = array_deep_merge($args[0][$key], $args[1][$key]);
    } else {
      $value = $isKey0 ? $args[0][$key] : $args[1][$key];
    }
    
    if (is_int($key)) {
      $return[] = $value;
    } else {
      $return[$key] = $value;
    }
  }
  
  return $return;
}