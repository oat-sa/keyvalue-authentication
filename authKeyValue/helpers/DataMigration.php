<?php
/**
 * Created by PhpStorm.
 * User: christophemassin
 * Date: 3/07/14
 * Time: 16:39
 */

namespace oat\authKeyValue\helpers;
use common_persistence_AdvKeyValuePersistence;
use oat\authKeyValue\model\AuthKeyValueAdapter;
use tao_models_classes_UserService;

class DataMigration {

    public static function fromOntologyToKey (){


        $kvStore = common_persistence_AdvKeyValuePersistence::getPersistence(AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID);
        $service = tao_models_classes_UserService::singleton();
        $users = $service->getAllUsers();


        foreach( $users as $user){

            $userParameterFormatedForDb = array();
            $userParameterFormatedForDb['uri'] = $user->getUri();

            $userData = $user->getRdfTriples();

            foreach($userData as $property){

                switch($property->predicate){
                    case PROPERTY_USER_LOGIN :
                        $userParameterFormatedForDb[PROPERTY_USER_LOGIN] = $property->object;
                        $login = $property->object;
                    case PROPERTY_USER_PASSWORD :
                        $userParameterFormatedForDb[PROPERTY_USER_PASSWORD] = $property->object;
                        $password = $property->object;
                        break;
                    case PROPERTY_USER_ROLES :
                        $userParameterFormatedForDb[PROPERTY_USER_ROLES][] = $property->object;
                        break;
                    default :
                        $userParameterFormatedForDb[$property->predicate] = $property->object;


                }

            }

            $kvStore->getDriver()->hSet($login, PROPERTY_USER_PASSWORD, $password);
            $kvStore->getDriver()->hSet($login, 'parameters', json_encode($userParameterFormatedForDb));

            echo $login.'
            ';
        }

    }

    public static function generateKeyValueUser()
    {
        $kvStore = common_persistence_AdvKeyValuePersistence::getPersistence(AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID);

        $generationId = substr( md5(rand()), 0, 3);

        $ext = \common_ext_ExtensionsManager::singleton()->getExtensionById('taoGroups');


        for ($i = 0; $i < 1000; $i++) {
            $login = 'tt'.$i;
            $password = \core_kernel_users_AuthAdapter::getPasswordHash()->encrypt('pass'.$i);

            $uri = \common_Utils::getNewUri();

            $tt = array(
                'uri' => $uri,
                RDFS_LABEL => 'Test taker '.$i,
                PROPERTY_USER_UILG	=> 'http://www.tao.lu/Ontologies/TAO.rdf#Langen-US',
                PROPERTY_USER_DEFLG => 'http://www.tao.lu/Ontologies/TAO.rdf#Langen-US',
                PROPERTY_USER_LOGIN	=> $login,
                PROPERTY_USER_PASSWORD => $password,
                PROPERTY_USER_ROLES => array('http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole'),
                PROPERTY_USER_FIRSTNAME => 'Testtaker '.$i,
                PROPERTY_USER_LASTNAME => 'Family '.$generationId
            );

            $kvStore->hset($login, PROPERTY_USER_PASSWORD, $password);
            $kvStore->hset($login, 'parameters', json_encode($tt) );

        }
        echo 'testakers created';
    }

} 