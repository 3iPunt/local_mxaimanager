<?php

namespace local_mxaimanager\app\controller;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use core\exception\coding_exception;
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

        $form = new form($this->base_factory, $this->url);

        $output = $this->base_factory->output()->header();
        $output .= $this->base_factory->output()->render(
            new \local_mxaimanager\output\manage_features\edit($this->base_factory, $form)
        );
        $output .= $this->base_factory->output()->footer();

        return $output;
    }
}
