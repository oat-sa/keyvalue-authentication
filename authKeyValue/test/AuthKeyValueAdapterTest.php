<?php
/**
 * Created by PhpStorm.
 * User: christophemassin
 * Date: 4/07/14
 * Time: 10:49
 */

namespace oat\authKeyValue\test;

use oat\authKeyValue\model\AuthKeyValueAdapter;
use oat\authKeyValue\model\AuthKeyValueUser;
use GenerisPhpUnitTestRunner;
use common_session_SessionManager;
use common_persistence_AdvKeyValuePersistence;

require_once dirname(__FILE__) . '/../../generis/test/GenerisPhpUnitTestRunner.php';

class AuthKeyValueAdapterTest extends GenerisPhpUnitTestRunner {


    protected $adapter;
    protected $login;
    protected $password;

    public function setUp() {

        $this->login = 'tt1';
        $this->password = 'pass1';

        $kvStore = common_persistence_AdvKeyValuePersistence::getPersistence(AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID);
        $user = $kvStore->getDriver()->hGetAll($this->login);

        if ( ! $user ){
            $kvStore->getDriver()->hset($this->login,PROPERTY_USER_PASSWORD, '');
            $kvStore->getDriver()->hset($this->login,
                'parameters',
                json_encode(array(
                    "uri" => "http://192.168.33.22/transferAll/test.rdf#i140473436657255010",
                    "http://www.w3.org/2000/01/rdf-schema#label" => "Test taker 1",
                    "http://www.tao.lu/Ontologies/generis.rdf#userUILg" => "http://www.tao.lu/Ontologies/TAO.rdf#Langen-US",
                    "http://www.tao.lu/Ontologies/generis.rdf#userDefLg" => "http://www.tao.lu/Ontologies/TAO.rdf#Langen-US",
                    "http://www.tao.lu/Ontologies/generis.rdf#login" => "tt1",
                    "http://www.tao.lu/Ontologies/generis.rdf#password" => "JGXEkjgSvAd978b110dffe22d243a2d18e4afe747d82cb6d1863470afc2016b18ecb3173fb",
                    "http://www.tao.lu/Ontologies/generis.rdf#userRoles" =>
                        ["http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole"],
                    "http://www.tao.lu/Ontologies/generis.rdf#userFirstName" => "Testtaker 1",
                    "http://www.tao.lu/Ontologies/generis.rdf#userLastName"=>"Family 047"
                ))
            );
        }

        $this->adapter = new AuthKeyValueAdapter($this->login,$this->password);
    }


    public function testAuthenticate()
    {

        $this->adapter->authenticate();
        $session = \common_session_SessionManager::getSession();

        $this->assertEquals( $session->getUserPropertyValues(PROPERTY_USER_LOGIN), array($this->login));
    }



    public function tearDown()
    {
        $kvStore = common_persistence_AdvKeyValuePersistence::getPersistence(AuthKeyValueAdapter::KEY_VALUE_PERSISTENCE_ID);

        $kvStore->getDriver()->del($this->login);
    }

}
 