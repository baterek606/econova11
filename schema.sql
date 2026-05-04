CREATE TABLE IF NOT EXISTS campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    location VARCHAR(255),
    goal INT NOT NULL,
    trees_planted INT DEFAULT 0,
    volunteers_count INT DEFAULT 0,
    progress_percent INT DEFAULT 0,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    image VARCHAR(255),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample Data
INSERT INTO campaigns (title, description, location, goal, trees_planted, volunteers_count, progress_percent, latitude, longitude, image)
VALUES 
('Amazon Rainforest Restoration', 'Helping restore the Amazon canopy by planting native species.', 'Manaus, Brazil', 50000, 1250, 48, 40, -3.1190, -60.0217, 'forest.png'),
('Atlantic Forest Restoration', 'A community-led project to revive the Atlantic Forest biodiversity.', 'Rio de Janeiro, Brazil', 30000, 500, 20, 50, -22.9068, -43.1729, 'forest.png');
