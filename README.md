nette-neo4j-extension
=====================

Neo4j extension for Nette Framework

Provides configured entity manager from https://github.com/lphuberdeau/Neo4j-PHP-OGM and a debug panel

Install via composer

```json
"require": {
		"izytechab/nette-neo4j-extension": "@dev"
    }
``` 

register in bootstrap

```php
$configurator->onCompile[] = function($configurator, $compiler) {
		$compiler->addExtension('neo4j', new \Bazo\Extensions\Neo4j\DI\Neo4jExtension);
};
```

config in common.neon

extensions:
        neo4j: izytechAB\neo4j\DI\Neo4jExtension
neo4j:
        host: neo4j
        port: 7474
        user: neo4j
        password: neo4j
        cachePrefix: neo4j
        metaDataCache: apc
