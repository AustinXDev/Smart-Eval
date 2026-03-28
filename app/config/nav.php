<?php
$navigation = [
    'super_admin' => [
        [
            'label' => 'Dashboard',
            'icon'  => 'fa-solid fa-house',
            'url'   => [
                ['label' => 'College Dashboard', 'url' => '/Smart-Eval/views/admin/dashboard.view.php?dept=college', 'icon' => 'fa-solid fa-graduation-cap'],
                ['label' => 'SHS Dashboard',     'url' => '/Smart-Eval/views/admin/dashboard.view.php?dept=shs',    'icon' => 'fa-solid fa-building-columns'],
            ],
        ],
        [
            'label' => 'Manage Teachers',
            'icon'  => 'fa-solid fa-user-tie',
            'url'   => [
                ['label' => 'College Teachers', 'url' => '/Smart-Eval/views/admin/teachers.view.php?dept=college', 'icon' => 'fa-solid fa-graduation-cap'],
                ['label' => 'SHS Teachers',     'url' => '/Smart-Eval/views/admin/teachers.view.php?dept=shs',    'icon' => 'fa-solid fa-building-columns'],
            ],
        ],
        [
            'label' => 'Manage Students',
            'icon'  => 'fa-solid fa-user-graduate',
            'url'   => [
                ['label' => 'College Students', 'url' => '/Smart-Eval/views/admin/students.view.php?dept=college', 'icon' => 'fa-solid fa-graduation-cap'],
                ['label' => 'SHS Students',     'url' => '/Smart-Eval/views/admin/students.view.php?dept=shs',    'icon' => 'fa-solid fa-building-columns'],
            ],
        ],
        [
            'label' => 'Evaluation Periods',
            'icon'  => 'fa-solid fa-calendar',
            'url'   => '/Smart-Eval/views/admin/evaluation_periods.view.php',
        ],
        [
            'label' => 'Manage Questionnaires',
            'icon'  => 'fa-solid fa-clipboard',
            'url'   => '/Smart-Eval/views/admin/manage_questionnaires.view.php',
        ],
        [
            'label' => 'Manage Programs',
            'icon'  => 'fa-solid fa-book',
            'url'   => '/Smart-Eval/views/admin/manage_programs.view.php',
        ],
        [
            'label' => 'Reports & Analytics',
            'icon'  => 'fa-solid fa-chart-bar',
            'url'   => [
                ['label' => 'College Reports', 'url' => '/Smart-Eval/views/admin/reports.view.php?dept=college', 'icon' => 'fa-solid fa-graduation-cap'],
                ['label' => 'SHS Reports',     'url' => '/Smart-Eval/views/admin/reports.view.php?dept=shs',    'icon' => 'fa-solid fa-building-columns'],
            ],
        ],
    ],

    'dean' => [
        [
            'label' => 'Dashboard',
            'icon'  => 'fa-solid fa-house',
            'url'   => [
                ['label' => 'College Dashboard', 'url' => '/Smart-Eval/views/admin/dashboard.view.php?dept=college', 'icon' => 'fa-solid fa-graduation-cap'],
                ['label' => 'SHS Dashboard',     'url' => '/Smart-Eval/views/admin/dashboard.view.php?dept=shs',    'icon' => 'fa-solid fa-building-columns'],
            ],
        ],
        [
            'label' => 'Reports & Analytics',
            'icon'  => 'fa-solid fa-chart-bar',
            'url'   => [
                ['label' => 'College Reports', 'url' => '/Smart-Eval/views/admin/reports.view.php?dept=college', 'icon' => 'fa-solid fa-graduation-cap'],
                ['label' => 'SHS Reports',     'url' => '/Smart-Eval/views/admin/reports.view.php?dept=shs',    'icon' => 'fa-solid fa-building-columns'],
            ],
        ],
    ],

    'student' => [
        [
            'label' => 'Evaluation Dashboard',
            'icon'  => 'fa-solid fa-clipboard-check',
            'url'   => '/Smart-Eval/views/student/evaluation_dashboard.view.php',
        ],
    ],
];
?>