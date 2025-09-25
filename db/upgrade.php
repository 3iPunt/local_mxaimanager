<?php

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

function xmldb_local_mxaimanager_upgrade($oldversion)
{
    global $CFG, $DB;
    $dbman = $DB->get_manager();

    return true;
}
