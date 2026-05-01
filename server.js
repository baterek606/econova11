const express = require('express');
const sqlite3 = require('sqlite3').verbose();
const cors = require('cors');
const path = require('path');

const app = express();
app.use(express.json());
app.use(cors());
app.use(express.static(__dirname));

// Initialize SQLite database
const db = new sqlite3.Database('econova.db', (err) => {
    if (err) console.error('Database opening error: ', err);
});

// Schema Setup
db.serialize(() => {
    db.run(`CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT, email TEXT UNIQUE, password TEXT, role TEXT
    )`);
    db.run(`CREATE TABLE IF NOT EXISTS posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        author_name TEXT, location TEXT, time_ago TEXT, type TEXT,
        title TEXT, content TEXT, article_link TEXT,
        likes_count INTEGER DEFAULT 0, comments_count INTEGER DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )`);
    db.run(`CREATE TABLE IF NOT EXISTS campaigns (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT, description TEXT, target_amount INTEGER, raised_amount INTEGER,
        days_left INTEGER, engagement_count INTEGER, badge TEXT
    )`);
    db.run(`CREATE TABLE IF NOT EXISTS impact_stats (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        co2_offset TEXT, trees_planted TEXT, water_liters TEXT
    )`);
    db.run(`CREATE TABLE IF NOT EXISTS stewards (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT, points INTEGER, avatar_id INTEGER
    )`);
    db.run(`CREATE TABLE IF NOT EXISTS explore_posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_name TEXT, user_location TEXT, title TEXT, description TEXT,
        category TEXT, likes_count INTEGER DEFAULT 0, comments_count INTEGER DEFAULT 0,
        before_image TEXT, after_image TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )`);
    db.run(`CREATE TABLE IF NOT EXISTS community_goal (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT, current_amount INTEGER, target_amount INTEGER
    )`);

    // Seed data for Explore
    db.get("SELECT COUNT(*) as count FROM explore_posts", (err, row) => {
        if (row && row.count === 0) {
            db.run(`INSERT INTO explore_posts (user_name, user_location, title, description, category, likes_count, comments_count, before_image, after_image) VALUES 
                ('Sarah Jenkins', 'Pismo Beach, CA', 'Restoring the Shoreline', 'Removed 450lbs of microplastics in just one weekend with local students.', 'beach', 0, 0, 'beach_before.png', 'beach_after.png')`);
            db.run(`INSERT INTO explore_posts (user_name, user_location, title, description, category, likes_count, comments_count, before_image, after_image) VALUES 
                ('Elena Rossi', 'Brooklyn, NY', 'Concrete to Compost', 'Transformed a vacant lot filled with rubble into a thriving food oasis for 50 families.', 'urban', 0, 0, 'urban_before.png', 'urban_after.png')`);
            db.run(`INSERT INTO explore_posts (user_name, user_location, title, description, category, likes_count, comments_count, before_image, after_image) VALUES 
                ('Marcus Thorne', 'Oregon Highlands', 'A New Canopy', 'Cleared out decades of trash from the forest floor, allowing new saplings to finally thrive.', 'forest', 0, 0, 'forest_before.png', 'forest_after.png')`);
            db.run(`INSERT INTO explore_posts (user_name, user_location, title, description, category, likes_count, comments_count, before_image, after_image) VALUES 
                ('David Chen', 'River Delta', 'Reviving the Delta', 'Initiated a massive cleanup project that removed floating plastic and restored water clarity.', 'river', 0, 0, 'https://images.unsplash.com/photo-1621451537084-482c73073e0f?auto=format&fit=crop&q=80&w=600', 'https://images.unsplash.com/photo-1437622368342-7a3d73a34c8f?auto=format&fit=crop&q=80&w=600')`);
        }
    });

    db.get("SELECT COUNT(*) as count FROM community_goal", (err, row) => {
        if (row && row.count === 0) {
            db.run(`INSERT INTO community_goal (title, current_amount, target_amount) VALUES ('Help us plant 10,000 native trees by winter.', 7450, 10000)`);
        }
    });

});

// API Routes
app.get('/api/posts', (req, res) => {
    db.all("SELECT * FROM posts ORDER BY (likes_count + comments_count) DESC", [], (err, rows) => {
        res.json(rows);
    });
});

