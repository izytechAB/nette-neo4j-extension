<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

 /*
        $eventManager->addEventListener(['PrePersist'], $listener);
        
        print_r($eventManager->getListeners());
        
        echo "listerner registrerd yes\n";
        */
    
        //$em->eventManager->addEventListener([\HireVoice\Neo4j\Event], $listener);

       // print_r($em->eventManager->getListeners());
        




        /**
         * QD must be other way to fetch prefix
         */
       // $panel = $container->getService($config['prefix']."panel");
        
        
        /**
         * @todo fix event registrarion for query -> panel
         */
        //$em->registerEvent('\HireVoice\Neo4j\EntityManager::QueryRunEvent'  , function($query, $parameters, $time)use($panel) {
        //                        $panel->addQuery($query, $parameters, $time);
        //});
        
        //$eventManager->addEventSubscriber('QueryRunEvent',function($query, $parameters, $time)use($panel){$panel->addQuery($query, $parameters, $time);});
        
