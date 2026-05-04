
const express = require('express');
const sqlite3 = require('sqlite3').verbose();
const cors = require('cors');
const path = require('path');
const multer = require('multer');


const app = express();
app.use(express.json());
app.use(cors());

// Configure storage for uploads
const storage = multer.diskStorage({
    destination: (req, file, cb) => {
        cb(null, 'uploads/');
    },
    filename: (req, file, cb) => {
        cb(null, Date.now() + path.extname(file.originalname));
    }
});
const upload = multer({ storage: storage });

// Create uploads folder if not exists
const fs = require('fs');
if (!fs.existsSync('uploads')) {
    fs.mkdirSync('uploads');
}
app.use('/uploads', express.static(path.join(__dirname, 'uploads')));

// Serve static files (HTML, CSS, JS, images) from the current directory
app.use(express.static(__dirname));

// Initialize SQLite database
const db = new sqlite3.Database('econova.db', (err) => {
    if (err) console.error('Database opening error: ', err);
});

// Schema Setup
db.serialize(() => {
    db.run(`CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT, email TEXT UNIQUE, password TEXT, role TEXT,
        avatar TEXT DEFAULT 'https://i.pravatar.cc/150?u=default',
        bio TEXT DEFAULT '',
        join_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        trees_planted INTEGER DEFAULT 0,
        plastic_removed INTEGER DEFAULT 0,
        compostings INTEGER DEFAULT 0
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
        [author_name, location, time_ago, type, title, content, article_link], function (err) {
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
        [title, description, target_amount, raised_amount, days_left, badge], function (err) {
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
    db.run(`INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')`, [name, email, password], function (err) {
        if (err) return res.status(400).json({ error: "Email already exists" });
        const newUser = { id: this.lastID, name, email, role: 'user', avatar: `https://i.pravatar.cc/150?u=${email}`, join_date: new Date().toISOString() };
        res.json({ success: true, user: newUser });
    });
});

app.post('/api/auth/login', (req, res) => {
    const { email, password } = req.body;
    db.get("SELECT * FROM users WHERE email = ? AND password = ?", [email, password], (err, user) => {
        if (user) {
            res.json({ success: true, user: { 
                id: user.id, 
                name: user.name, 
                email: user.email, 
                role: user.role, 
                avatar: user.avatar, 
                bio: user.bio || '',
                join_date: user.join_date 
            } });
        } else {
            res.status(401).json({ error: "Invalid credentials" });
        }
    });
});

app.post('/api/auth/update', (req, res) => {
    const { id, name, password, avatar } = req.body;
    let query = "UPDATE users SET name = ?, avatar = ?";
    let params = [name, avatar];

    if (password) {
        query += ", password = ?";
        params.push(password);
    }

    query += " WHERE id = ?";
    params.push(id);

    db.run(query, params, function (err) {
        if (err) return res.status(500).json({ error: err.message });
        db.get("SELECT * FROM users WHERE id = ?", [id], (err, user) => {
            res.json({ success: true, user: { id: user.id, name: user.name, email: user.email, role: user.role, avatar: user.avatar, join_date: user.join_date } });
        });
    });
});

app.put('/api/user/profile', (req, res) => {
    const { userId, name, email, password, avatar, bio, trees_planted, plastic_removed, compostings } = req.body;
    
    let query = "UPDATE users SET name = ?, email = ?, bio = ?, trees_planted = ?, plastic_removed = ?, compostings = ?";
    let params = [name, email, bio || '', trees_planted || 0, plastic_removed || 0, compostings || 0];
    
    if (avatar) {
        query += ", avatar = ?";
        params.push(avatar);
    }

    if (password) {
        query += ", password = ?";
        params.push(password);
    }
    
    query += " WHERE id = ?";
    params.push(userId);

    db.run(query, params, function (err) {
        if (err) return res.status(500).json({ error: err.message });
        db.get("SELECT * FROM users WHERE id = ?", [userId], (err, user) => {
            if (!user) return res.status(404).json({ error: "User not found" });
            res.json({ success: true, user: { 
                id: user.id, 
                name: user.name, 
                email: user.email, 
                role: user.role, 
                avatar: user.avatar, 
                bio: user.bio || '',
                join_date: user.join_date,
                trees_planted: user.trees_planted, 
                plastic_removed: user.plastic_removed, 
                compostings: user.compostings
            } });
        });
    });
});

app.get('/api/user/:id/posts', (req, res) => {
    db.all("SELECT * FROM explore_posts WHERE user_name = (SELECT name FROM users WHERE id = ?) ORDER BY created_at DESC", [req.params.id], (err, rows) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(rows);
    });
});

app.get('/api/user/:id/stats', (req, res) => {
    const userId = req.params.id;
    
    db.get("SELECT trees_planted, plastic_removed, compostings FROM users WHERE id = ?", [userId], (err, user) => {
        if (err) return res.status(500).json({ error: err.message });
        
        db.get("SELECT COUNT(*) as campaigns FROM user_campaigns WHERE user_id = ?", [userId], (err2, campaignRow) => {
            if (err2) return res.status(500).json({ error: err2.message });
            
            res.json({
                trees: user ? user.trees_planted : 0,
                plastic: user ? user.plastic_removed : 0,
                compost: user ? user.compostings : 0,
                campaigns: campaignRow ? campaignRow.campaigns : 0
            });
        });
    });
});

app.get('/api/user/:id/campaigns/count', (req, res) => {
    db.get("SELECT COUNT(*) as count FROM user_campaigns WHERE user_id = ?", [req.params.id], (err, row) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ count: row.count || 0 });
    });
});

