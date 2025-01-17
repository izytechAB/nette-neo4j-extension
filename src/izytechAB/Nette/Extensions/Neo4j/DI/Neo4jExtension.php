<?php
declare(strict_types = 1);

namespace izytechAB\Nette\Extensions\Neo4j\DI;

use \Nette\DI\CompilerExtension;

use \Doctrine\Common\Annotations\AnnotationReader;
use \Doctrine\Common\EventManager;

use izytechAB\Nette\Extensions\Neo4j\Events\EventListner as EventListner;


/**
 * Description of Neo4jExtension
 * 
 * Foreked from xxxxx
 *
 * @author Martin Bažík
 * @authot Thomas Alatalo Berg
 */
class Neo4jExtension extends \Nette\DI\CompilerExtension
{

    public $panel;


    /** @var array */
    public $defaults = [
            'host' => 'localhost',
            'port' => 7474,
            'cachePrefix' => 'neo4j',
            'metaDataCache' => 'array',
            'proxyDir' => '%appDir%/models/proxies',
            'debug' => false,
            'username' => null,
            'password' => null,
            'vendorDir' => null,
            'prefix'=>''

    ];
	
    /** @var array */
    private static $cacheClassMap = [
            'array' => '\Doctrine\Common\Cache\ArrayCache',
            'apc' => '\Doctrine\Common\Cache\ApcCache',
            'filesystem' => '\Doctrine\Common\Cache\FilesystemCache',
            'phpFile' => '\Doctrine\Common\Cache\PhpFileCache',
            'winCache' => '\Doctrine\Common\Cache\WinCacheCache',
            'xcache' => '\Doctrine\Common\Cache\XcacheCache',
            'zendData' => '\Doctrine\Common\Cache\ZendDataCache'
    ];

    /**
     * Processes configuration data
     *
     * @return void
     */
    public function loadConfiguration()
    {
            $builder = $this->getContainerBuilder();
            $config = $this->getConfig($this->defaults);
            $config['prefix'] = $this->prefix('');

            $builder->addDefinition($this->prefix('client'))
                            ->setClass('\Everyman\Neo4j\Client')
                            ->setFactory('izytechAB\Nette\Extensions\Neo4j\DI\Neo4jExtension::createNeo4jClient', ['@container', $config]);
                            //->setAutowired(FALSE);
            
            $builder->addDefinition($this->prefix('entityManager'))
                            ->setClass('izytechAB\Neo4j\EntityManager')
                            ->setFactory('izytechAB\Nette\Extensions\Neo4j\DI\Neo4jExtension::createEntityManager', ['@container', $config]);
                        
            
                            //->setAutowired(FALSE);

            /**
             * @todo >setFactory($this->prefix('@entityManager'));
             */
            //$builder->addDefinition('entityManager')
            
            //$builder->addDefinition($this->prefix('entityManager'))
            //                ->setClass('izytechAB\Neo4j\EntityManager')
            //                ->setFactory('@container::getService', array($this->prefix('entityManager')));
            

            $builder->addDefinition($this->prefix('panel'))
                    ->setClass('izytechAB\Nette\Extensions\Neo4j\Diagnostics\Panel')
                    ->setFactory('izytechAB\Nette\Extensions\Neo4j\Diagnostics\Panel::register');
            
            $builder->addDefinition($this->prefix('events'))
                    ->setClass('izytechAB\Nette\Extensions\Neo4j\Events\Listner');
                    
            
            
    }

    public static function createNeo4jClient(\Nette\DI\Container $container, $config)
    {
            return $container->neo4j->entityManager->getClient();
    }
    
    

    public static function createEntityManager(\Nette\DI\Container $container, $config)
    {
        
        \Doctrine\Common\Annotations\AnnotationRegistry::registerFile($config['vendorDir'] . '/izytechab/neo4jphp-ogm/lib/izytechAB/Neo4j/Annotation/Auto.php');
        \Doctrine\Common\Annotations\AnnotationRegistry::registerFile($config['vendorDir'] . '/izytechab/neo4jphp-ogm/lib/izytechAB/Neo4j/Annotation/Entity.php');
        \Doctrine\Common\Annotations\AnnotationRegistry::registerFile($config['vendorDir'] .  '/izytechab/neo4jphp-ogm/lib/izytechAB/Neo4j/Annotation/Index.php');
        \Doctrine\Common\Annotations\AnnotationRegistry::registerFile($config['vendorDir'] . '/izytechab/neo4jphp-ogm/lib/izytechAB/Neo4j/Annotation/ManyToMany.php');
        \Doctrine\Common\Annotations\AnnotationRegistry::registerFile($config['vendorDir'] .  '/izytechab/neo4jphp-ogm/lib/izytechAB/Neo4j/Annotation/ManyToOne.php');
        \Doctrine\Common\Annotations\AnnotationRegistry::registerFile($config['vendorDir'] . '/izytechab/neo4jphp-ogm/lib/izytechAB/Neo4j/Annotation/Property.php');

        
        
        $metadataCacheClass = self::$cacheClassMap[$config['metaDataCache']];
        $metadataCache = new $metadataCacheClass;
        $metadataCache->setNamespace($config['cachePrefix']);

        $reader = new \Doctrine\Common\Annotations\CachedReader(
                        new AnnotationReader, $metadataCache, false
        );
        
        $configuration = new \izytechAB\Neo4j\Configuration([
                'host' => $config['host'],
                'port' => $config['port'],
                'proxy_dir' => $config['proxyDir'],
                'debug' => $config['debug'],
                'username' => $config['username'],
                'password' => $config['password'],
                'annotationReader' => $reader
        ]);

        $entityManager = new \izytechAB\Neo4j\EntityManager($configuration);
        
        $eventManager = new EventManager();

        
        $listener = new \izytechAB\Nette\Extensions\Neo4j\Events\Listner($container);
        
        $eventManager->addEventListener(['prePersist'], $listener);

        $entityManager->setEventManager($eventManager);
        
        return $entityManager;

    }

}        