app.post('/api/posts', (req, res) => {
    const { author_name, location, time_ago, type, title, content, article_link } = req.body;
    db.run(`INSERT INTO posts (author_name, location, time_ago, type, title, content, article_link) VALUES (?, ?, ?, ?, ?, ?, ?)`,
        [author_name, location, time_ago, type, title, content, article_link], function(err) {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ id: this.lastID });
    });
});

app.put('/api/posts/:id', (req, res) => {
    const { title, content } = req.body;
    db.run("UPDATE posts SET title = ?, content = ? WHERE id = ?", [title, content, req.params.id], err => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ success: true });
    });
});

app.delete('/api/posts/:id', (req, res) => {
    db.run("DELETE FROM posts WHERE id = ?", [req.params.id], err => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ success: true });
    });
});

app.get('/api/campaigns', (req, res) => {
    db.all("SELECT * FROM campaigns", [], (err, rows) => {
        res.json(rows);
    });
});

app.post('/api/campaigns', (req, res) => {
    const { title, description, target_amount, raised_amount, days_left, badge } = req.body;
    db.run(`INSERT INTO campaigns (title, description, target_amount, raised_amount, days_left, badge) VALUES (?, ?, ?, ?, ?, ?)`,
        [title, description, target_amount, raised_amount, days_left, badge], function(err) {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ id: this.lastID });
    });
});

app.put('/api/campaigns/:id', (req, res) => {
    const { title, description } = req.body;
    db.run("UPDATE campaigns SET title = ?, description = ? WHERE id = ?", [title, description, req.params.id], err => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ success: true });
    });
});

app.delete('/api/campaigns/:id', (req, res) => {
    db.run("DELETE FROM campaigns WHERE id = ?", [req.params.id], err => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ success: true });
    });
});

app.get('/api/stats', (req, res) => {
    db.get("SELECT * FROM impact_stats LIMIT 1", [], (err, row) => {
        res.json(row);
    });
});

app.get('/api/stewards', (req, res) => {
    db.all("SELECT * FROM stewards ORDER BY points DESC", [], (err, rows) => {
        res.json(rows);
    });
});

app.post('/api/posts/:id/like', (req, res) => {
    db.run("UPDATE posts SET likes_count = likes_count + 1 WHERE id = ?", [req.params.id], err => {
        res.json({ success: true });
    });
});

app.post('/api/posts/:id/comment', (req, res) => {
    db.run("UPDATE posts SET comments_count = comments_count + 1 WHERE id = ?", [req.params.id], err => {
        res.json({ success: true });
    });
});

app.post('/api/auth/signup', (req, res) => {
    const { name, email, password } = req.body;
    db.run(`INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')`, [name, email, password], function(err) {
        if (err) return res.status(400).json({ error: "Email already exists" });
        res.json({ success: true, user: { id: this.lastID, name, email, role: 'user' } });
    });
});

app.post('/api/auth/login', (req, res) => {
    const { email, password } = req.body;
    db.get("SELECT * FROM users WHERE email = ? AND password = ?", [email, password], (err, user) => {
        if (user) {
            res.json({ success: true, user: { id: user.id, name: user.name, email: user.email, role: user.role } });
        } else {
            res.status(401).json({ error: "Invalid credentials" });
        }
    });
});

app.get('/api/explore/posts', (req, res) => {
    const category = req.query.category;
    let query = "SELECT * FROM explore_posts";
    let params = [];
    if (category && category !== 'all') {
        query += " WHERE category = ?";
        params.push(category);
    }
    query += " ORDER BY (likes_count + comments_count) DESC";
    
    db.all(query, params, (err, rows) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(rows);
    });
});

app.get('/api/explore/goal', (req, res) => {
    db.get("SELECT * FROM community_goal LIMIT 1", [], (err, row) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(row);
    });
});

app.post('/api/explore/posts/:id/like', (req, res) => {
    db.run("UPDATE explore_posts SET likes_count = likes_count + 1 WHERE id = ?", [req.params.id], err => {
        res.json({ success: true });
    });
});

app.post('/api/explore/posts/:id/comment', (req, res) => {
    db.run("UPDATE explore_posts SET comments_count = comments_count + 1 WHERE id = ?", [req.params.id], err => {
        res.json({ success: true });
    });
});

app.listen(3000, () => console.log('Server running on port 3000'));
