const sqlite3 = require('sqlite3').verbose();
const db = new sqlite3.Database('econova.db');

db.serialize(() => {
    db.run(`DROP TABLE IF EXISTS campaigns`);
    db.run(`CREATE TABLE campaigns (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT, location TEXT, status TEXT, date TEXT,
        category TEXT, stewards_count INTEGER, progress_percent INTEGER,
        description TEXT, image_url TEXT
    )`);

    db.get("SELECT COUNT(*) as count FROM campaigns", (err, row) => {
        if (row && row.count === 0) {
            db.run(`INSERT INTO campaigns (title, location, status, date, category, stewards_count, progress_percent, image_url) VALUES 
                ('Nile River Plastic Cleanup', 'Cairo, Egypt (Maadi Corniche)', 'ACTIVE', 'Nov 15, 2024', 'Coastal', 87, 64, 'https://images.unsplash.com/photo-1621451537084-482c73073e0f?auto=format&fit=crop&q=80&w=600')`);
            db.run(`INSERT INTO campaigns (title, location, status, date, category, stewards_count, progress_percent, image_url) VALUES 
                ('Wadi Degla Protectorate Restoration', 'Cairo, Egypt (Maadi)', 'ACTIVE', 'Oct 28, 2024', 'Forest', 112, 73, 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&q=80&w=600')`);
            db.run(`INSERT INTO campaigns (title, location, status, date, category, stewards_count, progress_percent, image_url) VALUES 
                ('Alexandria Beach Cleanup', 'Alexandria, Egypt (Montazah Beach)', 'NEW', 'Dec 01, 2024', 'Coastal', 45, 28, 'https://images.unsplash.com/photo-1618477461853-cf6ed80fbfc9?auto=format&fit=crop&q=80&w=600')`);
            db.run(`INSERT INTO campaigns (title, location, status, date, category, stewards_count, progress_percent, image_url) VALUES 
                ('Urban Green Roof Initiative', 'Giza, Egypt (Zamalek)', 'NEW', 'Jan 10, 2025', 'Urban', 32, 18, 'https://images.unsplash.com/photo-1518005020951-eccb494ad742?auto=format&fit=crop&q=80&w=600')`);
            console.log("Database seeded successfully!");
        }
    });
});
