<?php
namespace fajr;

class ServerConfig
{
  private $config;

  public function __construct($config)
  {
    $this->config = $config;
  }

  public function isBeta()
  {
    return isset($this->config['Server.Beta']) && $this->config['Server.Beta'];
  }

  public function getServerName()
  {
    return $this->config['Server.Name'];
  }

  public function getCosignCookieName() {
    if (!isset($this->config['Login.Cosign.CookieName'])) return null;
    return $this->config['Login.Cosign.CookieName'];
  }

  public function getCosignProxyDB() {
    if (!isset($this->config['Login.Cosign.ProxyDB'])) return null;
    return $this->config['Login.Cosign.ProxyDB'];
  }

  public function getLoginType()
  {
    assert(isset($this->config['Login.Type']));
    return $this->config['Login.Type'];
  }

  public function getInstanceName()
  {
    assert(isset($this->config['Server.InstanceName']));
    return $this->config['Server.InstanceName'];
  }

}
