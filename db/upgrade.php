<?php

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

function xmldb_local_mxaimanager_upgrade($oldversion): bool
{
    global $CFG, $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2026011200) {
        $table = new \xmldb_table('local_mxaimanager_feature_action_usage_logs');

        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->add_field('feature_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $table->add_field('request_json', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
            $table->add_field('response_json', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
            $table->add_field('input_tokens', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $table->add_field('output_tokens', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $table->add_field('session_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
            $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

            $table->add_index('feature_id', XMLDB_INDEX_NOTUNIQUE, ['feature_id']);
            $table->add_index(
                'feature_id_timecreated',
                XMLDB_INDEX_NOTUNIQUE,
                ['feature_id', 'timecreated']
            );
            $table->add_index('session_id', XMLDB_INDEX_NOTUNIQUE, ['session_id']);
            $table->add_index('user_id', XMLDB_INDEX_NOTUNIQUE, ['user_id']);

            $dbman->create_table($table);
        }
    }

    return true;
}
