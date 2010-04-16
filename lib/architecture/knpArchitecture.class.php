<?php

class knpArchitecture
{
  protected
    $config = null,
    $configCache = null,
    $architecture = null
    ;
 
  public function __construct(sfConfigCache $configCache, $architecture)
  {
    $this->configCache = $configCache;
    if(!file_exists($path = sfConfig::get('sf_config_dir') . '/knp_architecture.yml')) {
      throw new Exception($path . ' does not exist');
    }
    $this->configCache->registerConfigHandler('config/knp_architecture.yml', 'sfSimpleYamlConfigHandler');
    $this->config = include $this->configCache->checkConfig('config/knp_architecture.yml');
    $this->architecture = $architecture;
    $this->config = sfToolkit::arrayDeepMerge($this->config['all'], $this->config[$architecture]);
  }


  public function getHost($alias)
  {
    return new knpHost($alias, $this);
  }
  
  public function deploy(array $arguments = array(), array $options = array())
  {
    $entryHost = null;
    
    if(isset($this->config['entry'])) {
      $entryHost = $this->getHost($this->config['entry']);
      $entryHost->setIsEntry(true);
      $entryHost->deploy();
      
      $entryHost->remoteDeploy($arguments, $options);
    }
  }
  
  public function localDeploy($alias, array $arguments = array(), $options)
  {
    $currentHost = $this->getHost($alias);
    $currentHost->localUpdate();
    
    if($alias == $this->config['entry']) {
      foreach($this->getConfig('apps', array()) as $alias) {
        $host = $this->getHost($alias);
        $host->deploy();
        $host->remoteDeploy($arguments, $options);
      }
    }
  }
  
  public function getConfig($key = null, $default = false)
  {
    if(is_null($key)) {
      return $this->config;
    }
    return $this->config[$key];
  }


}