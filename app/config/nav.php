<?php
$navigation = [
    'super_admin' => [
        [
            'label' => 'Dashboard',
            'icon'  => 'fa-solid fa-house',
            'url'   => [
                ['label' => 'College Dashboard', 'url' => 'dashboard?dept=college', 'icon' => 'fa-solid fa-graduation-cap'],
                ['label' => 'SHS Dashboard',     'url' => 'dashboard?dept=shs',    'icon' => 'fa-solid fa-building-columns'],
            ],
        ],
        [
            'label' => 'Manage Teachers',
            'icon'  => 'fa-solid fa-user-tie',
            'url'   => [
                ['label' => 'College Teachers', 'url' => 'manage-teachers?dept=college', 'icon' => 'fa-solid fa-graduation-cap'],
                ['label' => 'SHS Teachers',     'url' => 'manage-teachers?dept=shs',    'icon' => 'fa-solid fa-building-columns'],
            ],
        ],
        [
            'label' => 'Manage Students',
            'icon'  => 'fa-solid fa-user-graduate',
            'url'   => [
                ['label' => 'College Students', 'url' => 'manage-students?dept=college', 'icon' => 'fa-solid fa-graduation-cap'],
                ['label' => 'SHS Students',     'url' => 'manage-students?dept=shs',    'icon' => 'fa-solid fa-building-columns'],
            ],
        ],
        [
            'label' => 'Evaluation Periods',
            'icon'  => 'fa-solid fa-calendar',
            'url'   => 'manage-evaluation-period',
        ],
        [
            'label' => 'Manage Questionnaires',
            'icon'  => 'fa-solid fa-clipboard',
            'url'   => 'manage-questionnaires',
        ],
        [
            'label' => 'Manage Programs',
            'icon'  => 'fa-solid fa-book',
            'url'   => 'manage-program',
        ],
        [
            'label' => 'Reports & Analytics',
            'icon'  => 'fa-solid fa-chart-bar',
            'url'   => [
                ['label' => 'College Reports', 'url' => 'reports-analytics?dept=college', 'icon' => 'fa-solid fa-graduation-cap'],
                ['label' => 'SHS Reports',     'url' => 'reports-analytics?dept=shs',    'icon' => 'fa-solid fa-building-columns'],
            ],
        ],
    ],

    'dean' => [
        [
            'label' => 'Dashboard',
            'icon'  => 'fa-solid fa-house',
            'url'   => [
                ['label' => 'College Dashboard', 'url' => 'dashboard?dept=collge', 'icon' => 'fa-solid fa-graduation-cap'],
                ['label' => 'SHS Dashboard',     'url' => 'dashboard?dept=shs',    'icon' => 'fa-solid fa-building-columns'],
            ],
        ],
        [
            'label' => 'Reports & Analytics',
            'icon'  => 'fa-solid fa-chart-bar',
            'url'   => [
                ['label' => 'College Reports', 'url' => 'reports-analytics?dept=college', 'icon' => 'fa-solid fa-graduation-cap'],
                ['label' => 'SHS Reports',     'url' => '/reports-analytics?dept=shs',    'icon' => 'fa-solid fa-building-columns'],
            ],
        ],
    ],

    'student' => [
        [
            'label' => 'Evaluation Dashboard',
            'icon'  => 'fa-solid fa-clipboard-check',
            'url'   => '/Smart-Eval/views/student/evaluation.view.php',
        ],
    ],
];
?>