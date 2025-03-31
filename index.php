<?php
session_start();

// Initialize students array in session if not exists
if (!isset($_SESSION['students'])) {
    $_SESSION['students'] = [];
}

// Peerformance classification
function getPerformanceClassification($average) {
    if ($average >= 90) return 'Excellent';
    if ($average >= 80) return 'Very Good';
    if ($average >= 70) return 'Good';
    if ($average >= 60) return 'Passed';
    return 'Failed';
}

// Handles submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_student'])) {
        // Add new student
        $newStudent = [
            'id' => uniqid(),
            'name' => htmlspecialchars($_POST['name']),
            'age' => intval($_POST['age']),
            'grade_level' => htmlspecialchars($_POST['grade_level']),
            'subjects' => []
        ];
        
        // Process subjects and scores
        $subjectNames = $_POST['subject_name'] ?? [];
        $subjectScores = $_POST['subject_score'] ?? [];
        
        for ($i = 0; $i < count($subjectNames); $i++) {
            if (!empty($subjectNames[$i])) {
                $newStudent['subjects'][] = [
                    'name' => htmlspecialchars($subjectNames[$i]),
                    'score' => intval($subjectScores[$i])
                ];
            }
        }
        
        // Calculate totals
        $total = 0;
        foreach ($newStudent['subjects'] as $subject) {
            $total += $subject['score'];
        }
        
        $newStudent['total'] = $total;
        $newStudent['average'] = count($newStudent['subjects']) > 0 ? $total / count($newStudent['subjects']) : 0;
        $newStudent['performance'] = getPerformanceClassification($newStudent['average']);
        
        $_SESSION['students'][] = $newStudent;
    } elseif (isset($_POST['delete_student'])) {
        // Delete student
        $studentId = $_POST['student_id'];
        $_SESSION['students'] = array_filter($_SESSION['students'], function($student) use ($studentId) {
            return $student['id'] !== $studentId;
        });
    } elseif (isset($_POST['reset_all'])) {
        // Reset all students
        $_SESSION['students'] = [];
    } elseif (isset($_POST['update_student'])) {
        // Update existing student
        $studentId = $_POST['student_id'];
        foreach ($_SESSION['students'] as &$student) {
            if ($student['id'] === $studentId) {
                $student['name'] = htmlspecialchars($_POST['edit_name']);
                $student['age'] = intval($_POST['edit_age']);
                $student['grade_level'] = htmlspecialchars($_POST['edit_grade_level']);
                
                // Process edited subjects
                $student['subjects'] = [];
                $subjectNames = $_POST['edit_subject_name'] ?? [];
                $subjectScores = $_POST['edit_subject_score'] ?? [];
                
                for ($i = 0; $i < count($subjectNames); $i++) {
                    if (!empty($subjectNames[$i])) {
                        $student['subjects'][] = [
                            'name' => htmlspecialchars($subjectNames[$i]),
                            'score' => intval($subjectScores[$i])
                        ];
                    }
                }
                
                // Recalculate totals
                $total = 0;
                foreach ($student['subjects'] as $subject) {
                    $total += $subject['score'];
                }
                
                $student['total'] = $total;
                $student['average'] = count($student['subjects']) > 0 ? $total / count($student['subjects']) : 0;
                $student['performance'] = getPerformanceClassification($student['average']);
                break;
            }
        }
    }
}

// Handle search
$searchResults = [];
if (isset($_GET['search'])) {
    $searchTerm = strtolower(trim($_GET['search']));
    if (!empty($searchTerm)) {
        foreach ($_SESSION['students'] as $student) {
            if (strpos(strtolower($student['name']), $searchTerm) !== false) {
                $searchResults[] = $student;
            }
        }
    }
}

// Get students to display (either search results or all)
$displayStudents = !empty($searchResults) ? $searchResults : $_SESSION['students'];

