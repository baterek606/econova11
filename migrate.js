const sqlite3 = require('sqlite3').verbose();
const path = require('path');
const dbPath = path.join(__dirname, 'econova.db');
const db = new sqlite3.Database(dbPath);

db.serialize(() => {
    console.log('--- Starting Database Migration ---');

    // 1. Add missing columns to 'users' table
    const columns = [
        { name: 'trees_planted', type: 'INTEGER DEFAULT 0' },
        { name: 'plastic_removed', type: 'INTEGER DEFAULT 0' },
        { name: 'compostings', type: 'INTEGER DEFAULT 0' },
        { name: 'bio', type: 'TEXT DEFAULT ""' },
        { name: 'avatar', type: 'TEXT DEFAULT "https://i.pravatar.cc/150?u=default"' },
        { name: 'campaigns_joined', type: 'INTEGER DEFAULT 0' }
    ];

    columns.forEach(col => {
        db.run(`ALTER TABLE users ADD COLUMN ${col.name} ${col.type}`, (err) => {
            if (err) {
                if (err.message.includes('duplicate column name')) {
                    console.log(`[OK] Column "${col.name}" already exists.`);
                } else {
                    console.error(`[ERROR] Adding column "${col.name}":`, err.message);
                }
            } else {
                console.log(`[SUCCESS] Added column "${col.name}".`);
            }
        });
    });

    // 2. Ensure user_campaigns table exists
    db.run(`CREATE TABLE IF NOT EXISTS user_campaigns (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        campaign_id INTEGER,
        joined_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(user_id, campaign_id)
    )`, (err) => {
        if (err) {
            console.error('[ERROR] Creating user_campaigns table:', err.message);
        } else {
            console.log('[OK] user_campaigns table is ready.');
        }
    });

    console.log('--- Migration Process Finished ---');
});

db.close((err) => {
    if (err) {
        console.error('Error closing database:', err.message);
    } else {
        console.log('Database connection closed. You can now run node server.js');
    }
});
