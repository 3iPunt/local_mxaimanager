<?php

namespace local_mxaimanager\app\controller;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use local_mxaimanager\app\factory as base_factory;
use local_mxaimanager\output\manage\form;

class manage implements interfaces\view
{
    private base_factory $base_factory;
    private \moodle_url $url;
    private \moodle_page $page;

    public function __construct(base_factory $base_factory)
    {
        $this->base_factory = $base_factory;
        $this->url = new \moodle_url('/local/mxaimanager/view.php', [
            'view' => 'manage'
        ]);
        $this->page = $this->base_factory->page();
    }

    public function action(string $action): string
    {
        $this->url->param('action', $action);

        switch ($action) {
            case 'index':
                return $this->index();
            default:
                send_file_not_found();
        }
    }

    private function page_setup(): void
    {
        $this->page->set_url($this->url);
        $this->page->set_context(\core\context\system::instance());
    }

    private function index(): string
    {
        $this->page_setup();

        $output = $this->base_factory->output()->header();
        $output .= $this->base_factory->output()->render(
            new \local_mxaimanager\output\manage\index($this->base_factory)
        );
        $output .= $this->base_factory->output()->footer();

        return $output;
    }
}
