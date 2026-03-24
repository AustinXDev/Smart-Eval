<?php
$navigation = [
    'super_admin' => [
        [
            'label' => 'Dashboard',
            'icon'  => '🏠',
            'url'   => [
                ['label' => 'College Dashboard', 'url' => '/Smart-Eval/views/admin/dashboard.view.php?dept=college', 'icon' => '🎓'],
                ['label' => 'SHS Dashboard',     'url' => '/Smart-Eval/views/admin/dashboard.view.php?dept=shs',    'icon' => '🏫'],
            ],
        ],
        [
            'label' => 'Manage Teachers',
            'icon'  => '👨‍🏫',
            'url'   => [
                ['label' => 'College Teachers', 'url' => '/Smart-Eval/views/admin/teachers.view.php?dept=college', 'icon' => '🎓'],
                ['label' => 'SHS Teachers',     'url' => '/Smart-Eval/views/admin/teachers.view.php?dept=shs',    'icon' => '🏫'],
            ],
        ],
        [
            'label' => 'Manage Students',
            'icon'  => '👨‍🎓',
            'url'   => [
                ['label' => 'College Students', 'url' => '/Smart-Eval/views/admin/students.view.php?dept=college', 'icon' => '🎓'],
                ['label' => 'SHS Students',     'url' => '/Smart-Eval/views/admin/students.view.php?dept=shs',    'icon' => '🏫'],
            ],
        ],
        [
            'label' => 'Evaluation Periods',
            'icon'  => '📅',
            'url'   => '/Smart-Eval/views/admin/evaluation_periods.view.php',
        ],
        [
            'label' => 'Manage Questionnaires',
            'icon'  => '📋',
            'url'   => '/Smart-Eval/views/admin/manage_questionnaires.view.php',
        ],
        [
            'label' => 'Manage Programs',
            'icon'  => '📚',
            'url'   => '/Smart-Eval/views/admin/manage_programs.view.php',
        ],
        [
            'label' => 'Reports & Analytics',
            'icon'  => '📊',
            'url'   => [
                ['label' => 'College Reports', 'url' => '/Smart-Eval/views/admin/reports.view.php?dept=college', 'icon' => '🎓'],
                ['label' => 'SHS Reports',     'url' => '/Smart-Eval/views/admin/reports.view.php?dept=shs',    'icon' => '🏫'],
            ],
        ],
    ],

    'dean' => [
        [
            'label' => 'Dashboard',
            'icon'  => '🏠',
            'url'   => [
                ['label' => 'College Dashboard', 'url' => '/Smart-Eval/views/admin/dashboard.view.php?dept=college', 'icon' => '🎓'],
                ['label' => 'SHS Dashboard',     'url' => '/Smart-Eval/views/admin/dashboard.view.php?dept=shs',    'icon' => '🏫'],
            ],
        ],
        [
            'label' => 'Reports & Analytics',
            'icon'  => '📊',
            'url'   => [
                ['label' => 'College Reports', 'url' => '/Smart-Eval/views/admin/reports.view.php?dept=college', 'icon' => '🎓'],
                ['label' => 'SHS Reports',     'url' => '/Smart-Eval/views/admin/reports.view.php?dept=shs',    'icon' => '🏫'],
            ],
        ],
    ],

    'student' => [
        [
            'label' => 'Evaluation Dashboard',
            'icon'  => '📝',
            'url'   => '/Smart-Eval/views/student/evaluation_dashboard.view.php',
        ],
    ],
];
?>