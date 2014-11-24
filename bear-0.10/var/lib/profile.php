<?php
/**
 * Profiler
 *
 * print [profile] link at the bottom of page if xhprof installed.
 *
 * usage:
 *
 * // at bootstrap
 * include /path/to/profile.php
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

$enable = extension_loaded('xhprof') && (PHP_SAPI !== 'cli');
if (! $enable) {
    return;
}

// ob start
ob_start();
// start
xhprof_enable(XHPROF_FLAGS_NO_BUILTINS | XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);

// stop
register_shutdown_function(
    function () {
        $xhprof = xhprof_disable();
        if (!$xhprof) {
            error_log('xhprof failed in ' . __FILE__ );
            return;
        }
        $id = (new XHProfRuns_Default)->save_run($xhprof, 'sunday');
        if ($id) {
            $ob = ob_get_clean();
            $replace = "<a style=\"position:absolute;right:20px; bottom:10px;\" class=\"btn btn btn-mini\" href=\"/xhprof_html/index.php?run={$id}&source=sunday\" target=\"_blank\">PROFILE</a></html>";
            echo str_replace('</html>', $replace, $ob);
        }
    }
);
