<?php

namespace local_mxaimanager\output\manage_providers;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

global $CFG;
require_once($CFG->libdir . '/formslib.php');

use local_mxaimanager\app\ai\provider\entity;
use local_mxaimanager\app\factory as base_factory;

class default_provider_form extends \moodleform
{
    private base_factory $base_factory;

    public function __construct(
        base_factory $base_factory,
        \moodle_url $url,
    ) {
        $this->base_factory = $base_factory;

        parent::__construct($url);
    }

    /**
     * @param \local_mxaimanager\app\collection<\local_mxaimanager\app\ai\default_provider\entity> $default_providers
     * @return void
     */
    public function load_data(\local_mxaimanager\app\collection $default_providers): void
    {
        $data = [];
        foreach ($default_providers as $default_provider) {
            $data[$default_provider->get_action_interface()] = (string)$default_provider->get_provider_id();
        }

        // Fill in defaults from preconfigured providers if not already set.
        $preconfigured_providers = $this->base_factory->ai()->provider()->repository()->get_all()->filter(static function (\local_mxaimanager\app\ai\provider\entity $provider) {
                if (!$provider->get_is_preconfigured()) {
                    return false;
                }

                $config = json_decode($provider->get_config_json(), true);

                return isset($config['default_unless_explicitly_set']) && $config['default_unless_explicitly_set'];
            });
        foreach ($this->base_factory->ai()->provider()->get_actions() as $interface => $action_name) {
            if (isset($data[$interface])) {
                continue;
            }

            $preconfigured_providers_supporting_action = $preconfigured_providers->filter(static function (\local_mxaimanager\app\ai\provider\entity $provider) use ($interface) {
                $classes_implemented = class_implements($provider->get_classname());

                return in_array($interface, $classes_implemented, true);
            });

            $data[$interface] = $preconfigured_providers_supporting_action->first()->get_id();
        }

        $this->set_data($data);
    }

    protected function definition(): void
    {
        $mform = $this->_form;

        $this->add_default_actions($mform);

        $this->add_action_buttons();
    }

    public function validation($data, $files): array
    {
        $errors = [];
        $actions = $this->base_factory->ai()->provider()->get_actions();

        /** @var string $interface */
        foreach ($actions as $interface => $action) {
            if (empty($data[$interface]) || ((int)$data[$interface]) === 0) {
                $errors[$interface] = get_string('required');
            }

            $providers_supporting_action = $this->base_factory->ai()->provider()->get_providers_supporting_action(
                $interface
            );
            $configured_providers_supporting_action = $this->base_factory->ai()->provider()->repository(
            )->get_all_by_classnames($providers_supporting_action);

            $providers_supporting_action_exists = $configured_providers_supporting_action->filter(
                static function (entity $provider) use ($data, $interface) {
                    if (!isset($data[$interface])) {
                        return false;
                    }

                    return $provider->get_id() === (int)$data[$interface];
                }
            )->not_empty();

            if (!$providers_supporting_action_exists) {
                $errors[$interface] = get_string('required');
            }
        }

        return $errors;
    }

    private function add_default_actions(\MoodleQuickForm $mform): void
    {
        $actions = $this->base_factory->ai()->provider()->get_actions();
        foreach ($actions as $interface => $action) {
            $providers_supporting_action = $this->base_factory->ai()->provider()->get_providers_supporting_action(
                $interface
            );
            $configured_providers_supporting_action = $this->base_factory->ai()->provider()->repository(
            )->get_all_by_classnames($providers_supporting_action);

            $options = $configured_providers_supporting_action->to_list(static function (entity $provider) {
                return $provider->get_id();
            }, static function (entity $provider) {
                return $provider->get_name();
            })->to_array();

            if (empty($options)) {
                $options[0] = get_string('no_available_providers', 'local_mxaimanager');
            } else {
                $options = [0 => get_string('none')] + $options;
            }

            $mform->addElement('select', $interface, $action, $options);
        }
    }
}
