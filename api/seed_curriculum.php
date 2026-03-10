<?php
require_once '../config.php';

// 1. Cleanup old data
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$pdo->exec("TRUNCATE activity_questions");
$pdo->exec("TRUNCATE activity_submissions");
$pdo->exec("TRUNCATE activities");
$pdo->exec("TRUNCATE lessons");
$pdo->exec("TRUNCATE subjects");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

$teacher_id = 1;

$curriculum = [
    // KINDERGARTEN
    ['grade' => 'K', 'subject' => 'The ABCs', 'icon' => 'fas fa-font', 'title' => 'The Magic Letter A', 
     'content' => "Look at the letter A! It looks like a tall mountain. \n\n'A' is for Apple! 🍎 \n'A' is for Ant! 🐜 \n\nCan you say Aaaa?",
     'questions' => [['q' => 'What starts with the letter A?', 'a' => 'Apple', 'b' => 'Ball', 'c' => 'Cat', 'd' => 'Dog', 'correct' => 'A']]],
    
    ['grade' => 'K', 'subject' => 'Fun Numbers', 'icon' => 'fas fa-sort-numeric-up', 'title' => 'Counting 1, 2, 3!', 
     'content' => "Let's count our fingers! \n\n1... One Sun! ☀️ \n2... Two Eyes! 👀 \n3... Three Little Pigs! 🐷🐷🐷",
     'questions' => [['q' => 'How many suns are in the sky?', 'a' => 'One', 'b' => 'Two', 'c' => 'Three', 'd' => 'Four', 'correct' => 'A']]],

    ['grade' => 'K', 'subject' => 'Our World', 'icon' => 'fas fa-globe-europe', 'title' => 'Rainbow Colors', 
     'content' => "The Sun is Yellow. ☀️ \nThe Grass is Green. 🌱 \nThe Sky is Blue. ☁️",
     'questions' => [['q' => 'What color is the Sun?', 'a' => 'Blue', 'b' => 'Red', 'c' => 'Yellow', 'd' => 'Green', 'correct' => 'C']]],

    // GRADE 1
    ['grade' => '1', 'subject' => 'Mathematics', 'icon' => 'fas fa-calculator', 'title' => 'Addition Basics', 
     'content' => 'Addition means putting groups together. 2 + 2 = 4. 3 + 2 = 5!',
     'questions' => [['q' => 'What is 4 + 3?', 'a' => '6', 'b' => '7', 'c' => '8', 'd' => '9', 'correct' => 'B']]],
    
    ['grade' => '1', 'subject' => 'Reading Time', 'icon' => 'fas fa-book-open', 'title' => 'Punctuation', 
     'content' => 'Sentences start with Big Letters and end with a Dot (.) or Question Mark (?).',
     'questions' => [['q' => 'What goes at the end of "How are you"?', 'a' => '.', 'b' => '?', 'c' => '!', 'd' => ',', 'correct' => 'B']]],

    // GRADE 2
    ['grade' => '2', 'subject' => 'Numbers & Logic', 'icon' => 'fas fa-brain', 'title' => 'Place Value', 
     'content' => 'Numbers have places! In 123, 1 is in the Hundreds, 2 is in the Tens, and 3 is in the Ones.',
     'questions' => [['q' => 'In 54, which number is in the TENS place?', 'a' => '5', 'b' => '4', 'c' => '50', 'd' => '0', 'correct' => 'A']]],
    
    ['grade' => '2', 'subject' => 'Science Explorer', 'icon' => 'fas fa-flask', 'title' => 'The Water Cycle', 
     'content' => 'Water moves in a circle! Evaporation (up), Condensation (clouds), and Precipitation (rain).',
     'questions' => [['q' => 'What is rain called?', 'a' => 'Evaporation', 'b' => 'Condensation', 'c' => 'Precipitation', 'd' => 'Storm', 'correct' => 'C']]],

    // GRADE 4
    ['grade' => '4', 'subject' => 'Science & Energy', 'icon' => 'fas fa-bolt', 'title' => 'Energy Sources', 
     'content' => 'Energy comes from the Sun (Solar), Wind, and Fossil Fuels (Coal, Oil).',
     'questions' => [['q' => 'Which is a renewable energy source?', 'a' => 'Coal', 'b' => 'Oil', 'c' => 'Wind', 'd' => 'Gas', 'correct' => 'C']]],

    // GRADE 6
    ['grade' => '6', 'subject' => 'Life Sciences', 'icon' => 'fas fa-microscope', 'title' => 'Plant vs Animal Cells', 
     'content' => 'Cells are building blocks! Plant cells have a CELL WALL, but animal cells do not.',
     'questions' => [['q' => 'Which is ONLY in plant cells?', 'a' => 'Nucleus', 'b' => 'Cell Wall', 'c' => 'Cytoplasm', 'd' => 'Membrane', 'correct' => 'B']]],
];

echo "<h2>Differentiating Subjects by Grade Level...</h2>";

$subject_ids = []; 

foreach ($curriculum as $item) {
    $grade = $item['grade'];
    $sname = $item['subject'];
    $key = $grade . '_' . $sname;

    if (!isset($subject_ids[$key])) {
        // Create subject for this grade
        $stmt = $pdo->prepare("INSERT INTO subjects (name, grade_level, icon) VALUES (?, ?, ?)");
        $stmt->execute([$sname, $grade, $item['icon']]);
        $subject_ids[$key] = $pdo->lastInsertId();
    }
    
    $sid = $subject_ids[$key];
    
    // Insert Lesson
    $stmt = $pdo->prepare("INSERT INTO lessons (teacher_id, subject_id, grade_level, title, content) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$teacher_id, $sid, $grade, $item['title'], $item['content']]);
    $lid = $pdo->lastInsertId();

    // Insert Activity
    $stmt = $pdo->prepare("INSERT INTO activities (lesson_id, title, activity_type, points_reward) VALUES (?, ?, 'quiz', 50)");
    $stmt->execute([$lid, $item['title'] . ' Quiz']);
    $aid = $pdo->lastInsertId();

    // Insert Questions
    $stmt = $pdo->prepare("INSERT INTO activity_questions (activity_id, question_text, option_a, option_b, option_c, option_d, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($item['questions'] as $q) {
        $stmt->execute([$aid, $q['q'], $q['a'], $q['b'], $q['c'], $q['d'], $q['correct']]);
    }
}

echo "<h3>Success! Each grade now has its own unique subjects and lessons.</h3>";
?>
