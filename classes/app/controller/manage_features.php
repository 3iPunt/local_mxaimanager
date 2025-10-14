<?php

namespace local_mxaimanager\app\controller;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use core\exception\coding_exception;
use local_mxaimanager\app\ai\provider\providers\provider;
use local_mxaimanager\app\factory as base_factory;
use local_mxaimanager\output\manage_features\form;

class manage_features implements interfaces\view
{
    private base_factory $base_factory;
    private \moodle_url $url;
    private \moodle_page $page;

    public function __construct(base_factory $base_factory)
    {
        $this->base_factory = $base_factory;
        $this->url = new \moodle_url('/local/mxaimanager/view.php', [
            'view' => 'manage_features'
        ]);
        $this->page = $this->base_factory->page();
    }

    public function action(string $action): string
    {
        $this->url->param('action', $action);

        switch ($action) {
            case 'browse':
                return $this->browse();
            case 'edit':
                return $this->edit();
            default:
                send_file_not_found();
        }
    }

    private function page_setup(): void
    {
        require_login();
        require_capability('local/mxaimanager:manage_configuration', \core\context\system::instance());

        $this->page->set_url($this->url);
        $this->page->set_context(\core\context\system::instance());
    }

    private function browse(): string
    {
        $this->page_setup();

        $output = $this->base_factory->output()->header();
        $output .= $this->base_factory->output()->render(
            new \local_mxaimanager\output\manage_features\browse($this->base_factory, $this->url)
        );
        $output .= $this->base_factory->output()->footer();

        return $output;
    }

    public function edit(): string
    {
        $feature_id = required_param('id', PARAM_INT);
        $this->url->param('id', $feature_id);

        $this->page_setup();

        $feature = $this->base_factory->ai()->feature()->repository()->get_by_id($feature_id);

        $form = new form($this->base_factory, $this->url, $feature);

        if ($form->is_cancelled()) {
            $this->url->param('action', 'browse');
            $this->url->param('id', null);
            redirect($this->url);
            die();
        }

        if ($form->is_submitted() && $form->is_validated()) {
            /** @var object $data */
            $data = $form->get_submitted_data();

            $actions = $this->base_factory->ai()->feature()->action()->repository()->get_all_by_feature_id($feature_id);
            foreach ($actions as $action) {
                $interface = $action->get_action_interface();

                $provider_id = (int)($data->{'provider_id_' . $interface} ?? '0');

                // If provider_id is selected, update the action to indicate use of default provider and settings.
                if ($provider_id === 0) {
                    $action->set_provider_id(null)
                        ->set_settings_json(null);
                    $this->base_factory->ai()->feature()->action()->repository()->update($action);
                    continue;
                }

                // Otherwise, gather provider-specific settings from the form data and update the action.
                $provider_config_data = [];

                $provider = $this->base_factory->ai()->provider()->repository()->get_by_id($provider_id);

                /** @var provider $provider_class */
                $provider_class = $provider->get_classname();
                $prefix = $provider_class::get_provider_moodleform_element_prefix();

                foreach ($data as $key => $value) {
                    if (str_starts_with($key, $prefix)) {
                        if (empty($value)) {
                            continue; // Skip empty values.
                        }

                        $key_without_prefix = str_replace($prefix, '', $key);
                        $provider_config_data[$key_without_prefix] = $value;
                    }
                }

                $json = empty($provider_config_data) ? null : json_encode($provider_config_data, JSON_THROW_ON_ERROR);

                $action->set_provider_id($provider_id)
                    ->set_settings_json($json);

                $this->base_factory->ai()->feature()->action()->repository()->update($action);
            }

            $this->url->param('action', 'browse');
            $this->url->param('id', null);
            redirect($this->url);
        }

        $output = $this->base_factory->output()->header();
        $output .= $this->base_factory->output()->render(
            new \local_mxaimanager\output\manage_features\edit($this->base_factory, $form, $feature)
        );
        $output .= $this->base_factory->output()->footer();

        return $output;
    }
}
