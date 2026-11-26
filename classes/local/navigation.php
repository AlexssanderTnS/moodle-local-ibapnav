<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace block_ibapnav\local;

use cm_info;
use moodle_url;
use stdClass;

/**
 * Resolves previous and next course content in the visible course sequence.
 *
 * @package    block_ibapnav
 * @copyright  2026 IBAP-RJ
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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

        // The get_sections() method contains course-module ids in the same sequence used
        // by course sections, so navigation follows the actual course order.
        foreach ($modinfo->get_sections() as $cmids) {
            foreach ($cmids as $cmid) {
                $cm = $modinfo->get_cm($cmid);

                if (!self::is_navigable($cm)) {
                    continue;
                }

                if ($foundcurrent) {
                    $next = self::item($cm);
                    break 2;
                }

                if ((int) $cm->id === (int) $currentcm->id) {
                    $foundcurrent = true;
                    continue;
                }

                $previous = self::item($cm);
            }
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

        // A null URL normally means the module has no direct view page. Do not
        // invent a view.php URL because custom/core modules may not expose one.
        return !empty($cm->url);
    }

    /**
     * Convert cm_info into render data.
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
