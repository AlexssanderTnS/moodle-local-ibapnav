# Changelog

## 2.3.0 - 2026-11-26

### Fixed
- Removed the deprecated legacy `before_footer` callback.
- Removed the fragile dependency on global renderer functions from a namespaced hook callback.
- Prevented database access while Moodle installation or upgrade is running.
- Added self-healing creation of the `block_ibapnav` table when an interrupted installation left plugin metadata without the table.
- Removed the XMLDB foreign-key/index collision on the `course` field.
- Added cleanup for duplicate per-course settings rows from older installations.
- Fixed per-course configuration saving on Moodle versions where `block_base::instance_config_save()` returns void.
- Prevented invented `/mod/{name}/view.php` links for modules without a valid view URL.
- Made canonical activity URL detection work when Moodle is installed in a subdirectory.

### Changed
- Rendering now uses `core\hook\output\before_footer_html_generation`.
- Navigation rendering moved to an autoloaded `renderer` service.
- Per-course persistence moved to an autoloaded `settings_repository` service.
- Previous/next resolution now follows the actual course section sequence.
- Only one activation block instance is allowed per course while course-specific configuration remains available.
- Quiz navigation is hidden for students until a finished attempt exists.
- Internal Lesson and Assignment workflows are excluded from the course-level navigation.
- Plugin release bumped to 2.3.0.

## 2.2.x

Intermediate compatibility fixes for contextual activity rendering and the Moodle output hook migration.

## 2.1.0

Stable block-based navigation with per-course activation and configurable previous/home/next experience.