app.post('/api/auth/upload', upload.single('avatar'), (req, res) => {
    if (!req.file) return res.status(400).json({ error: 'No file uploaded' });
    const avatarPath = `/uploads/${req.file.filename}`;
    const userId = req.body.userId;

    db.run("UPDATE users SET avatar = ? WHERE id = ?", [avatarPath, userId], function (err) {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ success: true, avatar: avatarPath });
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

app.post('/api/explore/posts', upload.fields([{ name: 'before_image' }, { name: 'after_image' }]), (req, res) => {
    const { userId, user_name, user_location, title, description, category } = req.body;
    const before_image = req.files['before_image'] ? `/uploads/${req.files['before_image'][0].filename}` : '';
    const after_image = req.files['after_image'] ? `/uploads/${req.files['after_image'][0].filename}` : '';

    db.run(`INSERT INTO explore_posts (user_name, user_location, title, description, category, before_image, after_image) VALUES (?, ?, ?, ?, ?, ?, ?)`,
        [user_name, user_location, title, description, category, before_image, after_image], function (err) {
            if (err) return res.status(500).json({ error: err.message });
            
            // AUTOMATIC IMPACT CALCULATION
            let impactQuery = "";
            if (category === 'forest') impactQuery = "UPDATE users SET trees_planted = trees_planted + 10 WHERE id = ?";
            else if (category === 'beach') impactQuery = "UPDATE users SET plastic_removed = plastic_removed + 5 WHERE id = ?";
            else if (category === 'urban') impactQuery = "UPDATE users SET compostings = compostings + 3 WHERE id = ?";
            else if (category === 'river') impactQuery = "UPDATE users SET plastic_removed = plastic_removed + 7 WHERE id = ?";

            if (impactQuery && userId) {
                db.run(impactQuery, [userId], (err2) => {
                    if (err2) console.error('Impact update error:', err2);
                });
            }

            res.json({ success: true, id: this.lastID });
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

// ====== NEW CAMPAIGN DETAILS ROUTES ======
db.serialize(() => {
    const columnsToAdd = [
        "location TEXT",
        "goal INTEGER",
        "trees_planted INTEGER",
        "volunteers_count INTEGER",
        "progress_percent INTEGER",
        "latitude REAL",
        "longitude REAL",
        "upcoming_event TEXT"
    ];
    columnsToAdd.forEach(col => {
        db.run(`ALTER TABLE campaigns ADD COLUMN ${col}`, (err) => { });
    });

    db.run(`CREATE TABLE IF NOT EXISTS user_campaigns (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        campaign_id INTEGER,
        UNIQUE(user_id, campaign_id)
    )`);

    // Seed test campaigns if none exist
    db.get("SELECT COUNT(*) as count FROM campaigns", (err, row) => {
        if (row && row.count === 0) {
            // Seed Amazon
            db.run(`INSERT INTO campaigns (title, description, target_amount, raised_amount, days_left, badge, location, goal, trees_planted, volunteers_count, progress_percent, latitude, longitude, upcoming_event) 
            VALUES ('Amazon Rainforest Restoration', 'Join us in restoring the lungs of the Earth.', 50000, 20000, 15, 'Urgent', 'Manaus, Brazil', 50000, 1250, 48, 40, -3.1190, -60.0217, 'Planting weekend - Oct 15th')`);

            // Seed Atlantic Forest
            db.run(`INSERT INTO campaigns (title, description, target_amount, raised_amount, days_left, badge, location, goal, trees_planted, volunteers_count, progress_percent, latitude, longitude, upcoming_event) 
            VALUES ('Atlantic Forest Restoration', 'Restoring the Atlantic Forest', 30000, 15000, 10, 'Active', 'Rio de Janeiro, Brazil', 30000, 500, 20, 50, -22.9068, -43.1729, 'Seedling Prep - Nov 10th')`);
        }
    });
});

app.get('/api/campaigns/:id', (req, res) => {
    db.get("SELECT * FROM campaigns WHERE id = ?", [req.params.id], (err, row) => {
        if (err) return res.status(500).json({ error: err.message });
        if (!row) return res.status(404).json({ error: "Campaign not found" });
        res.json(row);
    });
});

app.post('/api/campaigns/:id/join', (req, res) => {
    const userId = req.body.user_id;
    const campaignId = req.params.id;

    if (!userId) return res.status(400).json({ error: "User ID required" });

    db.run("INSERT OR IGNORE INTO user_campaigns (user_id, campaign_id) VALUES (?, ?)", [userId, campaignId], function (err) {
        if (err) return res.status(500).json({ error: err.message });

        if (this.changes > 0) {
            db.get("SELECT badge, title FROM campaigns WHERE id = ?", [campaignId], (err3, campaign) => {
                // Update volunteers count
                db.run("UPDATE campaigns SET volunteers_count = COALESCE(volunteers_count, 0) + 1 WHERE id = ?", [campaignId]);
                
                // AUTOMATIC IMPACT CALCULATION FOR JOINING
                let impactQuery = "";
                if (campaign.title.toLowerCase().includes('tree') || campaign.title.toLowerCase().includes('forest')) {
                    impactQuery = "UPDATE users SET trees_planted = trees_planted + 5 WHERE id = ?";
                } else if (campaign.title.toLowerCase().includes('plastic') || campaign.title.toLowerCase().includes('cleanup')) {
                    impactQuery = "UPDATE users SET plastic_removed = plastic_removed + 3 WHERE id = ?";
                }

                if (impactQuery) {
                    db.run(impactQuery, [userId]);
                }
                
                res.json({ success: true, message: "Joined successfully" });
            });
        } else {
            res.json({ success: false, message: "Already joined" });
        }
    });
});
// ==========================================

app.listen(3000, () => console.log('Server running on port 3000'));
