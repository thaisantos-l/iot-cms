<?php
/** Only the disposable test installation may use this transport fixture. */
require __DIR__ . '/bootstrap.php';
$action=$argv[1] ?? '';
if ('reset' === $action) {
    global $wpdb;
    foreach ($wpdb->get_col("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'iotnest_contact_request_%' OR option_name LIKE 'iotnest_contact_lock_%' OR option_name LIKE '_transient_iotnest_contact_rate_%' OR option_name LIKE '_transient_timeout_iotnest_contact_rate_%'") as $key) delete_option($key);
    wp_clear_scheduled_hook('iotnest_contact_cleanup');
    update_option('iotnest_qa_mail_mode','success');update_option('iotnest_qa_mail_count',0);
    update_option('iotnest_qa_cmp',false);
} elseif ('mode' === $action) update_option('iotnest_qa_mail_mode',$argv[2]);
elseif ('count' === $action) echo (int)get_option('iotnest_qa_mail_count',0);
elseif ('cmp' === $action) update_option('iotnest_qa_cmp',true);
else throw new RuntimeException('Unknown contact fixture action.');
