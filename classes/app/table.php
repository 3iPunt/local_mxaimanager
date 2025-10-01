<?php

namespace local_mxaimanager\app;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

global $CFG;
require_once "$CFG->libdir/tablelib.php";

abstract class table extends \table_sql
{
    public function __construct(\moodle_url $url, string $unique_id)
    {
        parent::__construct($unique_id);
        $this->define_baseurl($url);
    }

    public function get_html(int $page_size): string
    {
        ob_start();
        $this->out($page_size, false);
        $html = ob_get_contents();
        ob_clean();

        return $html;
    }
}
