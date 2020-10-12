# user_identity_checker
## Usage
* A plugin that ask a user permission to use his username in an other application
* This plugin is based on JWT use
## Install
* extract this plugin into moodle local directory
* run moodle update

## Settings
* enter the JWT informations to authorize the asking application to interact with this plugin
### Plugin setting
in moodle amdinistration fill plugin setting
#### Generate public and private keys pour moodle
the public key will be used by remote application to communicate with moodle
* Dashboard -> Site administration > Plugins -> Local plugins > user identity checker local plugin -> Settings
```bash
#!/usr/bin/env bash

ssh-keygen -t rsa -b 4096 -m PEM -f jwt-moodle-registration.key
# Ne pas mettre de mot de passe

openssl rsa -in jwt-moodle-registration.key -pubout -outform PEM -out jwt-moodle-registration.key.pub
```
##### Fill user_identity_checker plugin setting
through moodle administration
* local_user_identity_checker | publickey
* local_user_identity_checker | privatekey
#### add a remote application
* on the remote application generate private and public key
* in modle * Dashboard -> Site administration > Plugins -> Local plugins > user identity checker local plugin -> Settings -> External dashboards admin -> External dashboards admin
* Add an external dashboard
  * external url
  * external public key