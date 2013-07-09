<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class RestContactGroupsApiTest extends WebTestCase
{
    /** @var Client */
    protected $client = null;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
    }

    /**
     * @return array
     */
    public function testCreateContactGroup()
    {
        $request = array(
            "contact_group" => array(
            "name" => 'Contact_Group_Name_' . mt_rand()
            )
        );
        $this->client->request('POST', $this->client->generate('oro_api_post_contactgroup'), $request);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 201);

        return $request;
    }

    /**
     * @param $request
     * @return array
     * @depends testCreateContactGroup
     */
    public function testGetContactGroup($request)
    {
        $this->client->request('GET', $this->client->generate('oro_api_get_contactgroups'));
        $result = $this->client->getResponse();
        $result = json_decode($result->getContent(), true);
        $flag = 1;
        foreach ($result as $group) {
            if ($group['name'] == $request['contact_group']['name']) {
                $flag = 0;
                break;
            }
        }
        $this->assertEquals(0, $flag);

        $this->client->request('GET', $this->client->generate('oro_api_get_contactgroup', array('id' => $group['id'])));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        return $group;
    }

    /**
     * @param $group
     * @param $request
     * @depends testGetContactGroup
     * @depends testCreateContactGroup
     */
    public function testUpdateContactGroup($group, $request)
    {
        $group['name'] .= "_Updated";
        $this->client->request('PUT', $this->client->generate('oro_api_put_contactgroup', array('id' => $group['id'])), $request);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
        $this->client->request('GET', $this->client->generate('oro_api_get_contactgroup', array('id' => $group['id'])));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = json_decode($result->getContent(), true);
        $this->assertEquals($request['contact_group']['name'], $result['name'], 'ContactGroup does not updated');
    }

    /**
     * @param $group
     * @depends testGetContactGroup
     */
    public function testDeleteContact($group)
    {
        $this->client->request('DELETE', $this->client->generate('oro_api_delete_contactgroup', array('id' => $group['id'])));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
        $this->client->request('GET', $this->client->generate('oro_api_get_contactgroup', array('id' => $group['id'])));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404);
    }
}