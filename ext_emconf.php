<?php

$EM_CONF['ns_comments'] = [
    'title' => 'TYPO3 Page Comments',
    'description' => 'Enable user-friendly commenting functionality for TYPO3 pages. Features nested replies, backend moderation, and admin email notifications.',

    'category' => 'plugin',
    'author' => 'Team T3Planet',
    'author_email' => 'info@t3planet.de',
    'author_company' => 'T3Planet',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'version' => '13.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.0.0-13.9.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
