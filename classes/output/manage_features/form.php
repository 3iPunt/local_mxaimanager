<?php

namespace local_mxaimanager\output\manage_features;


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
    private \local_mxaimanager\app\ai\feature\entity $feature;

    public function __construct(
        base_factory $base_factory,
        \moodle_url $url,
        \local_mxaimanager\app\ai\feature\entity $feature
    ) {
        $this->base_factory = $base_factory;
        $this->feature = $feature;

        parent::__construct($url);

        $this->load_data();
    }

    private function load_data(): void
    {
        $mform = $this->_form;

        $features_actions = $this->base_factory->ai()->feature()->action()->repository()->get_all_by_feature_id(
            $this->feature->get_id()
        );

        foreach ($features_actions as $action) {
            $mform->setDefault('provider_id_' . $action->get_action_interface(), $action->get_provider_id() ?? 0);

            if ($action->get_provider_id() === null) {
                continue; // No custom settings to load.
            }

            $provider = $this->base_factory->ai()->provider()->repository()->get_by_id($action->get_provider_id());

            /** @var provider $provider_class */
            $provider_class = $provider->get_classname();

            $prefix = $provider_class::get_provider_moodleform_element_prefix();

            $settings = json_decode($action->get_settings_json() ?? '{}', true, 512, JSON_THROW_ON_ERROR);
            foreach ($settings as $key => $value) {
                $mform->setDefault($prefix . $key, $value);
            }
        }
    }

    protected function definition(): void
    {
        $mform = $this->_form;

        $features_actions = $this->base_factory->ai()->feature()->action()->repository()->get_all_by_feature_id(
            $this->feature->get_id()
        );

        $action_names = $this->base_factory->ai()->provider()->get_actions();
        foreach ($features_actions as $action) {
            $mform->addElement(
                'header',
                'header_' . $action->get_action_interface(),
                $action_names[$action->get_action_interface()]
            );
            $this->add_provider_settings($mform, $action);
        }

        $this->add_action_buttons();
    }

    public function validation($data, $files): array
    {
        $errors = [];
        return $errors;
    }

    private function add_provider_settings(
        \MoodleQuickForm $mform,
        \local_mxaimanager\app\ai\feature\action\entity $action
    ): void {
        $providers = $this->base_factory->ai()->provider()->repository()->get_all()
            ->filter(static function (entity $provider) use ($action) {
                $implemented_interfaces = class_implements($provider->get_classname());

                return in_array($action->get_action_interface(), $implemented_interfaces, true);
            });

        $this->add_provider_id($mform, $action, $providers);
        $this->add_provider_action_settings($mform, $action, $providers);
    }

    /**
     * @param \MoodleQuickForm $mform
     * @param \local_mxaimanager\app\ai\feature\action\entity $action
     * @param \local_mxaimanager\app\collection<entity> $providers
     * @return void
     * @throws \coding_exception
     */
    private function add_provider_id(
        \MoodleQuickForm $mform,
        \local_mxaimanager\app\ai\feature\action\entity $action,
        \local_mxaimanager\app\collection $providers
    ): void {
        $provider_options = $providers->to_list(static function (entity $provider) {
            return $provider->get_id();
        }, static function (entity $provider) {
            return $provider->get_name();
        })->to_array();

        $options = [];
        if (empty($provider_options)) {
            $options[0] = get_string('no_available_providers', 'local_mxaimanager');
        } else {
            $options[0] = get_string('default');
        }

        $options += $provider_options;

        $mform->addElement(
            'select',
            'provider_id_' . $action->get_action_interface(),
            get_string('manage_features:form:provider_id', 'local_mxaimanager'),
            $options
        );
        $mform->setType('provider_id_' . $action->get_action_interface(), PARAM_INT);
    }

    /**
     * @param \MoodleQuickForm $mform
     * @param \local_mxaimanager\app\ai\feature\action\entity $action
     * @param \local_mxaimanager\app\collection<entity> $providers
     * @return void
     */
    private function add_provider_action_settings(
        \MoodleQuickForm $mform,
        \local_mxaimanager\app\ai\feature\action\entity $action,
        \local_mxaimanager\app\collection $providers
    ): void {
        $unique_provider_classes = $providers->to_list(static function (entity $provider) {
            return $provider->get_classname();
        }, static function (entity $provider) {
            return $provider->get_classname();
        });

        /** @var \local_mxaimanager\app\ai\provider\providers\provider|class-string $unique_provider_class */
        foreach ($unique_provider_classes as $unique_provider_class) {
            $prefix = $unique_provider_class::get_provider_moodleform_element_prefix();

            $unique_provider_class::action_moodleform_definition(
                $mform,
                $action->get_action_interface(),
                $prefix
            );

            // Hide provider settings if the provider is not selected.
            foreach ($mform->_elements as /** @var \HTML_QuickForm_element $element */ $element) {
                if (!is_string($element->getName())) {
                    continue; // Apparently some elements have no name...
                }

                if (!str_starts_with($element->getName(), $prefix)) {
                    continue;
                }

                if ($element->getAttribute('action') !== $action->get_action_interface()) {
                    continue;
                }

                $provider_ids_of_all_providers_except_this_type = $providers->filter(
                    static function (entity $p) use ($unique_provider_class) {
                        return $p->get_classname() !== $unique_provider_class;
                    }
                )->map(static function (entity $provider) {
                    return (string)$provider->get_id();
                })->to_array(true);
                $provider_ids_of_all_providers_except_this_type[] = '0'; // Also hide if 'default' is selected.

                $mform->hideIf(
                    $element->getName(),
                    'provider_id_' . $action->get_action_interface(),
                    'in',
                    implode('|', $provider_ids_of_all_providers_except_this_type)
                );
            }
        }
    }
}
