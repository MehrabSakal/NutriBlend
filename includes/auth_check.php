<?php
/**
 * Convenience guard - include at the top of any page that requires a
 * logged-in user. For role specific pages call require_role() instead.
 */
if (!defined('APP_ROOT')) {
    require_once __DIR__ . '/bootstrap.php';
}
require_login();
