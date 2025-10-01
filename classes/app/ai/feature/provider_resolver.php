<?php

namespace local_mxaimanager\app\ai\feature;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use Exception;
use local_mxaimanager\app\factory as base_factory;

class provider_resolver
{
    private base_factory $base_factory;
    private entity $feature;

    public function __construct(base_factory $base_factory, entity $feature)
    {
        $this->base_factory = $base_factory;
        $this->feature = $feature;
    }

    /**
     * @param string $action_interface
     * @return array{0: int, 1: string}|array{}
     * @throws Exception
     */
    private function get_feature_action_provider_id_and_settings_json(string $action_interface): array
    {
        try {
            // Get the feature action.
            $feature_action = $this->base_factory->ai()->feature()->action()->repository(
            )->get_by_feature_id_and_action_interface($this->feature->get_id(), $action_interface);

            // Attempt to get the provider & settings from the feature action.
            if ($feature_action->get_provider_id() !== null) {
                return [$feature_action->get_provider_id(), $feature_action->get_settings_json()];
            }
        } catch (\dml_missing_record_exception|\JsonException) {
            return [];
        }

        return [];
    }

    /**
     * @param string $action_interface
     * @return int
     * @throws Exception
     */
    private function get_default_action_provider_id(string $action_interface): int
    {
        try {
            // Get the default action provider.
            $default_action_provider = $this->base_factory->ai()->default_provider()->repository(
            )->get_by_action_interface($action_interface);
            return $default_action_provider->get_provider_id();
        } catch (\dml_missing_record_exception) {
            throw new Exception("No default provider configured for action ID {$action_interface}");
        }
    }

    /**
     * @param int $provider_id
     * @param string $action_interface
     * @return string
     * @throws Exception
     */
    private function get_provider_action_settings_json(int $provider_id, string $action_interface): string
    {
        try {
            // Get the provider action settings.
            $provider_action_settings = $this->base_factory->ai()->provider()->action()->repository(
            )->get_by_action_interface_and_provider_id($action_interface, $provider_id);
            return $provider_action_settings->get_settings_json();
        } catch (\dml_missing_record_exception) {
            throw new Exception(
                "No default action settings configured for action ID {$action_interface} with provider ID {$provider_id}"
            );
        }
    }

    /**
     * @param string $action_interface
     * @return array{0: int, 1: string}
     * @throws Exception
     */
    public function get_provider_and_config(string $action_interface): array
    {
        // Try to get the provider & settings configured for the feature action.
        [$provider_id, $settings_json] = $this->get_feature_action_provider_id_and_settings_json($action_interface);

        // If not set, fall back to the configured default provider for the action.
        if (empty($provider_id)) {
            $provider_id = $this->get_default_action_provider_id($action_interface);
            $settings_json = [];
        }

        $provider_config_json = json_decode(
            $this->base_factory->ai()->provider()->repository()->get_by_id($provider_id)->get_config_json(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $config_json = array_merge($provider_config_json, $settings_json);

        return [$provider_id, $config_json];
    }
}
