# Active azure directory bundle
Active azure directory bundle for symfony 4 project

### Routing

Add the following code in the your `config/routes.yaml`

```yaml
opcoding_aad_bundle:
    resource: '@OpcodingAADBundle/Resources/config/routes.yaml'
```

Edit the bundles.php file and add the following code : 
```php
<?php
return [
    OpcodingAADBundle\OpcodingAADBundle::class => ['all' => true]
];
```

Edit de `config/packages/knpu_oauth2_client.yml` file and add the following code : 
```yaml
knpu_oauth2_client:
    clients:
        azure:
            type: azure
            client_id: '%env(resolve:AZURE_CLIENT_ID)%'
            client_secret: '%env(resolve:AZURE_CLIENT_SECRET)%'
            redirect_route: connect_azure_check
            redirect_params: {}
            api_version: '1.6'
```


Then edit the `config/packages/security.yml` and add the following code according to your needs: 

```yaml
security:
    providers:
        app:
            entity:
                class: OpcodingAADBundle:User
                property: username
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            pattern: ^/
            anonymous: ~
            logout:
                path: app_logout
                target: /
            guard:
                authenticators:
                    - OpcodingAADBundle\Security\AzureAuthenticator
```

For example, if your application request that all the users must be logged in you can configure it like that :


```yaml
security:
    firewalls:
        main:
            pattern: ^/
            anonymous: true
            logout:
                path: app_logout
                target: /
            guard:
                authenticators:
                    - OpcodingAADBundle\Security\AzureAuthenticator
    access_control:
        - { path: ^/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/, role: ROLE_USER }
```