// Get student to edit if requested
$editStudent = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    foreach ($_SESSION['students'] as $student) {
        if ($student['id'] === $editId) {
            $editStudent = $student;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>School Management System</h1>
        
        <!-- Search Form -->
        <div class="search-section">
            <form method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search student by name..." 
                       value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                <button type="submit" class="btn">Search</button>
                <?php if (!empty($searchResults)): ?>
                    <a href="?" class="btn btn-secondary">Clear Search</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Student Entry Form -->
        <div class="form-section">
            <h2><?= $editStudent ? 'Edit Student' : 'Add New Student' ?></h2>
            <form method="POST">
                <?php if ($editStudent): ?>
                    <input type="hidden" name="student_id" value="<?= $editStudent['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="<?= $editStudent ? 'edit_name' : 'name' ?>">Student Name:</label>
                    <input type="text" id="<?= $editStudent ? 'edit_name' : 'name' ?>" 
                           name="<?= $editStudent ? 'edit_name' : 'name' ?>" 
                           value="<?= $editStudent ? htmlspecialchars($editStudent['name']) : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="<?= $editStudent ? 'edit_age' : 'age' ?>">Age:</label>
                    <input type="number" id="<?= $editStudent ? 'edit_age' : 'age' ?>" 
                           name="<?= $editStudent ? 'edit_age' : 'age' ?>" 
                           value="<?= $editStudent ? $editStudent['age'] : '' ?>" min="5" max="25" required>
                </div>
                
                <div class="form-group">
                    <label for="<?= $editStudent ? 'edit_grade_level' : 'grade_level' ?>">Grade Level:</label>
                    <select id="<?= $editStudent ? 'edit_grade_level' : 'grade_level' ?>" 
                            name="<?= $editStudent ? 'edit_grade_level' : 'grade_level' ?>" required>
                        <option value="">Select Grade</option>
                        <optgroup label="School">
                            <option value="Grade 1" <?= ($editStudent && $editStudent['grade_level'] === 'Grade 1') ? 'selected' : '' ?>>Grade 1</option>
                            <option value="Grade 2" <?= ($editStudent && $editStudent['grade_level'] === 'Grade 2') ? 'selected' : '' ?>>Grade 2</option>
                            <option value="Grade 3" <?= ($editStudent && $editStudent['grade_level'] === 'Grade 3') ? 'selected' : '' ?>>Grade 3</option>
                            <option value="Grade 4" <?= ($editStudent && $editStudent['grade_level'] === 'Grade 4') ? 'selected' : '' ?>>Grade 4</option>
                            <option value="Grade 5" <?= ($editStudent && $editStudent['grade_level'] === 'Grade 5') ? 'selected' : '' ?>>Grade 5</option>
                            <option value="Grade 6" <?= ($editStudent && $editStudent['grade_level'] === 'Grade 6') ? 'selected' : '' ?>>Grade 6</option>
                            <option value="Grade 7" <?= ($editStudent && $editStudent['grade_level'] === 'Grade 7') ? 'selected' : '' ?>>Grade 7</option>
                            <option value="Grade 8" <?= ($editStudent && $editStudent['grade_level'] === 'Grade 8') ? 'selected' : '' ?>>Grade 8</option>
                            <option value="Grade 9" <?= ($editStudent && $editStudent['grade_level'] === 'Grade 9') ? 'selected' : '' ?>>Grade 9</option>
                            <option value="Grade 10" <?= ($editStudent && $editStudent['grade_level'] === 'Grade 10') ? 'selected' : '' ?>>Grade 10</option>
                            <option value="Grade 11" <?= ($editStudent && $editStudent['grade_level'] === 'Grade 11') ? 'selected' : '' ?>>Grade 11</option>
                            <option value="Grade 12" <?= ($editStudent && $editStudent['grade_level'] === 'Grade 12') ? 'selected' : '' ?>>Grade 12</option>
                        </optgroup>
                        <optgroup label="College">
                            <option value="1st Year" <?= ($editStudent && $editStudent['grade_level'] === '1st Year') ? 'selected' : '' ?>>1st Year</option>
                            <option value="2nd Year" <?= ($editStudent && $editStudent['grade_level'] === '2nd Year') ? 'selected' : '' ?>>2nd Year</option>
                            <option value="3rd Year" <?= ($editStudent && $editStudent['grade_level'] === '3rd Year') ? 'selected' : '' ?>>3rd Year</option>
                            <option value="4th Year" <?= ($editStudent && $editStudent['grade_level'] === '4th Year') ? 'selected' : '' ?>>4th Year</option>
                        </optgroup>
                    </select>
                </div>
                
                <h3>Subjects and Scores</h3>
                <div id="subject-container">
                    <?php if ($editStudent): ?>
                        <?php foreach ($editStudent['subjects'] as $index => $subject): ?>
                            <div class="subject-group">
                                <input type="text" name="edit_subject_name[]" placeholder="Subject name" 
                                       value="<?= htmlspecialchars($subject['name']) ?>">
                                <input type="number" name="edit_subject_score[]" placeholder="Score" min="0" max="100" 
                                       value="<?= $subject['score'] ?>">
                                <button type="button" class="remove-subject" onclick="removeSubject(this)">Remove</button>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($editStudent['subjects'])): ?>
                            <div class="subject-group">
                                <input type="text" name="edit_subject_name[]" placeholder="Subject name">
                                <input type="number" name="edit_subject_score[]" placeholder="Score" min="0" max="100">
                                <button type="button" class="remove-subject" onclick="removeSubject(this)">Remove</button>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="subject-group">
                            <input type="text" name="subject_name[]" placeholder="Subject name">
                            <input type="number" name="subject_score[]" placeholder="Score" min="0" max="100">
                            <button type="button" class="remove-subject" onclick="removeSubject(this)">Remove</button>
                        </div>
                    <?php endif; ?>
                </div>
                
                <button type="button" id="add-subject" class="btn">Add Another Subject</button>
                <?php if ($editStudent): ?>
                    <button type="submit" name="update_student" class="btn btn-primary">Update Student</button>
                    <a href="?" class="btn btn-secondary">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="add_student" class="btn btn-primary">Add Student</button>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Students List -->
        <div class="list-section">
            <h2>Student Records</h2>
            
            <?php if (empty($displayStudents)): ?>
                <p>No student records found.</p>
            <?php else: ?>
                <form method="POST" class="reset-form">
                    <button type="submit" name="reset_all" class="btn btn-danger">Reset All Students</button>
                </form>
                
                <div class="student-cards">
                    <?php foreach ($displayStudents as $student): ?>
                        <div class="student-card">
                            <div class="student-header">
                                <h3><?= htmlspecialchars($student['name']) ?> (Age: <?= $student['age'] ?>)</h3>
                                <span class="grade-level"><?= htmlspecialchars($student['grade_level']) ?></span>
                                <div class="student-actions">
                                    <a href="?edit=<?= $student['id'] ?><?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>" 
                                       class="btn btn-edit">Edit</a>
                                    <form method="POST" class="delete-form">
                                        <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                                        <button type="submit" name="delete_student" class="btn btn-danger">Delete</button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="performance <?= strtolower(str_replace(' ', '-', $student['performance'])) ?>">
                                Performance: <?= $student['performance'] ?>
                            </div>
                            
                            <div class="scores-summary">
                                <p>Total Score: <?= $student['total'] ?></p>
                                <p>Average Score: <?= number_format($student['average'], 2) ?></p>
                            </div>
                            
                            <div class="subjects-list">
                                <h4>Subjects:</h4>
                                <ul>
                                    <?php foreach ($student['subjects'] as $subject): ?>
                                        <li><?= htmlspecialchars($subject['name']) ?>: <?= $subject['score'] ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Add new subject field
        document.getElementById('add-subject').addEventListener('click', function() {
            const container = document.getElementById('subject-container');
            const newSubject = document.createElement('div');
            newSubject.className = 'subject-group';
            
            const isEditForm = document.querySelector('input[name="edit_subject_name[]"]') !== null;
            const namePrefix = isEditForm ? 'edit_' : '';
            
            newSubject.innerHTML = `
                <input type="text" name="${namePrefix}subject_name[]" placeholder="Subject name">
                <input type="number" name="${namePrefix}subject_score[]" placeholder="Score" min="0" max="100">
                <button type="button" class="remove-subject" onclick="removeSubject(this)">Remove</button>
            `;
            container.appendChild(newSubject);
        });
        
        // Remove subject field
        function removeSubject(button) {
            const subjectGroup = button.parentElement;
            if (document.querySelectorAll('.subject-group').length > 1) {
                subjectGroup.remove();
            } else {
                // Clear inputs if it's the last one
                const inputs = subjectGroup.querySelectorAll('input');
                inputs.forEach(input => input.value = '');
            }
        }
    </script>
</body>
</html>