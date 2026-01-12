<?php

namespace local_mxaimanager\privacy;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use core_privacy\local\metadata\collection;

class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider
{

    public static function get_metadata(collection $collection): collection
    {
        $collection->add_database_table(
            'local_mxaimanager\local_mxaimanager_feature_action_usage_logs',
            [
                'id' => 'privacy:metadata:local_mxaimanager_feature_action_usage_logs:id',
                'feature_id' => 'privacy:metadata:local_mxaimanager_feature_action_usage_logs:feature_id',
                'request_json' => 'privacy:metadata:local_mxaimanager_feature_action_usage_logs:request_json',
                'response_json' => 'privacy:metadata:local_mxaimanager_feature_action_usage_logs:response_json',
                'input_tokens' => 'privacy:metadata:local_mxaimanager_feature_action_usage_logs:input_tokens',
                'output_tokens' => 'privacy:metadata:local_mxaimanager_feature_action_usage_logs:output_tokens',
                'session_id' => 'privacy:metadata:local_mxaimanager_feature_action_usage_logs:session_id',
                'user_id' => 'privacy:metadata:local_mxaimanager_feature_action_usage_logs:user_id',
                'timecreated' => 'privacy:metadata:local_mxaimanager_feature_action_usage_logs:timecreated',
            ],
            'privacy:metadata:local_mxaimanager_feature_action_usage_logs'
        );

        return $collection;
    }

    public static function get_contexts_for_userid(int $userid): \core_privacy\local\request\contextlist
    {
        $contextlist = new \core_privacy\local\request\contextlist();

        $sql = "SELECT c.id
              FROM {context} c
              JOIN {local_mxaimanager_feature_action_usage_logs} l ON l.user_id = :userid AND c.contextlevel = :contextlevel AND c.instanceid = l.user_id";
        $params = [
            'userid' => $userid,
            'contextlevel' => CONTEXT_USER,
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    public static function export_user_data(\core_privacy\local\request\approved_contextlist $contextlist): void
    {
        global $DB;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_USER && $context->contextlevel != CONTEXT_SYSTEM) {
                continue;
            }

            $user = $contextlist->get_user();
            $records = $DB->get_records(
                'local_mxaimanager_feature_action_usage_logs',
                ['user_id' => $user->id],
                'timecreated DESC'
            );
            foreach ($records as $record) {
                $data = (object)[
                    'request_json' => $record->request_json,
                    'response_json' => $record->response_json,
                    'input_tokens' => $record->input_tokens,
                    'output_tokens' => $record->output_tokens,
                    'session_id' => $record->session_id,
                    'user_id' => $record->user_id,
                    'timecreated' => \core_privacy\local\request\transform::datetime($record->timecreated),
                ];

                \core_privacy\local\request\writer::with_context($context)
                    ->export_data(['local_mxaimanager'], $data);
            }
        }
    }

    public static function delete_data_for_users(\core_privacy\local\request\approved_userlist $userlist): void
    {
        global $DB;

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        // Delete all records for the specified users.
        $DB->delete_records_select(
            'local_mxaimanager_feature_action_usage_logs',
            'user_id IN (' . implode(',', array_map('intval', $userids)) . ')'
        );
    }

    public static function delete_data_for_all_users_in_context(\context $context): void
    {
        if ($context->contextlevel !== CONTEXT_USER) {
            return;
        }

        global $DB;
        $userid = $context->instanceid;
        $DB->delete_records('local_mxaimanager_feature_action_usage_logs', ['user_id' => $userid]);
    }

    public static function delete_data_for_user(\core_privacy\local\request\approved_contextlist $contextlist): void
    {
        global $DB;

        $userid = $contextlist->get_user()->id;

        $DB->delete_records('local_mxaimanager_feature_action_usage_logs', ['user_id' => $userid]);
    }

    public static function get_users_in_context(\core_privacy\local\request\userlist $userlist): void
    {
        global $DB;
        $userids = $DB->get_fieldset_sql("SELECT DISTINCT user_id FROM {local_mxaimanager_feature_action_usage_logs}");
        foreach ($userids as $userid) {
            $userlist->add_user($userid);
        }
    }
}
