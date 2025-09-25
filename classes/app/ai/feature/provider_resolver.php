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
     * @param int $action_id
     * @return array{0: int, 1: string}|array{}
     * @throws Exception
     */
    private function get_feature_action_provider_id_and_settings_json(int $action_id): array
    {
        try {
            // Get the feature action.
            $feature_action = $this->base_factory->ai()->feature()->action()->repository(
            )->get_by_feature_id_and_action_id($this->feature->get_id(), $action_id);

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
     * @param int $action_id
     * @return int
     * @throws Exception
     */
    private function get_default_action_provider_id(int $action_id): int
    {
        try {
            // Get the default action provider.
            $default_action_provider = $this->base_factory->ai()->action()->default_provider()->repository(
            )->get_by_action_id($action_id);
            return $default_action_provider->get_provider_id();
        } catch (\dml_missing_record_exception) {
            throw new Exception("No default provider configured for action ID {$action_id}");
        }
    }

    /**
     * @param int $provider_id
     * @param int $action_id
     * @return string
     * @throws Exception
     */
    private function get_provider_action_settings_json(int $provider_id, int $action_id): string
    {
        try {
            // Get the provider action settings.
            $provider_action_settings = $this->base_factory->ai()->provider()->action()->repository(
            )->get_by_action_id_and_provider_id($action_id, $provider_id);
            return $provider_action_settings->get_settings_json();
        } catch (\dml_missing_record_exception) {
            throw new Exception(
                "No default action settings configured for action ID {$action_id} with provider ID {$provider_id}"
            );
        }
    }

    /**
     * @param int $action_id
     * @return array{0: int, 1: string}
     * @throws Exception
     */
    public function get_provider_and_settings(int $action_id): array
    {
        // Try to get the provider & settings configured for the feature action.
        [$provider_id, $settings_json] = $this->get_feature_action_provider_id_and_settings_json($action_id);

        // If not set, fall back to the configured default provider for the action.
        if (empty($provider_id)) {
            $provider_id = $this->get_default_action_provider_id($action_id);
            $settings_json = $this->get_provider_action_settings_json($provider_id, $action_id);
        }

        // If not set, fall back to the provider action settings.
        if (empty($settings_json)) {
            $settings_json = $this->get_provider_action_settings_json($provider_id, $action_id);
        }

        return [$provider_id, $settings_json];
    }
}
