<?php

namespace local_mxaimanager\app\ai\provider\providers;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use local_mxaimanager\app\factory as base_factory;

abstract class provider
{
    protected base_factory $base_factory;

    abstract public function __construct(base_factory $base_factory, array $json_config);

    /**
     * Define the form elements required for this provider's configuration.
     * This method should add the elements in a group element to prevent conflicts with other providers.
     * @param \MoodleQuickForm $mform
     * @param string $element_name_prefix The prefix to use for the element names.
     * @return void
     */
    abstract public static function moodleform_definition(\MoodleQuickForm $mform, string $element_name_prefix): void;

    /**
     * Define the form validation rules for this provider's configuration.
     * This method should add the validation rules in a group element to prevent conflicts with other providers.
     * @param array $data
     * @param string $element_name_prefix
     * @return array
     */
    abstract public static function moodleform_validation(array $data, string $element_name_prefix): array;

    /**
     * Define the prefix to use for the provider's moodleform elements.'
     *
     * @return string
     */
    public static function get_provider_moodleform_element_prefix(): string
    {
        return str_replace(' ', '_', strtolower(static::class)) . '_';
    }
}
