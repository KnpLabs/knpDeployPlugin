<?php

class knpDeployTask extends sfBaseTask
{
  protected function configure()
  {
    // add your own arguments here
    $this->addArguments(array(
      new sfCommandArgument('architecture', sfCommandArgument::OPTIONAL, 'Architecture to deploy to', 'prod'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', true),
      new sfCommandOption('local', null, sfCommandOption::PARAMETER_OPTIONAL, 'Local deploy'),
    ));

    $this->namespace        = 'knp';
    $this->name             = 'deploy';
    $this->briefDescription = 'Deploys the code to a distant pack of servers';
    $this->detailedDescription = <<<EOF
The [knp:deploy|INFO] task {$this->briefDescription}.
Call it with:

  [php symfony knp:deploy|INFO] prod
  
Config the architecture to deploy to with config/knp_architecture.yml
A typical config is provided in the plugin config/knp_architecture.example.yml
Note that you config/knp_architecture.yml file will merge with the default config/knp_architecture.yml in the plugin directory.
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = $this->configuration;
    $configCache = new sfConfigCache($configuration);

    $archi = new knpArchitecture($configCache, $arguments['architecture']);
    $this->logSection('knpdeploy', "Deploying to " . $arguments['architecture']);
    if($options['local']) {
      $archi->localDeploy($options['local'], $arguments, $options);
    } else {
      $archi->deploy($arguments, $options);
    }
  }
}
