<?php

namespace Core\Models;


/**
 * Html pages model, for quick record paging.
 */
class Pages
{
    private static $obj = [];
    private static $base_uri = '';

    public static function init($record_count, $active_page, $page_limit, $pages_display = 10, $base_uri = null)
    {
        self::$base_uri = $base_uri;

        $pages_left = (int) floor($pages_display / 2);
        $pages_right = $pages_display - $pages_left - 1;

        self::$obj = [];

        self::$obj['record_count'] = $record_count;
        self::$obj['active_page'] = $active_page;
        self::$obj['page_limit'] = $page_limit;

        self::$obj['page_count'] = (int) ceil(self::$obj['record_count'] / self::$obj['page_limit']);
        if (empty(self::$obj['active_page']) || self::$obj['active_page'] > self::$obj['page_count']) {
            self::$obj['active_page'] = 1;
        }
        self::$obj['limit_from'] = (self::$obj['active_page'] < 1 ? 0 : (self::$obj['active_page'] - 1) * self::$obj['page_limit']);

        self::$obj['next_page'] = (self::$obj['active_page'] + 1 > self::$obj['page_count'] ? false : self::$obj['active_page'] + 1);
        self::$obj['prev_page'] = (self::$obj['active_page'] - 1 < 1 ? false : self::$obj['active_page'] - 1);

        switch (true) {
            case (self::$obj['active_page'] - $pages_left < 1):
                self::$obj['pages_from'] = 1;
                self::$obj['pages_to'] = (self::$obj['active_page'] + $pages_display >= self::$obj['page_count'] ? self::$obj['page_count'] : self::$obj['active_page'] + ($pages_display - self::$obj['active_page']));
                break;

            case (self::$obj['active_page'] + $pages_right >= self::$obj['page_count']):
                self::$obj['pages_from'] = (int) (self::$obj['active_page'] - $pages_display <= 0 ? 1 : self::$obj['active_page'] - ($pages_display - (self::$obj['page_count'] - self::$obj['active_page']) - 1));
                self::$obj['pages_to'] = self::$obj['page_count'];
                break;

            default:
                self::$obj['pages_from'] = self::$obj['active_page'] - $pages_left;
                self::$obj['pages_to'] = self::$obj['active_page'] + $pages_right;
                break;
        }

        return self::$obj;
    }

    public static function display()
    {
        if (empty(self::$obj) || self::$obj['page_count'] <= 1) {
            return '';
        }

        $pages = '<nav><ul class="pagination">';
        $pages .= '<li'.(self::$obj['active_page'] == 1 ? ' class="disabled"' : '').'><a href="'.self::$base_uri.self::$obj['prev_page'].'"><span aria-hidden="true">&laquo;</span><span class="sr-only">Previous</span></a></li>';

        for ($i = self::$obj['pages_from']; $i <= self::$obj['pages_to']; ++$i) {
            if ($i === self::$obj['active_page']) {
                $pages .= '<li class="active"><a href="'.self::$base_uri.$i.'">'.$i.' <span class="sr-only">(current)</span></a></li>';
            } else {
                $pages .= '<li><a href="'.self::$base_uri.$i.'">'.$i.'</a></li>';
            }
        }

        $pages .= '<li'.(self::$obj['active_page'] == self::$obj['page_count'] ? ' class="disabled"' : '').'><a href="'.self::$base_uri.self::$obj['next_page'].'"><span aria-hidden="true">&raquo;</span><span class="sr-only">Next</span></a></li>';
        $pages .= '</ul></nav>';

        return $pages;
    }
}
