<?php

namespace fajr\regression;
use fajr\libfajr\data_manipulation\AIS2Version;

class VersionRange
{
  public static function getMinVersion()
  {
    return new AIS2Version(2,3,19,35);
  }

  public static function getMaxVersion()
  {
    return new AIS2Version(2,3,19,36);
  }

}
