<?php
session_start();
require_once 'CourseController.php';

$courseController = new CourseController();

$courseId = $_GET['course_id'] ?? 1;
$section = $_GET['section'] ?? 'intro';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Course Preview - LMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6;
        }
    </style>
</head>
<body class="min-h-screen">
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="index.php" class="text-2xl font-bold text-gray-900">LMS</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="p-6">
                    <?php
                    ob_start();
                    $courseController->showPreviewContent($courseId, $section);
                    $content = ob_get_clean();
                    
                    $lines = explode("\n", htmlspecialchars($content));
                    foreach ($lines as $line) {
                        if (strpos($line, "Title:") === 0) {
                            echo "<h1 class='text-2xl font-bold text-gray-900 mb-4'>" . substr($line, 6) . "</h1>";
                        } elseif (strpos($line, "Content:") === 0) {
                            echo "<div class='prose max-w-none mt-4'>" . substr($line, 8) . "</div>";
                        } elseif (strpos($line, "Flag:") === 0) {
                            echo "<div class='mt-4 p-4 bg-yellow-100 rounded-md'>" . $line . "</div>";
                        } else {
                            echo "<p class='mt-2 text-gray-600'>" . $line . "</p>";
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html> 