<?php
declare(strict_types = 1);

namespace izytechAB\Neo4jNetteExtension\DI;

use \Nette\DI\CompilerExtension;

use \Doctrine\Common\Annotations\AnnotationReader;
use \Doctrine\Common\EventManager;

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
                            ->setFactory('izytechAB\neo4j\DI\Neo4jExtension::createNeo4jClient', ['@container', $config])
                            ->setAutowired(FALSE);
            
            $builder->addDefinition($this->prefix('entityManager'))
                            ->setClass('izytechAB\Neo4j\EntityManager')
                            ->setFactory('izytechAB\neo4j\DI\Neo4jExtension::createEntityManager', ['@container', $config])
                            ->setAutowired(FALSE);

            $builder->addDefinition('entityManager')
                            ->setClass('\izytechAB\Neo4j\EntityManager')
                            ->setFactory('@container::getService', array($this->prefix('entityManager')));
            
            /**
             * @todo >setFactory($this->prefix('@entityManager'));
             */
            $builder->addDefinition($this->prefix('panel'))
                    ->setFactory('\izytechAB\Neo4jNetteExtension\Diagnostics\Panel::register');

    }

    public static function createNeo4jClient(\Nette\DI\Container $container, $config)
    {
            return $container->neo4j->entityManager->getClient();
    }
    
    

    public static function createEntityManager(\Nette\DI\Container $container, $config)
    {

        /**
         * @todo Fix QD 
         */
        \Doctrine\Common\Annotations\AnnotationRegistry::registerFile($config['vendorDir'] . '/izytechAB/neo4jphp-ogm/lib/izytechAB/Neo4j/Annotation/Auto.php');
        \Doctrine\Common\Annotations\AnnotationRegistry::registerFile($config['vendorDir'] . '/izytechAB/neo4jphp-ogm/lib/izytechAB/Neo4j/Annotation/Entity.php');
        \Doctrine\Common\Annotations\AnnotationRegistry::registerFile($config['vendorDir'] .  '/izytechAB/neo4jphp-ogm/lib/izytechAB/Neo4j/Annotation/Index.php');
        \Doctrine\Common\Annotations\AnnotationRegistry::registerFile($config['vendorDir'] . '/izytechAB/neo4jphp-ogm/lib/izytechAB/Neo4j/Annotation/ManyToMany.php');
        \Doctrine\Common\Annotations\AnnotationRegistry::registerFile($config['vendorDir'] .  '/izytechAB/neo4jphp-ogm/lib/izytechAB/Neo4j/Annotation/ManyToOne.php');
        \Doctrine\Common\Annotations\AnnotationRegistry::registerFile($config['vendorDir'] . '/izytechAB/neo4jphp-ogm/lib/izytechAB/Neo4j/Annotation/Property.php');

        
        
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

        $entityManager->setEventManager($eventManager);

        
        $listener = new  \izytechAB\Neo4jNetteExtension\Events\EventListner();
        
        $eventManager->addEventListener(['prePersist'], $listener);
        
        $eventManager->dispatchEvent('prePersist');
    
        /**
         * QD must be other way to fetch prefix
         */
        $panel = $container->getService($config['prefix']."panel");
        
        
        /**
         * @todo fix event registrarion for query -> panel
         */
        //$em->registerEvent('\HireVoice\Neo4j\EntityManager::QueryRunEvent'  , function($query, $parameters, $time)use($panel) {
        //                        $panel->addQuery($query, $parameters, $time);
        //});
        
        //$eventManager->addEventSubscriber('QueryRunEvent',function($query, $parameters, $time)use($panel){$panel->addQuery($query, $parameters, $time);});
        
        
     
        
        return $entityManager;

    }

}

 /*
        $eventManager->addEventListener(['PrePersist'], $listener);
        
        print_r($eventManager->getListeners());
        
        echo "listerner registrerd yes\n";
        */
    
        //$em->eventManager->addEventListener([\HireVoice\Neo4j\Event], $listener);

       // print_r($em->eventManager->getListeners());
        
        