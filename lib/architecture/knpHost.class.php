<?php 

/**
* 
*/
class knpHost
{
  protected $alias, $architecture, $config = null;
  
  function __construct($alias, $architecture)
  {
    $this->alias = $alias;
    $this->architecture = $architecture;
    
    $archiConfig = $architecture->getConfig();
    
    $this->config = sfToolkit::arrayDeepMerge(
      $archiConfig['hosts']['all'],
      $archiConfig['hosts'][$alias]
    );
  }
  
  public function deploy()
  {
    $cmd = $this->getDeployCommand();
    $this->exec($cmd);
  }
  
  public function localUpdate()
  {
    $this->localPostCommands();
  }
  
  public function localPostCommands()
  {
    $commands = $this->getConfig('post-commands', array());
    foreach($commands as $command) {
      $this->exec($command);
    }
  }
  
  public function remoteDeploy($arguments, $options)
  {
    unset($arguments['task']);
    unset($options['help'], $options['quiet'], $options['trace'], $options['version'], $options['color']);

    $options['local'] = $this->alias;
    
    $mergeOptions = array();
    foreach($options as $key => $value) {
      if($value === false) {
        $value = '0';
      } elseif($value === null) {
        continue;
      } elseif($value === true) {
        $value = '1';
      }
      $mergeOptions[] = '--' . $key . '=' . $value;
    }
    
    $this->remoteExec(strtr('./symfony knp:deploy {arguments} {options}', array(
      '{arguments}' => implode(' ', $arguments),
      '{options}' => implode(' ', $mergeOptions),
    )), $this->config['host']);
  }
  

  public function remoteExec($remoteCmd, $host)
  {
    $cmd = strtr("{ssh} {ssh_options} {user}@{hostname} \"cd {dir} && {remote_cmd}\"", array(
      '{ssh}' => $this->getConfig('ssh'),
      '{ssh_options}' => $this->getConfig('ssh-options'),
      '{user}' => $this->getConfig('user'),
      '{hostname}' => $this->getConfig('host'),
      '{dir}' => $this->getConfig('dir'),
      '{remote_cmd}' => $remoteCmd,
    ));
    $this->exec($cmd);
  }
  
  public function getConfig($key)
  {
    return $this->config[$key];
  }
  
  public function getDeployCommand()
  {
    $hostAlias = $this->alias;
    $hostConfig = $this->config;
    
    if (!isset($hostConfig['host']))
    {
      throw new sfCommandException("You must define a \"host\" entry for $hostAlias.");
    }

    if (!isset($hostConfig['dir']))
    {
      throw new sfCommandException("You must define a \"dir\" entry for $hostAlias.");
    }

    $host = $hostConfig['host'];
    $dir  = $hostConfig['dir'];
    $user = isset($hostConfig['user']) ? $hostConfig['user'].'@' : '';

    if (substr($dir, -1) != '/')
    {
      $dir .= '/';
    }

    $ssh = 'ssh';

    if (isset($hostConfig['port']))
    {
      $port = $hostConfig['port'];
      $ssh = '"ssh -p'.$port.'"';
    }

    if (isset($hostConfig['parameters']))
    {
      $parameters = $hostConfig['parameters'];
    }
    else
    {
      $options = $hostConfig['options'];
      $parameters = $options['rsync-options'];
      if (file_exists($options['rsync-dir'].'/rsync_exclude.txt'))
      {
        $parameters .= sprintf(' --exclude-from=%s/rsync_exclude.txt', $options['rsync-dir']);
      }

      if (file_exists($options['rsync-dir'].'/rsync_include.txt'))
      {
        $parameters .= sprintf(' --include-from=%s/rsync_include.txt', $options['rsync-dir']);
      }

      if (file_exists($options['rsync-dir'].'/rsync.txt'))
      {
        $parameters .= sprintf(' --files-from=%s/rsync.txt', $options['rsync-dir']);
      }
    }

    // $dryRun = $options['go'] ? '' : '--dry-run';
    $command = "rsync $parameters -e $ssh ./ $user$host:$dir";
    return $command;
  }

  public function exec($cmd)
  {
    echo "[exec] " . $cmd . "\n";
    return passthru($cmd);
  }
}
