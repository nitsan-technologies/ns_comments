<?php

$EM_CONF['ns_comments'] = [
    'title' => 'TYPO3 Page Comments',
    'description' => 'Enable user-friendly commenting functionality for TYPO3 pages. Features nested replies, backend moderation, and admin email notifications.',
    'category' => 'plugin',
    'author' => 'Team T3Planet',
    'author_email' => 'support@t3planet.de',
    'author_company' => 'T3Planet',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'version' => '14.1.0',
    'constraints' => [
        'depends' => [
            'php' => '8.1.0-8.5.99',
            'typo3' => '12.0.0-14.9.99',
            'ns_license' => '14.5.4-14.9.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
