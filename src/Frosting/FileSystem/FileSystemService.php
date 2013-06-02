<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\FileSystem;

use Symfony\Component\Filesystem\Filesystem;
use Frosting\IService\FileSystem\IFileSystemService;

/**
 * This class is there just to have a interface for the service
 *
 * @author Martin
 */
class FileSystemService extends Filesystem implements IFileSystemService
{
  //put your code here
}
