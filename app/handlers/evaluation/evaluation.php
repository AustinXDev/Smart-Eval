<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-type: application/json');

require_once __DIR__ . '/../../middleware/require_auth.php';
require_once __DIR__ . '/../../config/database.php';

if(!isStudentLoggedIn()){
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit();
}

$studentID = getStudent();
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// Get student info - include year_level for regular student filtering
$student_stmt = $pdo->prepare("
    SELECT s.student_id, s.program_id, s.year_level, s.enrollment_type, s.selected_load_ids, p.department
    FROM students s
    INNER JOIN programs p ON s.program_id = p.program_id
    WHERE s.student_id = ?
");
$student_stmt->execute([$studentID['student_id']]);
$student = $student_stmt->fetch(PDO::FETCH_ASSOC);

if(!$student) {
  http_response_code(400);
  echo json_encode(['error' => 'Student not found']);
  exit();
}

$student_dept = $student['department'];

// Get active evaluation period
try {
  $period_stmt = $pdo->prepare("
    SELECT period_id, set_id 
    FROM evaluation_periods
    WHERE is_active = 1 AND target_dept = ? 
    ORDER BY start_date DESC
    LIMIT 1
  ");
  $period_stmt->execute([$student_dept]); 
  $period = $period_stmt->fetch(PDO::FETCH_ASSOC);

  if (!$period) {
    http_response_code(400);
    echo json_encode(['error' => 'No active evaluation period']);
    exit();
  }

  $period_id = $period['period_id'];
  $set_id = $period['set_id'];

  $_SESSION['period_id'] = $period_id;

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
  exit();
}

// Get teachers assigned to this student
try {
  $enrollment_type = $student['enrollment_type'] ?? 'Regular';

  if ($enrollment_type === 'Irregular') {
    // IRREGULAR: Get only teachers they selected via select_teachers.php
    $selected_load_ids = json_decode($student['selected_load_ids'] ?? '[]', true);

    if (empty($selected_load_ids)) {
      http_response_code(400);
      echo json_encode(['error' => 'Please select your teachers first before starting evaluation']);
      exit();
    }

    $placeholders = implode(',', array_fill(0, count($selected_load_ids), '?'));
    $teacher_stmt = $pdo->prepare("
        SELECT tl.load_id, t.teacher_id, t.full_name, t.department, t.email, t.image_path
        FROM teacher_load tl
        INNER JOIN teachers t ON tl.teacher_id = t.teacher_id
        WHERE tl.load_id IN ($placeholders)
          AND t.is_active = 1
        ORDER BY t.full_name ASC
    ");
    $teacher_stmt->execute($selected_load_ids);

  } else {
    // REGULAR: Get teachers by program_id + year_level
    $teacher_stmt = $pdo->prepare("
        SELECT DISTINCT tl.load_id, t.teacher_id, t.full_name, t.department, t.email, t.image_path
        FROM teacher_load tl
        INNER JOIN teachers t ON tl.teacher_id = t.teacher_id
        WHERE tl.program_id = ?
          AND tl.year_level = ?
          AND t.is_active = 1
        ORDER BY t.full_name ASC
    ");
    $teacher_stmt->execute([$student['program_id'], $student['year_level']]);
  }

  $teachers = $teacher_stmt->fetchAll(PDO::FETCH_ASSOC);

  if (empty($teachers)) {
    http_response_code(400);
    echo json_encode(['error' => 'No teachers assigned for your program']);
    exit();
  }

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
  exit();
}

// Current teacher index from session
$current_index = $_SESSION['current_teacher_index'] ?? 0;

// Reset if out of bounds (stale session from previous evaluation)
if ($current_index >= count($teachers)) {
    $current_index = 0;
    $_SESSION['current_teacher_index'] = 0;
}

switch ($action) {
  case 'get_teacher':
    getTeacher($teachers, $current_index, $period_id, $pdo, $student);
    break;

  case 'get_questions':
    getQuestions($set_id, $pdo);
    break;

  case 'submit_evaluation':
    submitEvaluation($studentID, $period_id, $set_id, $pdo, $teachers);
    break;

  case 'next_teacher':
    nextTeacher($teachers, $current_index);
    break;

  case 'previous_teacher':
    previousTeacher($teachers, $current_index);
    break;

  case 'check_completion':
    checkCompletion($studentID, $period_id, $teachers, $pdo);
    break;

  case 'complete_evaluation':
    completeAllEvaluation($studentID, $period_id, $teachers, $pdo);
    break;

  case 'get_all_answers':
    getAllAnswers($studentID, $period_id, $teachers, $pdo);
    break;

  default:
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
    exit();
}

// ─────────────────────────────────────────────
// FUNCTIONS
// ─────────────────────────────────────────────

function getTeacher($teachers, $currentIndex, $period_id, $pdo, $student) {
  // Safety net reset
  if ($currentIndex >= count($teachers)) {
    $currentIndex = 0;
    $_SESSION['current_teacher_index'] = 0;
  }

  $teacher    = $teachers[$currentIndex];
  $student_id = $student['student_id'];

  // Get evaluation status for this teacher
  $stmt = $pdo->prepare("
      SELECT eval_id, is_submitted 
      FROM evaluation_status
      WHERE student_id = ? AND load_id = ? AND period_id = ?
  ");
  $stmt->execute([$student_id, $teacher['load_id'], $period_id]);
  $eval_status = $stmt->fetch(PDO::FETCH_ASSOC);

  // Get previous answers if any
  $previous_answers = [];
  if ($eval_status && $eval_status['eval_id']) {
    $stmt = $pdo->prepare("
        SELECT question_id, score, comment 
        FROM evaluation_answers 
        WHERE eval_id = ?
    ");
    $stmt->execute([$eval_status['eval_id']]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
      $previous_answers[$row['question_id']] = [
        'score'   => (int)$row['score'],
        'comment' => $row['comment']
      ];
    }
  }

  echo json_encode([
    'success'             => true,
    'teacher'             => [
      'load_id'    => (int)$teacher['load_id'],
      'teacher_id' => (int)$teacher['teacher_id'],
      'full_name'  => $teacher['full_name'],
      'department' => $teacher['department'],
      'email'      => $teacher['email'],
      'image_path' => $teacher['image_path'] ?? 'default.png'
    ],
    'current_index'       => (int)$currentIndex,
    'total_teachers'      => (int)count($teachers),
    'progress_percentage' => round((($currentIndex + 1) / count($teachers)) * 100),
    'is_submitted'        => $eval_status ? (int)$eval_status['is_submitted'] : 0,
    'previous_answers'    => $previous_answers,
    'eval_id'             => $eval_status ? (int)$eval_status['eval_id'] : null,
    'period_id'           => (int)$period_id
  ]);
}

function getQuestions($setId, $pdo) {
  $stmt = $pdo->prepare("
      SELECT question_id, question_text, category 
      FROM questions 
      WHERE set_id = ? AND is_active = 1
      ORDER BY question_id ASC
  ");
  $stmt->execute([$setId]);
  $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
    'success'        => true,
    'questions'      => $questions,
    'question_count' => count($questions)
  ]);
}

function submitEvaluation($student, $periodId, $setId, $pdo, $teachers) {
  $data = json_decode(file_get_contents('php://input'), true);

  if (!$data || !isset($data['load_id']) || !isset($data['answers'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields: load_id or answers']);
    exit();
  }

  $load_id    = intval($data['load_id']);
  $answers    = $data['answers'];
  $student_id = $student['student_id'];

  //verify this load_id belongs to this student
  $valid = array_filter($teachers, fn($t) => (int)$t['load_id'] === $load_id);
  if (empty($valid)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid teacher for your evaluation']);
    exit();
  }

  if (empty($answers)) {
    http_response_code(400);
    echo json_encode(['error' => 'No answers provided']);
    exit();
  }

  // Verify all questions are answered
  $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM questions WHERE set_id = ? AND is_active = 1");
  $stmt->execute([$setId]);
  $total_questions = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

  if (count($answers) < $total_questions) {
    http_response_code(400);
    echo json_encode(['error' => 'Please answer all questions']);
    exit();
  }

  // Get or create evaluation_status row
  $stmt = $pdo->prepare("
      SELECT eval_id 
      FROM evaluation_status 
      WHERE student_id = ? AND load_id = ? AND period_id = ?
  ");
  $stmt->execute([$student_id, $load_id, $periodId]);
  $existing = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($existing) {
    $eval_id = $existing['eval_id'];
    $pdo->prepare("
        UPDATE evaluation_status 
        SET is_submitted = 1, date_taken = NOW() 
        WHERE eval_id = ?
    ")->execute([$eval_id]);
  } else {
    $stmt = $pdo->prepare("
        INSERT INTO evaluation_status (student_id, load_id, period_id, is_submitted, date_taken)
        VALUES (?, ?, ?, 1, NOW())
    ");
    $stmt->execute([$student_id, $load_id, $periodId]);
    $eval_id = $pdo->lastInsertId();
  }

  // UPSERT answers
  $stmt = $pdo->prepare("
      INSERT INTO evaluation_answers (eval_id, question_id, score, comment)
      VALUES (?, ?, ?, ?)
      ON DUPLICATE KEY UPDATE
          score   = VALUES(score),
          comment = VALUES(comment)
  ");

  foreach ($answers as $question_id => $answer) {
    $score   = intval($answer['score']);
    $comment = trim($answer['comment'] ?? '');

    if ($score < 1 || $score > 5) {
      http_response_code(400);
      echo json_encode(['error' => 'Invalid score for question ' . $question_id]);
      exit();
    }

    $stmt->execute([$eval_id, intval($question_id), $score, $comment]);
  }

  echo json_encode([
    'success' => true,
    'message' => 'Evaluation submitted successfully',
    'eval_id' => (int)$eval_id
  ]);
}

function nextTeacher($teachers, $currentIndex) {
  $nextIndex = $currentIndex + 1;

  if ($nextIndex >= count($teachers)) {
    echo json_encode([
      'success' => true,
      'is_last' => true,
      'message' => 'All teachers evaluated'
    ]);
    exit();
  }

  $_SESSION['current_teacher_index'] = $nextIndex;
  echo json_encode([
    'success'        => true,
    'is_last'        => false,
    'current_index'  => (int)$nextIndex,
    'total_teachers' => (int)count($teachers)
  ]);
}

function previousTeacher($teachers, $currentIndex) {
  $prevIndex = $currentIndex - 1;

  if ($prevIndex < 0) {
    http_response_code(400);
    echo json_encode(['error' => 'No previous teacher']);
    exit();
  }

  $_SESSION['current_teacher_index'] = $prevIndex;
  echo json_encode([
    'success'        => true,
    'current_index'  => (int)$prevIndex,
    'total_teachers' => (int)count($teachers)
  ]);
}

function checkCompletion($studentID, $periodId, $teachers, $pdo) {
  try {
    $student_id      = $studentID['student_id'];
    $total           = count($teachers);
    $completed_count = 0;

    foreach ($teachers as $teacher) {
      $stmt = $pdo->prepare("
          SELECT eval_id 
          FROM evaluation_status 
          WHERE student_id = ? AND load_id = ? AND period_id = ? AND is_submitted = 1
      ");
      $stmt->execute([$student_id, $teacher['load_id'], $periodId]);
      if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        $completed_count++;
      }
    }

    echo json_encode([
      'success'               => true,
      'completed_count'       => (int)$completed_count,
      'total_count'           => (int)$total,
      'all_completed'         => $completed_count === $total,
      'completion_percentage' => round(($completed_count / $total) * 100)
    ]);

  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit();
  }
}

function completeAllEvaluation($studentID, $periodId, $teachers, $pdo) {
  try {
    $student_id      = $studentID['student_id'];
    $total           = count($teachers);
    $completed_count = 0;

    // Verify ALL assigned teachers are submitted
    foreach ($teachers as $teacher) {
      $stmt = $pdo->prepare("
          SELECT eval_id 
          FROM evaluation_status 
          WHERE student_id = ? AND load_id = ? AND period_id = ? AND is_submitted = 1
      ");
      $stmt->execute([$student_id, $teacher['load_id'], $periodId]);
      if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        $completed_count++;
      }
    }

    if ($completed_count < $total) {
      http_response_code(400);
      echo json_encode([
        'error'     => 'Please complete all evaluations first',
        'completed' => (int)$completed_count,
        'total'     => (int)$total
      ]);
      exit();
    }

    $pdo->prepare("UPDATE students SET is_finished_all = 1 WHERE student_id = ?")
        ->execute([$student_id]);

    unset($_SESSION['current_teacher_index']);
    unset($_SESSION['period_id']);

    echo json_encode([
      'success'  => true,
      'message'  => 'All evaluations completed',
      'redirect' => '/Smart-Eval/views/student/evaluation_complete.view.php'
    ]);

  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit();
  }
}

function getAllAnswers($studentID, $periodId, $teachers, $pdo) {
  try {
    $student_id = $studentID['student_id'];
    $result = [];

    foreach ($teachers as $teacher) {
      $stmt = $pdo->prepare("
          SELECT ea.question_id, ea.score, ea.comment
          FROM evaluation_status es
          INNER JOIN evaluation_answers ea ON es.eval_id = ea.eval_id
          WHERE es.student_id = ? AND es.load_id = ? AND es.period_id = ? AND es.is_submitted = 1
      ");
      $stmt->execute([$student_id, $teacher['load_id'], $periodId]);
      $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $mapped = [];
      foreach ($answers as $row) {
        $mapped[$row['question_id']] = [
          'score'   => (int)$row['score'],
          'comment' => $row['comment']
        ];
      }

      $result[] = [
        'teacher' => [
          'load_id'    => (int)$teacher['load_id'],
          'full_name'  => $teacher['full_name'],
          'department' => $teacher['department'],
          'image_path' => $teacher['image_path'] ?? 'default.png',
        ],
        'answers' => $mapped
      ];
    }

    echo json_encode([
      'success' => true,
      'data'    => $result
    ]);

  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit();
  }
}
?>