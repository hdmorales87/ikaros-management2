<?php

return [
    'default' => 'default',
    'date_format' => 'd-M-Y',
    'max_attachment_size' => (int) env('IMAP_MAX_ATTACHMENT_SIZE', 10485760),
    'allowed_extensions' => explode(',', env('IMAP_ALLOWED_EXTENSIONS', 'pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,jpg,jpeg,png,zip')),
    'accounts' => [],
    'options' => [
        'delimiter' => '/',
        'fetch' => \Webklex\PHPIMAP\IMAP::FT_PEEK,
        'sequence' => \Webklex\PHPIMAP\IMAP::ST_UID,
        'fetch_body' => true,
        'fetch_flags' => true,
        'soft_fail' => true,
        'rfc822' => true,
        'debug' => false,
        'uid_cache' => true,
        'message_key' => 'uid',
        'fetch_order' => 'asc',
        'dispositions' => ['attachment', 'inline'],
        'common_folders' => ['root' => 'INBOX'],
        'open' => [],
    ],
];
