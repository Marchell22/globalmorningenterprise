-- Menggunakan database yang benar
USE globalmorningenterprise;

-- Menonaktifkan pengecekan foreign key
SET FOREIGN_KEY_CHECKS = 0;

-- Menggunakan DELETE untuk menghapus data dari tabel-tabel
DELETE FROM comments;
DELETE FROM media;
DELETE FROM articles;
DELETE FROM categories;
DELETE FROM users;

-- Aktifkan kembali pengecekan foreign key
SET FOREIGN_KEY_CHECKS = 1;

-- Reset AUTO_INCREMENT untuk setiap tabel
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE categories AUTO_INCREMENT = 1;
ALTER TABLE articles AUTO_INCREMENT = 1;
ALTER TABLE comments AUTO_INCREMENT = 1;
ALTER TABLE media AUTO_INCREMENT = 1;

-- Insert data dummy untuk tabel users (3 data)
INSERT INTO users (user_id, username, first_name, last_name, email, password, user_info, role, last_login, created) VALUES
(1, 'johndoe', 'John', 'Doe', 'john.doe@example.com', '$2y$10$abcdefghijklmnopqrstuv', 'Senior editor', 'editor', NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 60 DAY),
(2, 'janedoe', 'Jane', 'Doe', 'jane.doe@example.com', '$2y$10$abcdefghijklmnopqrstuv', 'Tech journalist', 'author', NOW() - INTERVAL 1 DAY, NOW() - INTERVAL 45 DAY),
(3, 'bobsmith', 'Bob', 'Smith', 'bob.smith@example.com', '$2y$10$abcdefghijklmnopqrstuv', 'Freelance writer', 'author', NOW() - INTERVAL 3 DAY, NOW() - INTERVAL 30 DAY);

-- Insert data dummy untuk tabel categories (3 data)
INSERT INTO categories (category_id, category_name, parent_category_id) VALUES
(1, 'Technology', NULL),
(2, 'Lifestyle', NULL),
(3, 'Entertainment', NULL);

-- Insert data dummy untuk tabel articles (3 data)
INSERT INTO articles (article_id, title, content, author_id, status, category_id, tags, views, likes, created, modified) VALUES
(1, 'Getting Started with React Hooks', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam eleifend odio at magna pretium suscipit.', 2, 'published', 1, 'react,javascript,frontend', 1240, 56, '2023-12-15 10:23:45', '2023-12-15 14:35:22'),
(2, 'Top 10 European Travel Destinations', 'Mauris placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra.', 3, 'published', 2, 'travel,europe,vacation', 3245, 187, '2023-11-22 09:45:22', '2023-11-22 11:30:18'),
(3, 'The Future of AI in Entertainment', 'Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante.', 1, 'draft', 3, 'ai,entertainment,future', 0, 0, '2023-10-10 14:35:10', '2023-10-10 16:40:37');

-- Insert data dummy untuk tabel comments (3 data)
INSERT INTO comments (comment_id, article_id, user_id, name, email, comment, created, is_approved) VALUES
(1, 1, 3, NULL, NULL, 'Great article! This really helped me understand React Hooks.', '2023-12-16 10:23:45', 1),
(2, 1, NULL, 'Guest User', 'guest@example.com', 'Can you explain the difference between useEffect and componentDidMount?', '2023-12-17 15:30:20', 1),
(3, 2, 2, NULL, NULL, 'I visited Paris last year, it was amazing!', '2023-11-23 08:15:30', 1);

-- Insert data dummy untuk tabel media (3 data)
INSERT INTO media (media_id, filename, path, type, size, article_id, uploaded, uploader_id) VALUES
(1, 'react-hooks-banner.jpg', '/uploads/images/2023/12/react-hooks-banner.jpg', 'image/jpeg', 245678, 1, '2023-12-15 09:15:30', 2),
(2, 'paris-eiffel.jpg', '/uploads/images/2023/12/paris-eiffel.jpg', 'image/jpeg', 432567, 2, '2023-11-22 09:30:20', 3),
(3, 'ai-future.png', '/uploads/images/2023/10/ai-future.png', 'image/png', 156789, 3, '2023-10-10 14:10:15', 1);