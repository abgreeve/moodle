<?php

require_once(__DIR__ . '/../../../config.php');

// The integration must be enabled for this import endpoint to be active.
if (!get_config('core', 'enablemoodlenet')) {
    print_error('moodlenetnotenabled', 'tool_moodlenet');
}

// The POST data must be present and valid.
if (!empty($_POST)) {
    if (!empty($_POST['resourceurl'])) {
        // Take the params we need, create a local URL, and redirect to it.
        // This allows us to hook into the 'wantsurl' capability of the auth system.
        $resourceurl = urlencode($_POST['resourceurl']);

        $url = new moodle_url('/admin/tool/moodlenet/index.php', ['resourceurl' => $resourceurl]);

        redirect($url);
    }
}

// Invalid or missing POST data. Show an error to the user.
print_error('missinginvalidpostdata', 'tool_moodlenet');
