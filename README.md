# knpDeployPlugin

Deployment plugin for symfony to deploy to multiple servers and exec commands (migration, clear cache, add crontab) after the deployment on all or specific servers.
    
## Installation

    git submodule add git://github.com/knplabs/knpDeployPlugin.git plugins/knpDeployPlugin
    
## Configuration

Create a config/knp_architecture.yml file in your symfony directory:

    all:
      hosts:
    
        proxy:
          host: 67.123.123.123    # Host ip
      
        app01:
          host: 192.168.0.12    # Host ip
      
        app02:
          host: 192.168.0.13    # Host ip

    prod:
      hosts:

        all:
          user: nobody    # SSH username
          post-commands:    # Commands to run after synchronization
            crontab: crontab -r    # Remove the crontab
            cc: ./symfony cc    # Clear all caches

        kapp01:
          post-commands:
            crontab: crontab data/crontab    # Reload the crontab for this server
      
      env: prod    # Symfony environment to deploy to
  
      entry: proxy    # Proxy server (to deploy from)

      apps:   # App servers (to deploy to)
        - app01
        - app02

## Usage

    ./symfony knp:deploy prod
