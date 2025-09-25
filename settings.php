<?php

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

/**
 * @var bool $hassiteconfig
 * @var admin_root $ADMIN
 */

global $CFG, $PAGE, $OUTPUT;

if ($hassiteconfig) {
    $component = 'local_mxaimanager';
    //$ADMIN->add('localplugins', $settings);
}
