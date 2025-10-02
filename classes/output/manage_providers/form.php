<?php

namespace local_mxaimanager\output\manage_providers;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

global $CFG;
require_once($CFG->libdir . '/formslib.php');

use local_mxaimanager\app\ai\provider\entity;
use local_mxaimanager\app\ai\provider\providers\provider;
use local_mxaimanager\app\factory as base_factory;

class form extends \moodleform
{
    private base_factory $base_factory;
    private bool $show_set_as_default;
    private ?entity $entity;

    public function __construct(
        base_factory $base_factory,
        \moodle_url $url,
        ?entity $entity = null,
        bool $show_set_as_default = false
    ) {
        $this->base_factory = $base_factory;
        $this->entity = $entity;
        $this->show_set_as_default = $show_set_as_default;

        parent::__construct($url);

        $this->load_data();
    }

    private function load_data(): void
    {
        if (!$this->entity) {
            return;
        }

        $this->_form->setDefault('name', $this->entity->get_name());
        $this->_form->setDefault('classname', $this->entity->get_classname());

        /** @var provider $provider */
        $provider = $this->entity->get_classname();
        $prefix = $provider::get_provider_moodleform_element_prefix();

        $json_config = json_decode($this->entity->get_config_json() ?? '{}', true, 512, JSON_THROW_ON_ERROR);
        foreach ($json_config as $key => $value) {
            $this->_form->setDefault($prefix . $key, $value);
        }
    }

    protected function definition(): void
    {
        $mform = $this->_form;

        $this->add_name($mform);
        $this->add_classname($mform);
        $this->add_provider_support($mform);

        if ($this->show_set_as_default) {
            $this->add_set_as_default($mform);
        }

        $this->add_provider_config($mform);

        $this->add_action_buttons();
    }

    public function validation($data, $files): array
    {
        $errors = [];

        try {
            if ($this->entity && $this->entity->get_name() !== $data['name']) {
                // Only check for name in use if the name has changed.
                $this->base_factory->ai()->provider()->repository()->get_by_name($data['name']);
                $errors['name'] = get_string('in_use', 'local_mxaimanager');
            }
        } catch (\Exception) {
            // Name not in use, all good.
        }

        /** @var provider $provider */
        $providers = array_keys($this->base_factory->ai()->provider()->get_providers());
        foreach ($providers as $provider) {
            if ($data['classname'] !== $provider) {
                continue;
            }

            $errors = array_merge(
                $provider::moodleform_validation($data, $provider::get_provider_moodleform_element_prefix()),
                $errors
            );
        }

        return $errors;
    }

    private function add_name(\MoodleQuickForm $mform): void
    {
        $mform->addElement('text', 'name', get_string('manage_providers:form:name', 'local_mxaimanager'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule(
            'name',
            get_string('maximumchars', '', 255),
            'maxlength',
            255,
            'client'
        );
    }

    private function add_classname(\MoodleQuickForm $mform): void
    {
        $options = $this->base_factory->ai()->provider()->get_providers();

        $mform->addElement(
            'select',
            'classname',
            get_string('manage_providers:form:type', 'local_mxaimanager'),
            $options
        );
        $mform->setType('classname', PARAM_TEXT);
        $mform->addRule('classname', null, 'required', null, 'client');
        $mform->addRule(
            'classname',
            get_string('maximumchars', '', 255),
            'maxlength',
            255,
            'client'
        );
    }

    private function add_set_as_default(\MoodleQuickForm $mform): void
    {
        $mform->addElement('advcheckbox', 'set_as_default', get_string('set_as_default', 'local_mxaimanager'));
    }

    private function add_provider_support(\MoodleQuickForm $mform): void
    {
        /** @var provider $provider */
        $providers = array_keys($this->base_factory->ai()->provider()->get_providers());
        foreach ($providers as $provider) {
            $prefix = $provider::get_provider_moodleform_element_prefix();

            $html = $this->base_factory->output()->render(
                new form_provider_supports($provider)
            );

            $mform->addElement('static', "{$prefix}tihi", '', $html);
            $mform->hideIf("{$prefix}tihi", 'classname', 'neq', $provider);
        }
    }

    public function add_provider_config(\MoodleQuickForm $mform): void
    {
        $mform->addElement('header', 'provider_settings', get_string('provider_settings', 'local_mxaimanager'));

        /** @var provider $provider */
        $providers = array_keys($this->base_factory->ai()->provider()->get_providers());
        foreach ($providers as $provider) {
            $prefix = $provider::get_provider_moodleform_element_prefix();
            $provider::moodleform_definition($mform, $prefix);

            // Hide provider settings if the provider is not selected.
            foreach ($mform->_elements as /** @var \HTML_QuickForm_element $element */ $element) {
                if (!is_string($element->getName())) {
                    continue; // Apparently some elements have no name...
                }

                if (!str_starts_with($element->getName(), $prefix)) {
                    continue;
                }

                $mform->hideIf($element->getName(), 'classname', 'neq', $provider);
            }
        }
    }
}
