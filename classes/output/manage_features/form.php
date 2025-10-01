<?php

namespace local_mxaimanager\output\manage_features;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

global $CFG;
require_once($CFG->libdir . '/formslib.php');

use local_mxaimanager\app\ai\provider\entity;
use local_mxaimanager\app\factory as base_factory;

class form extends \moodleform
{
    private base_factory $base_factory;

    public function __construct(
        base_factory $base_factory,
        \moodle_url $url,
    ) {
        $this->base_factory = $base_factory;

        parent::__construct($url);
    }

    public function load_data(entity $entity): void
    {
    }

    protected function definition(): void
    {
        $mform = $this->_form;

        $this->add_action_buttons();
    }

    public function validation($data, $files): array
    {
        $errors = [];
        return $errors;
    }
}
