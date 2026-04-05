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

// Get student info
$student_stmt = $pdo->prepare("
    SELECT s.student_id, s.program_id, p.department
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
try{
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

  // Store period_id in session for later use
  $_SESSION['period_id'] = $period_id;
} 
catch (Exception $e){
  http_response_code(500);
  echo json_encode(['error' => 'Database error: '.$e->getMessage()]);
  exit();
}

// Get teachers assigned to this student for the current period
try {
  $teacher_stmt = $pdo->prepare("
      SELECT tl.load_id, t.teacher_id, t.full_name, t.department, t.email, t.image_path
      FROM teacher_load tl
      INNER JOIN teachers t ON tl.teacher_id = t.teacher_id
      INNER JOIN students s ON tl.program_id = s.program_id
      WHERE s.student_id = ? 
        AND tl.program_id = s.program_id
      ORDER BY t.full_name ASC
  ");
  $teacher_stmt->execute([$studentID['student_id']]);
  $teachers = $teacher_stmt->fetchAll(PDO::FETCH_ASSOC);

  if (!$teachers) {
      http_response_code(400);
      echo json_encode(['error' => 'No teachers assigned for your program']);
      exit();
  }
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Database error: '.$e->getMessage()]);
  exit();
}

// Keep track of current teacher index in session
$current_index = $_SESSION['current_teacher_index'] ?? 0;

switch ($action) {
    case 'get_teacher':
      getTeacher($teachers, $current_index, $period_id, $pdo, $student);
      break;

    case 'get_questions':
      getQuestions($set_id, $pdo);
      break;

    case 'submit_evaluation':
      submitEvaluation($studentID, $period_id, $set_id, $pdo);
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
      completeAllEvaluation($studentID, $period_id, $pdo);
      break;

    default:
      http_response_code(400);
      echo json_encode(['error' => 'Invalid action']);
      exit();
}

/**
 * Get current teacher with evaluation status and previous answers
 */
function getTeacher($teachers, $currentIndex, $period_id, $pdo, $student) {
  if ($currentIndex >= count($teachers)) {
      http_response_code(400);
      echo json_encode(['error' => 'No more teachers']);
      exit();
  }

  $teacher = $teachers[$currentIndex];
  $student_id = $student['student_id'];

  // Get evaluation status for this teacher
  $status_query = "SELECT eval_id, is_submitted FROM evaluation_status 
                   WHERE student_id = ? AND load_id = ? AND period_id = ?";
  $stmt = $pdo->prepare($status_query);
  $stmt->execute([$student_id, $teacher['load_id'], $period_id]);
  $eval_status = $stmt->fetch(PDO::FETCH_ASSOC);

  // Get previous answers - CRITICAL FIX
  $previous_answers = [];
  if ($eval_status && $eval_status['eval_id']) {
    $answers_query = "SELECT question_id, score, comment FROM evaluation_answers WHERE eval_id = ?";
    $stmt = $pdo->prepare($answers_query);
    $stmt->execute([$eval_status['eval_id']]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        $previous_answers[$row['question_id']] = [
            'score' => (int)$row['score'],
            'comment' => $row['comment']
        ];
    }
  }

  echo json_encode([
    'success' => true,
    'teacher' => [
        'load_id' => (int)$teacher['load_id'],
        'teacher_id' => (int)$teacher['teacher_id'],
        'full_name' => $teacher['full_name'],
        'department' => $teacher['department'],
        'email' => $teacher['email'],
        'image_path' => $teacher['image_path'] ?? 'default.png'
    ],
    'current_index' => (int)$currentIndex,
    'total_teachers' => (int)count($teachers),
    'progress_percentage' => round((($currentIndex + 1) / count($teachers)) * 100),
    'is_submitted' => $eval_status ? (int)$eval_status['is_submitted'] : 0,
    'previous_answers' => $previous_answers,
    'eval_id' => $eval_status ? (int)$eval_status['eval_id'] : null,
    'period_id' => (int)$period_id
  ]);
}

/**
 * Get all questions for current evaluation period
 */
function getQuestions($setId, $pdo) {
  try {
    $stmt = $pdo->prepare("SELECT question_id, question_text, category FROM questions WHERE set_id = ? ORDER BY question_id ASC");
    $stmt->execute([$setId]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$questions) {
      http_response_code(400);
      echo json_encode(['error' => 'No questions found for this evaluation period']);
      exit();
    }

    echo json_encode([
        'success' => true,
        'questions' => $questions,
        'question_count' => count($questions)
    ]);
  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: '.$e->getMessage()]);
    exit();
  }
}

/**
 * Submit evaluation for current teacher
 */
function submitEvaluation($studentID, $periodId, $setId, $pdo) {
  $data = json_decode(file_get_contents('php://input'), true);

  if (!$data || !isset($data['load_id']) || !isset($data['answers'])) {
      http_response_code(400);
      echo json_encode(['error' => 'Missing required fields: load_id or answers']);
      exit();
  }

  $load_id = intval($data['load_id']);
  $answers = $data['answers'];
  $student_id = $studentID['student_id'];

  if(empty($answers)) {
    http_response_code(400);
    echo json_encode(['error' => 'No answers provided']);
    exit();
  }

  // Get total questions in this evaluation period
  try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM questions WHERE set_id = ?");
    $stmt->execute([$setId]);
    $total_questions = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    if(count($answers) < $total_questions) {
       http_response_code(400);
      echo json_encode(['error' => 'Please answer all questions. Found ' . count($answers) . ' answers but need ' . $total_questions]);
      exit();
    }

    // Check if evaluation exists
    $stmt = $pdo->prepare("SELECT eval_id FROM evaluation_status WHERE student_id = ? AND load_id = ? AND period_id = ?");
    $stmt->execute([$student_id, $load_id, $periodId]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
      // Update existing evaluation
      $eval_id = $existing['eval_id'];
      $pdo->prepare("UPDATE evaluation_status SET is_submitted = 1, date_taken = NOW() WHERE eval_id = ?")->execute([$eval_id]);
      // Delete old answers to avoid duplicates
      $pdo->prepare("DELETE FROM evaluation_answers WHERE eval_id = ?")->execute([$eval_id]);
    } else {
      // Create new evaluation
      $stmt = $pdo->prepare("INSERT INTO evaluation_status (student_id, load_id, period_id, is_submitted, date_taken) VALUES (?, ?, ?, 1, NOW())");
      $stmt->execute([$student_id, $load_id, $periodId]);
      $eval_id = $pdo->lastInsertId();
    }

    // Insert all answers
    $stmt = $pdo->prepare("INSERT INTO evaluation_answers (eval_id, question_id, score, comment) VALUES (?, ?, ?, ?)");
    
    foreach ($answers as $question_id => $answer) {
        $score = intval($answer['score']);
        $comment = isset($answer['comment']) ? trim($answer['comment']) : '';
        
        if ($score < 1 || $score > 5) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid score value for question ' . $question_id]);
            exit();
        }
        
        $stmt->execute([$eval_id, intval($question_id), $score, $comment]);
    }

    echo json_encode([
      'success' => true, 
      'message' => 'Evaluation submitted successfully', 
      'eval_id' => (int)$eval_id
    ]);

  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: '.$e->getMessage()]);
    exit();
  }
}

