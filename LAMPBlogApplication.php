<?php
// This is a single-file, self-contained PHP application for demonstration purposes.
// It simulates a basic blog to showcase LAMP stack skills (PHP, MySQL, HTML, CSS, JS).

// --- Configuration ---
// This would be external in a real application.
$db_host = 'localhost';
$db_user = 'your_db_user';
$db_pass = 'your_db_password';
$db_name = 'your_db_name';

// --- Database Connection & Setup ---
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create 'posts' table if it doesn't exist
$sql_create_table = "CREATE TABLE IF NOT EXISTS posts (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql_create_table);

// Insert some dummy data if the table is empty
$sql_check_empty = "SELECT COUNT(*) FROM posts";
$result_count = $conn->query($sql_check_empty);
$row = $result_count->fetch_row();
if ($row[0] == 0) {
    $sql_insert_data = "INSERT INTO posts (title, content) VALUES
    ('My First Blog Post', 'This is the content for my very first blog post. It demonstrates the ability to create, read, and display content from a MySQL database using PHP.'),
    ('A Second Great Post', 'This is another example post, showing how the application can handle multiple entries. It is a good example of how a blog can be created with PHP and MySQL.'),
    ('A Third Post on Web Development', 'This post discusses the fundamentals of front-end and back-end development. It is a great way to show how a blog can be used to share knowledge.');";
    $conn->multi_query($sql_insert_data);
    do {
        // Required to clear results from multi_query for next query to work
    } while ($conn->next_result());
}

// --- Router Logic ---
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$post_id = isset($_GET['id']) ? $_GET['id'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple PHP Blog</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            @apply bg-gray-900 text-gray-200;
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-200">
    <!-- Header -->
    <header class="bg-gray-800 p-6 shadow-lg">
        <nav class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-3xl font-bold text-indigo-400">Your Blog</a>
        </nav>
    </header>

    <main class="container mx-auto p-6">
        <?php if ($page === 'home'): ?>
            <h1 class="text-4xl font-bold text-center my-8 text-white">Latest Blog Posts</h1>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php
                $sql = "SELECT id, title, SUBSTRING(content, 1, 150) AS content, created_at FROM posts ORDER BY created_at DESC";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<div class="bg-gray-800 p-6 rounded-xl shadow-lg">';
                        echo '<h2 class="text-2xl font-semibold text-white mb-2">' . htmlspecialchars($row['title']) . '</h2>';
                        echo '<p class="text-gray-400 mb-4">' . htmlspecialchars($row['content']) . '...</p>';
                        echo '<a href="?page=post&id=' . htmlspecialchars($row['id']) . '" class="text-indigo-400 hover:text-indigo-300 font-bold transition-colors">Read More &rarr;</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<p class="text-center text-gray-400">No posts found.</p>';
                }
                ?>
            </div>
        <?php elseif ($page === 'post' && $post_id): ?>
            <?php
            $sql = "SELECT title, content, created_at FROM posts WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $post_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $post = $result->fetch_assoc();
                echo '<article class="bg-gray-800 p-8 rounded-xl shadow-lg max-w-3xl mx-auto">';
                echo '<h1 class="text-4xl font-bold text-white mb-4">' . htmlspecialchars($post['title']) . '</h1>';
                echo '<p class="text-sm text-gray-500 mb-6">Published on ' . htmlspecialchars($post['created_at']) . '</p>';
                echo '<div class="prose prose-invert text-gray-300">';
                echo '<p>' . nl2br(htmlspecialchars($post['content'])) . '</p>';
                echo '</div>';
                echo '</article>';
            } else {
                echo '<p class="text-center text-red-400">Post not found.</p>';
            }
            ?>
        <?php else: ?>
            <h1 class="text-4xl font-bold text-center text-red-400">Page Not Found</h1>
        <?php endif; ?>
    </main>
    <!-- Footer -->
    <footer class="bg-gray-800 py-6 text-center text-gray-500 text-sm mt-8">
        <p>&copy; 2025 Simple PHP Blog. All rights reserved.</p>
    </footer>
    <?php $conn->close(); ?>
</body>
</html>
