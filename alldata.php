<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Konten - Lihat Semua</title>
    <style>
        * {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #f5f7fb;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            color: #4a6cf7;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
        }

        h2 {
            color: #1f2937;
            margin: 20px 0 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }

        .section {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background-color: #f8fafc;
            font-weight: 600;
            color: #4b5563;
        }

        tr:hover {
            background-color: #f1f5f9;
        }

        .truncate {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .back-link {
            display: inline-block;
            margin: 20px 0;
            color: #4a6cf7;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .tag {
            display: inline-block;
            background-color: #e5e7eb;
            color: #4b5563;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-right: 5px;
        }

        .status-published {
            background-color: #10b981;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .status-draft {
            background-color: #f59e0b;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .status-archived {
            background-color: #6b7280;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Data Manajemen Konten</h1>
        <a href="index.html" class="back-link">← Kembali ke Dashboard</a>

        <?php
        // Koneksi database
        require_once 'config/database.php';

        try {
            // Users Table
            echo '<div class="section">';
            echo '<h2>Tabel Users</h2>';
            $stmt = $pdo->query("SELECT * FROM users ORDER BY user_id");
            $users = $stmt->fetchAll();

            echo '<table>';
            echo '<tr><th>ID</th><th>Username</th><th>Nama</th><th>Email</th><th>Role</th><th>Last Login</th></tr>';
            foreach ($users as $user) {
                echo '<tr>';
                echo '<td>' . $user['user_id'] . '</td>';
                echo '<td>' . htmlspecialchars($user['username']) . '</td>';
                echo '<td>' . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . '</td>';
                echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                echo '<td>' . htmlspecialchars($user['role']) . '</td>';
                echo '<td>' . $user['last_login'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';

            // Categories Table
            echo '<div class="section">';
            echo '<h2>Tabel Categories</h2>';
            $stmt = $pdo->query("SELECT * FROM categories ORDER BY category_id");
            $categories = $stmt->fetchAll();

            echo '<table>';
            echo '<tr><th>ID</th><th>Category Name</th><th>Parent Category</th></tr>';
            foreach ($categories as $category) {
                $parentName = '';
                if ($category['parent_category_id']) {
                    foreach ($categories as $parent) {
                        if ($parent['category_id'] == $category['parent_category_id']) {
                            $parentName = $parent['category_name'];
                            break;
                        }
                    }
                }

                echo '<tr>';
                echo '<td>' . $category['category_id'] . '</td>';
                echo '<td>' . htmlspecialchars($category['category_name']) . '</td>';
                echo '<td>' . htmlspecialchars($parentName) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';

            // Articles Table
            echo '<div class="section">';
            echo '<h2>Tabel Articles</h2>';
            $stmt = $pdo->query("
                SELECT a.*, u.username, c.category_name 
                FROM articles a
                LEFT JOIN users u ON a.author_id = u.user_id
                LEFT JOIN categories c ON a.category_id = c.category_id
                ORDER BY a.article_id
            ");
            $articles = $stmt->fetchAll();

            echo '<table>';
            echo '<tr><th>ID</th><th>Title</th><th>Author</th><th>Category</th><th>Status</th><th>Views</th><th>Likes</th><th>Created</th></tr>';
            foreach ($articles as $article) {
                $statusClass = 'status-' . $article['status'];

                echo '<tr>';
                echo '<td>' . $article['article_id'] . '</td>';
                echo '<td class="truncate">' . htmlspecialchars($article['title']) . '</td>';
                echo '<td>' . htmlspecialchars($article['username']) . '</td>';
                echo '<td>' . htmlspecialchars($article['category_name']) . '</td>';
                echo '<td><span class="' . $statusClass . '">' . htmlspecialchars($article['status']) . '</span></td>';
                echo '<td>' . $article['views'] . '</td>';
                echo '<td>' . $article['likes'] . '</td>';
                echo '<td>' . $article['created'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';

            // Comments Table
            echo '<div class="section">';
            echo '<h2>Tabel Comments</h2>';
            $stmt = $pdo->query("
                SELECT c.*, a.title as article_title, u.username 
                FROM comments c
                LEFT JOIN articles a ON c.article_id = a.article_id
                LEFT JOIN users u ON c.user_id = u.user_id
                ORDER BY c.comment_id
            ");
            $comments = $stmt->fetchAll();

            echo '<table>';
            echo '<tr><th>ID</th><th>Article</th><th>User</th><th>Comment</th><th>Approved</th><th>Created</th></tr>';
            foreach ($comments as $comment) {
                $commenter = $comment['username'] ? $comment['username'] : ($comment['name'] ? $comment['name'] . ' (Guest)' : 'Anonymous');

                echo '<tr>';
                echo '<td>' . $comment['comment_id'] . '</td>';
                echo '<td class="truncate">' . htmlspecialchars($comment['article_title']) . '</td>';
                echo '<td>' . htmlspecialchars($commenter) . '</td>';
                echo '<td class="truncate">' . htmlspecialchars($comment['comment']) . '</td>';
                echo '<td>' . ($comment['is_approved'] ? 'Yes' : 'No') . '</td>';
                echo '<td>' . $comment['created'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';

            // Media Table
            echo '<div class="section">';
            echo '<h2>Tabel Media</h2>';
            $stmt = $pdo->query("
                SELECT m.*, a.title as article_title, u.username 
                FROM media m
                LEFT JOIN articles a ON m.article_id = a.article_id
                LEFT JOIN users u ON m.uploader_id = u.user_id
                ORDER BY m.media_id
            ");
            $medias = $stmt->fetchAll();

            echo '<table>';
            echo '<tr><th>ID</th><th>Filename</th><th>Type</th><th>Article</th><th>Uploader</th><th>Uploaded</th></tr>';
            foreach ($medias as $media) {
                echo '<tr>';
                echo '<td>' . $media['media_id'] . '</td>';
                echo '<td class="truncate">' . htmlspecialchars($media['filename']) . '</td>';
                echo '<td>' . htmlspecialchars($media['type']) . '</td>';
                echo '<td class="truncate">' . htmlspecialchars($media['article_title']) . '</td>';
                echo '<td>' . htmlspecialchars($media['username']) . '</td>';
                echo '<td>' . $media['uploaded'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';
        } catch (PDOException $e) {
            echo '<div class="section">';
            echo '<h2>Error</h2>';
            echo '<p>Error connecting to database: ' . $e->getMessage() . '</p>';
            echo '</div>';
        }
        ?>

        <a href="index.html" class="back-link">← Kembali ke Dashboard</a>
    </div>
</body>

</html>