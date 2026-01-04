CREATE TABLE IF NOT EXISTS blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255),
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS guestbook (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed Blogs
INSERT INTO blogs (title, content, image, date) VALUES 
('Welcome to my Future Portfolio', 'This is the first post on my new holographic portfolio system. Stay tuned for more updates on my projects and experiments with AI and Web3.', 'https://placehold.co/600x400/050510/00f3ff?text=First+Post', '2023-10-27'),
('Test', 'test', 'https://placehold.co/600x400/050510/00f3ff?text=New+Post', '2025-12-17');
