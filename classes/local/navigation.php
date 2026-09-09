<?php
// This file is part of Moodle - http://moodle.org/

namespace block_ibapnav\local;

defined('MOODLE_INTERNAL') || die();

use cm_info;
use moodle_url;
use stdClass;

/**
 * Resolves previous and next content by traversing get_fast_modinfo()->cms
 * directly, mirroring the proven legacy navigation algorithm.
 */
final class navigation {
    /**
     * Resolve navigation for the current activity/resource.
     *
     * @param object $course Current course object.
     * @param cm_info $currentcm Current course module.
     * @return stdClass|null
     */
    public static function resolve(object $course, cm_info $currentcm): ?stdClass {
        $modinfo = get_fast_modinfo($course);

        $previous = null;
        $next = null;
        $foundcurrent = false;

        foreach ($modinfo->cms as $cm) {
            if (!self::is_navigable($cm)) {
                continue;
            }

            $item = self::item($cm);

            if ($foundcurrent) {
                $next = $item;
                break;
            }

            if ((int)$cm->id === (int)$currentcm->id) {
                $foundcurrent = true;
                continue;
            }

            $previous = $item;
        }

        if (!$foundcurrent) {
            return null;
        }

        $result = new stdClass();
        $result->previous = $previous;
        $result->next = $next;
        $result->courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);

        return $result;
    }

    /**
     * Decide whether a course module participates in navigation.
     *
     * The filter is deliberately permissive: skip only labels, invisible
     * modules and items without a usable view URL. This keeps the plugin from
     * hiding itself because of theme/page-type assumptions.
     *
     * @param cm_info $cm Course module.
     * @return bool
     */
    private static function is_navigable(cm_info $cm): bool {
        if ($cm->modname === 'label') {
            return false;
        }

        if (!$cm->uservisible) {
            return false;
        }

        if (!empty($cm->url)) {
            return true;
        }

        // Last-resort compatibility with modules that expose a normal
        // /mod/{name}/view.php endpoint but do not populate cm_info->url.
        return !empty($cm->modname) && !empty($cm->id);
    }

    /**
     * Convert cm_info into render data.
     *
     * @param cm_info $cm Course module.
     * @return stdClass
     */
    private static function item(cm_info $cm): stdClass {
        $item = new stdClass();

        if (!empty($cm->url)) {
            $item->url = $cm->url;
        } else {
            $item->url = new moodle_url('/mod/' . $cm->modname . '/view.php', ['id' => $cm->id]);
        }

        $item->name = $cm->get_formatted_name();
        return $item;
    }
}
