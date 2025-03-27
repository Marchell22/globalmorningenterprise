<?php
// Koneksi database
require_once '../config/database.php';

// Mendapatkan parameter filter tanggal
$dateRange = isset($_GET['date_range']) ? intval($_GET['date_range']) : 30;

// Membuat tanggal untuk filter
$currentDate = date('Y-m-d H:i:s');
$pastDate = date('Y-m-d H:i:s', strtotime("-$dateRange days"));

// Variabel respons
$response = [];

try {
    // 1. Card Performa Artikel
    // 1a. Menampilkan jumlah artikel - Tetap menampilkan total semua artikel
    $articleCountsQuery = "
        SELECT 
            COUNT(*) AS total,
            SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) AS published,
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS draft,
            SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) AS archived
        FROM 
            articles
    ";

    $stmt = $pdo->prepare($articleCountsQuery);
    $stmt->execute();
    $response['articleCounts'] = $stmt->fetch(PDO::FETCH_ASSOC);

    // 1b. Tampilkan chart dari jumlah artikel yang dipublish berdasarkan filter tanggal
    $monthlyPublishedQuery = "
        SELECT 
            DATE_FORMAT(created, '%Y-%m') AS month,
            COUNT(*) AS count
        FROM 
            articles
        WHERE 
            status = 'published' 
            AND created >= :pastDate
        GROUP BY 
            DATE_FORMAT(created, '%Y-%m')
        ORDER BY 
            month ASC
    ";

    $stmt = $pdo->prepare($monthlyPublishedQuery);
    $stmt->bindParam(':pastDate', $pastDate);
    $stmt->execute();
    $response['monthlyPublished'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Panel Aktifitas Author
    // 2a. Top 5 Author berdasarkan jumlah artikel dalam periode terpilih
    $topAuthorsByArticlesQuery = "
        SELECT 
            users.username,
            COUNT(articles.article_id) AS article_count,
            COALESCE(AVG(articles.views), 0) AS average_views
        FROM 
            articles
            JOIN users ON articles.author_id = users.user_id
        WHERE
            articles.created >= :pastDate
        GROUP BY 
            users.user_id, users.username
        ORDER BY 
            article_count DESC
        LIMIT 5
    ";

    $stmt = $pdo->prepare($topAuthorsByArticlesQuery);
    $stmt->bindParam(':pastDate', $pastDate);
    $stmt->execute();
    $response['topAuthorsByArticles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2b. Top 5 Author berdasarkan jumlah view artikel dalam periode terpilih
    $topAuthorsByViewsQuery = "
        SELECT 
            users.username,
            SUM(articles.views) AS total_views,
            COUNT(articles.article_id) AS article_count
        FROM 
            articles
            JOIN users ON articles.author_id = users.user_id
        WHERE
            articles.created >= :pastDate
        GROUP BY 
            users.user_id, users.username
        ORDER BY 
            total_views DESC
        LIMIT 5
    ";

    $stmt = $pdo->prepare($topAuthorsByViewsQuery);
    $stmt->bindParam(':pastDate', $pastDate);
    $stmt->execute();
    $response['topAuthorsByViews'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2c. Tren kontribusi Author dalam periode terpilih
    $authorContributionsQuery = "
        SELECT 
            users.username AS author,
            DATE_FORMAT(articles.created, '%Y-%m') AS month,
            COUNT(articles.article_id) AS count
        FROM 
            articles
            JOIN users ON articles.author_id = users.user_id
        WHERE 
            articles.created >= :pastDate
        GROUP BY 
            users.username, DATE_FORMAT(articles.created, '%Y-%m')
        ORDER BY 
            month ASC, author ASC
    ";

    $stmt = $pdo->prepare($authorContributionsQuery);
    $stmt->bindParam(':pastDate', $pastDate);
    $stmt->execute();
    $response['authorContributions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Komponen analisa konten
    // 3a. Artikel per kategori dalam periode terpilih
    $articlesByCategoryQuery = "
        SELECT 
            categories.category_name,
            COUNT(articles.article_id) AS count
        FROM 
            articles
            JOIN categories ON articles.category_id = categories.category_id
        WHERE
            articles.created >= :pastDate
        GROUP BY 
            categories.category_id, categories.category_name
        ORDER BY 
            count DESC
    ";

    $stmt = $pdo->prepare($articlesByCategoryQuery);
    $stmt->bindParam(':pastDate', $pastDate);
    $stmt->execute();
    $response['articlesByCategory'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3b. Rata-rata comment per artikel dalam periode terpilih
    $avgCommentsQuery = "
        SELECT 
            AVG(comment_count) AS avg_comments
        FROM (
            SELECT 
                articles.article_id,
                COUNT(comments.comment_id) AS comment_count
            FROM 
                articles
                LEFT JOIN comments ON articles.article_id = comments.article_id
            WHERE
                articles.created >= :pastDate
            GROUP BY 
                articles.article_id
        ) AS article_comments
    ";

    $stmt = $pdo->prepare($avgCommentsQuery);
    $stmt->bindParam(':pastDate', $pastDate);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response['averageComments'] = floatval($result['avg_comments']);

    // 3c. Top 5 artikel dengan comment terbanyak dalam periode terpilih
    $topCommentedArticlesQuery = "
        SELECT 
            articles.title,
            COUNT(comments.comment_id) AS comment_count
        FROM 
            articles
            JOIN comments ON articles.article_id = comments.article_id
        WHERE
            articles.created >= :pastDate
        GROUP BY 
            articles.article_id, articles.title
        ORDER BY 
            comment_count DESC
        LIMIT 5
    ";

    $stmt = $pdo->prepare($topCommentedArticlesQuery);
    $stmt->bindParam(':pastDate', $pastDate);
    $stmt->execute();
    $response['topCommentedArticles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3d. Top 5 artikel dengan view terbanyak dalam periode terpilih
    $topViewedArticlesQuery = "
        SELECT 
            articles.title,
            articles.views,
            categories.category_name
        FROM 
            articles
            LEFT JOIN categories ON articles.category_id = categories.category_id
        WHERE
            articles.created >= :pastDate
        ORDER BY 
            articles.views DESC
        LIMIT 5
    ";

    $stmt = $pdo->prepare($topViewedArticlesQuery);
    $stmt->bindParam(':pastDate', $pastDate);
    $stmt->execute();
    $response['topViewedArticles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mengembalikan hasil dalam format JSON
    header('Content-Type: application/json');
    echo json_encode($response);
} catch (PDOException $e) {
    // Error handling
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}
?>