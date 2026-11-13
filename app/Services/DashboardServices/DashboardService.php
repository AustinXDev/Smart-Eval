<?php

declare(strict_types=1);

namespace App\Services\DashboardServices;

use App\Repositories\AnalyticsRepo\AnalyticsRepository;
use App\Repositories\DashboardRepo\DashboardRepository;
use App\Repositories\EvaluationRepo\EvaluationRepository;
use App\Repositories\EvaluationRepo\EvaluationStatusRepository;
use App\Repositories\MonitoringRepo\ParticipationMonitoringRepository;
use App\Repositories\StudentRepo\StudentRepository;
use App\Repositories\TeacherRepo\TeacherRepository;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Dashboard bundle for one department. Figures are reused from the modules that
 * own them so the dashboard always agrees with those pages:
 *   - participation : Evaluation Monitoring (ParticipationMonitoringRepository)
 *   - performance   : Reports & Analytics live mean score and teacher ranking
 */
class DashboardService
{
    private const DEPARTMENTS = ['college', 'shs'];
    private const MAX_TREND_DAYS = 90;
    private const TOP_TEACHERS = 3;

    public function __construct(
        private EvaluationRepository $evaluationRepo,
        private StudentRepository $studentRepo,
        private TeacherRepository $teacherRepo,
        private EvaluationStatusRepository $evalStatusRepo,
        private ParticipationMonitoringRepository $monitoringRepo,
        private AnalyticsRepository $analyticsRepo,
        private DashboardRepository $dashboardRepo
    ) {
    }

    public function getDashboard(string $department): array
    {
        $department = strtolower(trim($department));
        if (!in_array($department, self::DEPARTMENTS, true)) {
            throw new InvalidArgumentException('Invalid department.');
        }

        $students = $this->studentRepo->countActiveByDepartment($department);
        $teachers = $this->teacherRepo->countActiveByDepartment($department);
        $activities = $this->dashboardRepo->getRecentActivities($department);

        $period = $this->evaluationRepo->findActiveByDepartment($department);
        if (!$period || (int) ($period['is_closed'] ?? 0) === 1) {
            return [
                'period' => null,
                'cards' => [
                    'students_total' => $students,
                    'teachers_total' => $teachers,
                    'submitted_evaluations' => 0,
                ],
                'participation' => null,
                'trend' => ['labels' => [], 'data' => []],
                'performance' => null,
                'activities' => $activities,
                'generated_at' => date('c'),
            ];
        }

        $periodId = (int) $period['period_id'];

        return [
            'period' => [
                'period_id' => $periodId,
                'academic_year' => (string) $period['academic_year'],
                'semester' => (string) $period['semester'],
                'start_date' => (string) $period['start_date'],
                'end_date' => (string) $period['end_date'],
            ],
            'cards' => [
                'students_total' => $students,
                'teachers_total' => $teachers,
                'submitted_evaluations' => $this->evalStatusRepo->countTotalSubmitted($department, $periodId),
            ],
            'participation' => $this->participation($periodId, $department),
            'trend' => $this->trend($periodId, $department, $period),
            'performance' => $this->performance($periodId, $department),
            'activities' => $activities,
            'generated_at' => date('c'),
        ];
    }

    /** Same counts and rules as the Evaluation Monitoring page. */
    private function participation(int $periodId, string $department): array
    {
        $s = $this->monitoringRepo->getSummary($periodId, $department);
        $eligible = $s['eligible'];
        $participating = $s['completed'] + $s['incomplete'];

        return [
            'eligible' => $eligible,
            'completed' => $s['completed'],
            'incomplete' => $s['incomplete'],
            'unresponsive' => $s['unresponsive'],
            'remaining' => $s['incomplete'] + $s['unresponsive'],
            'participating' => $participating,
            'participation_rate' => $eligible > 0 ? round($participating / $eligible * 100, 1) : 0.0,
            'completion_rate' => $eligible > 0 ? round($s['completed'] / $eligible * 100, 1) : 0.0,
        ];
    }

    /**
     * Daily submissions from the period start to today (or the end date if it
     * has passed), with zero-filled days; capped to the latest 90 days.
     */
    private function trend(int $periodId, string $department, array $period): array
    {
        $counts = $this->dashboardRepo->getDailySubmissions($periodId, $department);

        $today = new DateTimeImmutable('today');
        $start = (new DateTimeImmutable((string) $period['start_date']))->setTime(0, 0);
        $end = (new DateTimeImmutable((string) $period['end_date']))->setTime(0, 0);
        $last = min($today, $end);

        // Include days with submissions outside the scheduled window (e.g. forced periods).
        if ($counts !== []) {
            $first = new DateTimeImmutable(array_key_first($counts));
            $latest = new DateTimeImmutable(array_key_last($counts));
            $start = min($start, $first);
            $last = max($last, $latest);
        }
        if ($last < $start) {
            $last = $start;
        }
        $earliest = $last->sub(new DateInterval('P' . (self::MAX_TREND_DAYS - 1) . 'D'));
        $start = max($start, $earliest);

        $labels = [];
        $data = [];
        foreach (new DatePeriod($start, new DateInterval('P1D'), $last->modify('+1 day')) as $day) {
            $key = $day->format('Y-m-d');
            $labels[] = $key;
            $data[] = $counts[$key] ?? 0;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /** Live mean score and ranking, exactly as Reports & Analytics shows them. */
    private function performance(int $periodId, string $department): array
    {
        $mean = $this->analyticsRepo->getLiveMeanScore($periodId);
        $ranking = $this->analyticsRepo->getLiveTeacherRanking($periodId, $department);

        $top = array_map(static fn (array $row): array => [
            'full_name' => (string) $row['full_name'],
            'mean_score' => round((float) $row['mean_score'], 2),
            'total_evaluated' => (int) $row['total_evaluated'],
        ], array_slice($ranking, 0, self::TOP_TEACHERS));

        $average = (float) ($mean['score'] ?? 0);

        return [
            'overall_average' => $average > 0 ? round($average, 2) : null,
            'teachers_rated' => count($ranking),
            'top_teachers' => $top,
        ];
    }
}
