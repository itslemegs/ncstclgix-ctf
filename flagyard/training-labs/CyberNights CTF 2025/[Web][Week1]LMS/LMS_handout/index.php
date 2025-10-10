<?php
session_start();
require_once 'CourseController.php';

$courseController = new CourseController();

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'start_preview':
            $courseController->startPreview(
                $_GET['course_id'] ?? 1,
                $_GET['section'] ?? 'intro'
            );
            header('Location: preview.php?course_id=' . ($_GET['course_id'] ?? 1) . '&section=' . ($_GET['section'] ?? 'intro'));
            exit;
            break;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>LMS Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6;
        }
        .course-card {
            transition: transform 0.2s;
        }
        .course-card:hover {
            transform: translateY(-5px);
        }
        .premium-badge {
            background: linear-gradient(135deg, #ffd700 0%, #ffaa00 100%);
        }
        .preview-badge {
            background: linear-gradient(135deg, #34d399 0%, #059669 100%);
        }
    </style>
</head>
<body class="min-h-screen">
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900">LMS</h1>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php if (isset($response)): ?>
        <div class="mb-8 bg-white shadow rounded-lg overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Preview Content</h2>
                <pre class="whitespace-pre-wrap text-sm text-gray-600"><?= htmlspecialchars($response) ?></pre>
            </div>
        </div>
        <?php endif; ?>

        <div class="px-4 py-6 sm:px-0">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($courseController->getCourses() as $course): ?>
                <div class="course-card bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-start">
                            <h3 class="text-lg font-medium text-gray-900">
                                <?= htmlspecialchars($course['title']) ?>
                            </h3>
                            <div class="flex space-x-2">
                                <?php if ($course['preview_length'] > 0): ?>
                                    <span class="preview-badge px-2 py-1 text-xs font-semibold rounded-full text-white">
                                        Preview
                                    </span>
                                <?php endif; ?>
                                <?php if ($course['is_premium']): ?>
                                    <span class="premium-badge px-2 py-1 text-xs font-semibold rounded-full text-gray-900">
                                        Premium
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">
                            <?= htmlspecialchars($course['description']) ?>
                        </p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <?php foreach ($course['sections'] as $section): ?>
                                <?php if ($course['preview_length'] > 0): ?>
                                    <a href="?action=start_preview&course_id=<?= $course['id'] ?>&section=<?= $section ?>" 
                                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Preview <?= ucfirst($section) ?>
                                    </a>
                                <?php endif; ?>
                                <a href="view.php?course_id=<?= $course['id'] ?>&section=<?= $section ?>" 
                                   class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    View <?= ucfirst($section) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <?php if (isset($_GET['action'])): ?>
    <div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
        <h3>System Response:</h3>
        <pre style="margin: 0;"></pre>
    </div>
    <?php endif; ?>
</body>
</html> 