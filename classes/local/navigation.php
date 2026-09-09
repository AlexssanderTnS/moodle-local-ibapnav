<?php
// This file is part of Moodle - http://moodle.org/

namespace local_ibapnav\local;

defined('MOODLE_INTERNAL') || die();

use cm_info;
use moodle_url;
use stdClass;

/**
 * Resolves previous, course-home, and next links for the current course module.
 */
final class navigation {
    /**
     * Build navigation data for the current course module.
     *
     * @param cm_info $currentcm Current course module.
     * @return stdClass|null
     */
    public static function resolve(cm_info $currentcm): ?stdClass {
        $modinfo = get_fast_modinfo($currentcm->course);
        $ordered = [];

        foreach ($modinfo->sections as $sectioncmids) {
            foreach ($sectioncmids as $cmid) {
                if (!isset($modinfo->cms[$cmid])) {
                    continue;
                }

                $cm = $modinfo->cms[$cmid];

                if (!self::is_navigable($cm)) {
                    continue;
                }

                $ordered[] = $cm;
            }
        }

        if (!$ordered) {
            return null;
        }

        $currentindex = null;
        foreach ($ordered as $index => $cm) {
            if ((int)$cm->id === (int)$currentcm->id) {
                $currentindex = $index;
                break;
            }
        }

        if ($currentindex === null) {
            return null;
        }

        $result = new stdClass();
        $result->previous = $currentindex > 0 ? self::item($ordered[$currentindex - 1]) : null;
        $result->next = $currentindex < count($ordered) - 1 ? self::item($ordered[$currentindex + 1]) : null;
        $result->courseurl = new moodle_url('/course/view.php', ['id' => $currentcm->course]);

        return $result;
    }

    /**
     * Check whether a module should participate in sequential navigation.
     *
     * @param cm_info $cm Course module.
     * @return bool
     */
    private static function is_navigable(cm_info $cm): bool {
        if (!$cm->uservisible) {
            return false;
        }

        if (!$cm->has_view()) {
            return false;
        }

        if (empty($cm->url)) {
            return false;
        }

        return true;
    }

    /**
     * Convert a course module into render data.
     *
     * @param cm_info $cm Course module.
     * @return stdClass
     */
    private static function item(cm_info $cm): stdClass {
        $item = new stdClass();
        $item->url = $cm->url;
        $item->name = $cm->get_formatted_name();
        return $item;
    }
}
