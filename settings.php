<?php

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

/**
 * @var bool $hassiteconfig
 * @var admin_root $ADMIN
 */

global $CFG, $PAGE, $OUTPUT;

$component = 'local_mxaimanager';

$ADMIN->add('localplugins', new admin_category('local_mxaimanager_pages', get_string('pluginname', $component)));

if ($hassiteconfig) {
    $ADMIN->add(
        'local_mxaimanager_pages',
        new admin_externalpage(
            'local_mxaimanager_manage_page',
            get_string('settings:manage_page', $component),
            new moodle_url('/local/mxaimanager/view.php?view=manage&action=index')
        )
    );
}
