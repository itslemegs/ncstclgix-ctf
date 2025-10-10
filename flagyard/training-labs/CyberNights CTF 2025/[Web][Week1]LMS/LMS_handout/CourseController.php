<?php
class CourseController {
    private $courses;

    public function __construct() {
        $this->courses = [
            1 => [
                'id' => 1,
                'title' => 'Web Development Fundamentals',
                'description' => 'Learn the basics of HTML, CSS, and JavaScript to build modern websites.',
                'preview_length' => 1,
                'content' => 'In this comprehensive course, you\'ll learn modern web development from scratch...',
                'is_premium' => false,
                'sections' => ['intro', 'html-basics', 'css-fundamentals']
            ],
            2 => [
                'id' => 2,
                'title' => 'Advanced Security Concepts',
                'description' => 'Deep dive into cybersecurity concepts, vulnerabilities, and secure coding practices.',
                'preview_length' => 0,
                'content' => 'Master advanced security concepts including session management, authentication flows...',
                'flag' => getenv('DYN_FLAG') ?: 'FlagY{test_flag}',
                'is_premium' => true,
                'sections' => ['intro', 'advanced', 'practice']
            ],
            3 => [
                'id' => 3,
                'title' => 'Cloud Architecture',
                'description' => 'Learn to design and implement scalable cloud solutions.',
                'preview_length' => 0,
                'content' => 'Explore cloud computing concepts, AWS services, and best practices...',
                'is_premium' => true,
                'sections' => ['intro', 'basics', 'advanced']
            ],
            4 => [
                'id' => 4,
                'title' => 'Python Programming',
                'description' => 'From basics to advanced Python programming concepts.',
                'preview_length' => 0.8,
                'content' => 'Learn Python programming through practical examples and projects...',
                'is_premium' => false,
                'sections' => ['intro', 'basics', 'advanced']
            ]
        ];
    }

    public function startPreview($courseId, $section = 'intro') {
        $course = $this->courses[$courseId] ?? null;
        if (!$course) {
            echo "Course not found\n";
            return;
        }

        $_SESSION['preview_state'] = [
            'course_id' => $courseId,
            'section' => $section,
            'started' => time(),
            'expires' => time() + ($course['preview_length'] > 0 ? $course['preview_length'] : 0),
            'token' => bin2hex(random_bytes(16)),
            'progress' => []
        ];
    }

    public function viewSection($courseId, $section) {
        $course = $this->courses[$courseId] ?? null;
        if (!$course) {
            echo "Course not found\n";
            return;
        }

        if (!isset($_SESSION['user_id'])) {
            echo "Access denied: Please purchase this course or log in to view content.\n";
            return;
        }

        $this->displaySection($course, $section);
    }

    public function showPreviewContent($courseId, $section) {
        $course = $this->courses[$courseId] ?? null;
        if (!$course) {
            echo "Course not found\n";
            return;
        }

        if (!isset($_SESSION['preview_state'])) {
            echo "Preview session not found\n";
            return;
        }

        $state = $_SESSION['preview_state'];
        $currentTime = time();

        if ($section !== 'intro' && (!isset($state['progress'][$courseId]) || !in_array('intro', $state['progress'][$courseId]))) {
            echo "Please view intro section first\n";
            return;
        }

        if ($currentTime > $state['expires']) {
            unset($_SESSION['preview_state']);
            echo "Preview has expired\n";
            return;
        }

        if ($section === 'intro') {
            if (!isset($_SESSION['preview_state']['progress'][$courseId])) {
                $_SESSION['preview_state']['progress'][$courseId] = [];
            }
            if (!in_array('intro', $_SESSION['preview_state']['progress'][$courseId])) {
                $_SESSION['preview_state']['progress'][$courseId][] = 'intro';
            }
        }

        echo "Title: {$course['title']} - {$section}\n";
        echo "Content: {$course['content']}\n";
        
        if (isset($course['flag']) && 
            isset($state['progress'][1]) && in_array('intro', $state['progress'][1]) &&
            isset($state['progress'][2]) && in_array('intro', $state['progress'][2]) &&
            $section === 'advanced') {
            echo "Flag: {$course['flag']}\n";
        }
    }

    private function displaySection($course, $section) {
        echo "Title: {$course['title']} - {$section}\n";
        echo "Content: {$course['content']}\n";
        
        if (isset($course['flag'])) {
            echo "Flag: {$course['flag']}\n";
        }
    }

    public function getCourses() {
        $courses = [];
        foreach ($this->courses as $course) {
            $courses[] = [
                'id' => $course['id'],
                'title' => $course['title'],
                'description' => $course['description'],
                'preview_length' => $course['preview_length'] ?? 0,
                'is_premium' => $course['is_premium'],
                'sections' => $course['sections']
            ];
        }
        return $courses;
    }
} 