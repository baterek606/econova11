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

    // Seed Data
    db.get("SELECT COUNT(*) as count FROM posts", (err, row) => {
        if (row && row.count === 0) {
            db.run(`INSERT OR IGNORE INTO users (name, email, password, role) VALUES ('Admin Owner', 'owner@econova.com', 'owner123', 'owner')`);
            
            db.run(`INSERT INTO posts (author_name, location, time_ago, type, title, content, likes_count, comments_count) VALUES 
                ('Elena Rodriguez', 'MADRID, ES', '2 HOURS AGO', 'REFORESTATION', 'Planted 50 native oaks in the Retiro buffer zone today.', 'The soil was perfect after the morning rain. Special thanks to the local nursery for the saplings!', 142, 18)`);
            db.run(`INSERT INTO posts (author_name, type, title, article_link, likes_count, comments_count) VALUES 
                ('Solar Guild', 'ARTICLE', 'How to audit your community''s energy waste in 3 steps.', '#', 50, 5)`);
            
            db.run(`INSERT INTO campaigns (title, description, target_amount, raised_amount, days_left, badge) VALUES 
                ('Save the Mangroves: Phase 2', 'Targeting 5,000 new seedlings along the coast of Queensland by the end of Q4.', 15000, 12450, 12, 'URGENT')`);
            db.run(`INSERT INTO campaigns (title, description, engagement_count, badge) VALUES 
                ('Neighborhood Composting', 'Join 400+ households in Brooklyn reducing landfill waste.', 412, '')`);
            
            db.run(`INSERT INTO impact_stats (co2_offset, trees_planted, water_liters) VALUES ('24.8k', '142,000', '2.1M')`);
            
            db.run(`INSERT INTO stewards (name, points, avatar_id) VALUES ('Marcus Chen', 1240, 11), ('Sarah Jenkins', 980, 5), ('David Thorne', 850, 8)`);
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

app.listen(3000, () => console.log('Server running on port 3000'));
