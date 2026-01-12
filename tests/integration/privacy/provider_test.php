<?php

namespace local_mxaimanager\privacy;

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\writer;

class provider_test extends \advanced_testcase
{
    protected $user1;
    protected $user2;
    protected $course1;
    protected $course2;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        $this->resetAfterTest(true);

        global $DB;

        $this->course1 = self::getDataGenerator()->create_course();
        $this->user1 = self::getDataGenerator()->create_and_enrol($this->course1);
        $this->course2 = self::getDataGenerator()->create_course();
        $this->user2 = self::getDataGenerator()->create_and_enrol($this->course2);

        $DB->insert_record('local_mxaimanager_feature_action_usage_logs', [
            'feature_id' => 1,
            'request_json' => json_encode(['prompt' => 'Test prompt 1']),
            'response_json' => json_encode(['response' => 'Test response 1']),
            'input_tokens' => 10,
            'output_tokens' => 20,
            'session_id' => session_id(),
            'user_id' => $this->user1->id,
            'timecreated' => time(),
        ]);
        $DB->insert_record('local_mxaimanager_feature_action_usage_logs', [
            'feature_id' => 2,
            'request_json' => json_encode(['prompt' => 'Test prompt 2']),
            'response_json' => json_encode(['response' => 'Test response 2']),
            'input_tokens' => 15,
            'output_tokens' => 25,
            'session_id' => session_id(),
            'user_id' => $this->user2->id,
            'timecreated' => time(),
        ]);
    }

    /**
     * Test the get_reason method.
     */
    public function test_get_metadata()
    {
        $collection = new \core_privacy\local\metadata\collection('local_mxaimanager');
        $collection = provider::get_metadata($collection)->get_collection()[0];
        $privatefields = $collection->get_privacy_fields();

        self::assertEquals('local_mxaimanager\\local_mxaimanager_feature_action_usage_logs', $collection->get_name());
        self::assertEquals(
            'privacy:metadata:local_mxaimanager_feature_action_usage_logs:user_id',
            $privatefields['user_id']
        );
        self::assertEquals('privacy:metadata:local_mxaimanager_feature_action_usage_logs', $collection->get_summary());
    }

    public function test_get_users_in_context(): void
    {
        $context = \context_user::instance($this->user1->id);
        $userlist = new \core_privacy\local\request\userlist($context, 'local_mxaimanager_feature_action_usage_logs');

        $this->assertCount(0, $userlist->get_userids());

        provider::get_users_in_context($userlist);

        $this->assertEqualsCanonicalizing(
            [$this->user1->id, $this->user2->id],
            $userlist->get_userids()
        );
        $this->assertCount(2, $userlist->get_userids());
    }

    public function test_get_contexts_for_userid(): void
    {
        $contextlist1 = provider::get_contexts_for_userid($this->user1->id);

        $this->assertCount(1, $contextlist1->get_contexts());
        $this->assertNotEmpty($contextlist1->get_contexts());
        $this->assertEquals(
            \context_user::instance($this->user1->id)->id,
            $contextlist1->get_contexts()[0]->id
        );

        $contextlist2 = provider::get_contexts_for_userid($this->user2->id);
        $this->assertCount(1, $contextlist2->get_contexts());
        $this->assertNotEmpty($contextlist2->get_contexts());
        $this->assertEquals(
            \context_user::instance($this->user2->id)->id,
            $contextlist2->get_contexts()[0]->id
        );
    }

    public function test_export_user_data(): void
    {
        $exported_data1 = writer::with_context(\context_user::instance($this->user1->id))
            ->get_data(['local_mxaimanager']);
        $this->assertEquals(0, count($exported_data1));

        $contextlist = new approved_contextlist(
            $this->user1,
            'local_mxaimanager_feature_action_usage_logs',
            [\context_user::instance($this->user1->id)->id]
        );

        provider::export_user_data($contextlist);

        $exported_data = writer::with_context(\context_user::instance($this->user1->id))
            ->get_data(['local_mxaimanager']);

        $this->assertNotEmpty($exported_data);
        $this->assertEquals(
            $this->user1->id,
            $exported_data->user_id
        );
    }

    public function test_delete_data_for_users(): void
    {
        global $DB;

        $this->assertCount(2, $DB->get_records('local_mxaimanager_feature_action_usage_logs'));

        $contextlist = new approved_userlist(
            \context_user::instance($this->user1->id),
            'local_mxaimanager_feature_action_usage_logs',
            [$this->user1->id]
        );
        provider::delete_data_for_users($contextlist);

        $this->assertCount(1, $DB->get_records('local_mxaimanager_feature_action_usage_logs'));
    }

    public function test_delete_data_for_all_users_in_context(): void
    {
        global $DB;

        $this->assertCount(2, $DB->get_records('local_mxaimanager_feature_action_usage_logs'));

        $ctx = \context_user::instance($this->user1->id);
        provider::delete_data_for_all_users_in_context($ctx);

        $this->assertCount(1, $DB->get_records('local_mxaimanager_feature_action_usage_logs'));

        $ctx = \context_user::instance($this->user2->id);
        provider::delete_data_for_all_users_in_context($ctx);

        $this->assertCount(0, $DB->get_records('local_mxaimanager_feature_action_usage_logs'));
    }
}