/**
 * Navigate to next teacher
 */
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
    'success' => true, 
    'is_last' => false, 
    'current_index' => (int)$nextIndex, 
    'total_teachers' => (int)count($teachers)
  ]);
}

/**
 * Navigate to previous teacher
 */
function previousTeacher($teachers, $currentIndex) {
  $prevIndex = $currentIndex - 1;
  
  if ($prevIndex < 0) {
      http_response_code(400);
      echo json_encode(['error' => 'No previous teacher']);
      exit();
  }
  
  $_SESSION['current_teacher_index'] = $prevIndex;
  echo json_encode([
    'success' => true, 
    'current_index' => (int)$prevIndex, 
    'total_teachers' => (int)count($teachers)
  ]);
}

/**
 * Check completion status
 */
function checkCompletion($studentID, $periodId, $teachers, $pdo) {
  try {
    $student_id = $studentID['student_id'];
    $completed_count = 0;
    
    foreach($teachers as $teacher) {
      $stmt = $pdo->prepare("SELECT eval_id FROM evaluation_status WHERE student_id = ? AND load_id = ? AND period_id = ? AND is_submitted = 1");
      $stmt->execute([$student_id, $teacher['load_id'], $periodId]);
      if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        $completed_count++;
      }
    }

    $total = count($teachers);
    echo json_encode([
        'success' => true,
        'completed_count' => (int)$completed_count,
        'total_count' => (int)$total,
        'all_completed' => $completed_count === $total,
        'completion_percentage' => round(($completed_count / $total) * 100)
    ]);
  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: '.$e->getMessage()]);
    exit();
  }
}

/**
 * Complete all evaluations
 */
function completeAllEvaluation($studentID, $periodId, $pdo) {
  try {
    $student_id = $studentID['student_id'];
    
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT load_id) as total FROM evaluation_status WHERE student_id = ? AND period_id = ? AND is_submitted = 1");
    $stmt->execute([$student_id, $periodId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if($row['total'] > 0) {
      $pdo->prepare("UPDATE students SET is_finished_all = 1 WHERE student_id = ?")->execute([$student_id]);

      // Clear session
      unset($_SESSION['current_teacher_index']);
      unset($_SESSION['period_id']);

      echo json_encode([
        'success' => true, 
        'message' => 'All evaluations completed', 
        'redirect' => '/Smart-Eval/views/student/evaluation_complete.view.php'
      ]);
    } else {
      http_response_code(400);
      echo json_encode(['error' => 'No evaluations submitted']);
    }
  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: '.$e->getMessage()]);
    exit();
  }
}

?>