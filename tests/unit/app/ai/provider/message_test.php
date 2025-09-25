<?php

namespace local_mxaimanager\unit\app\ai\provider;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

class message_test extends \base_testcase
{
    public function test_json_encodable(): void
    {
        $role = 'user';
        $content = 'Hello, world!';

        $message = new \local_mxaimanager\app\ai\provider\message($role, $content);

        // Test that the instance is JSON encodable.
        $json = json_encode($message);

        // Verify the JSON is valid.
        $this->assertJson($json);

        // Verify the decoded JSON matches expected structure.
        $decoded = json_decode($json, true);
        $this->assertEquals([$role, $content], [$decoded['role'], $decoded['content']]);
        $this->assertEquals('{"role":"user","content":"Hello, world!"}', $json);

        // Ensure json_encode calls jsonSerialize() method.
        $expected_json = json_encode($message->jsonSerialize());
        $this->assertEquals($expected_json, $json);
    }
}
