const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

/**
 * Econova Database Fix Tool
 * 1. Checks if sqlite3 is working.
 * 2. If not, replaces it with better-sqlite3.
 * 3. Injects a compatibility shim into server.js to keep existing code working.
 */

function checkSqlite3() {
    try {
        console.log('Checking if sqlite3 is functional...');
        const sqlite3 = require('sqlite3').verbose();
        const db = new sqlite3.Database(':memory:');
        db.close();
        return true;
    } catch (err) {
        return false;
    }
}

function run() {
    const isWorking = checkSqlite3();

    if (isWorking) {
        console.log('Result: sqlite3 is already installed and working correctly.');
        console.log('No changes needed.');
        return;
    }

    console.log('Result: sqlite3 is broken or missing. Initiating migration to better-sqlite3...');

    try {
        console.log('Step 1: Uninstalling broken sqlite3 package...');
        execSync('npm uninstall sqlite3', { stdio: 'inherit' });

        console.log('Step 2: Installing better-sqlite3...');
        execSync('npm install better-sqlite3', { stdio: 'inherit' });

        console.log('Step 3: Updating server.js with better-sqlite3 shim...');
        const serverPath = path.join(__dirname, 'server.js');
        if (!fs.existsSync(serverPath)) {
            throw new Error('server.js not found in current directory.');
        }

        let content = fs.readFileSync(serverPath, 'utf8');

        // Replace require statement
        content = content.replace(
            /const sqlite3 = require\(['"]sqlite3['"]\)\.verbose\(\);/g,
            "const sqlite3 = require('better-sqlite3');"
        );

        // Replace initialization with a compatibility shim
        const shim = `const db_raw = new sqlite3('econova.db');
// Compatibility shim to keep existing sqlite3 code functional
const db = {
    serialize: (fn) => fn(),
    run: (sql, params, cb) => {
        if (typeof params === 'function') { cb = params; params = []; }
        try {
            const info = db_raw.prepare(sql).run(params || []);
            if (cb) cb.call({ lastID: info.lastInsertRowid, changes: info.changes }, null);
        } catch (e) { if (cb) cb(e); }
    },
    get: (sql, params, cb) => {
        if (typeof params === 'function') { cb = params; params = []; }
        try {
            const row = db_raw.prepare(sql).get(params || []);
            if (cb) cb(null, row);
        } catch (e) { if (cb) cb(e); }
    },
    all: (sql, params, cb) => {
        if (typeof params === 'function') { cb = params; params = []; }
        try {
            const rows = db_raw.prepare(sql).all(params || []);
            if (cb) cb(null, rows);
        } catch (e) { if (cb) cb(e); }
    },
    close: () => db_raw.close()
};`;

        content = content.replace(
            /const db = new sqlite3\.Database\(['"]econova\.db['"].*?\);/s,
            shim
        );

        fs.writeFileSync(serverPath, content);

        console.log('Migration successful!');
        console.log('Server is now using better-sqlite3 via a compatibility layer.');
        console.log('You can now run your server with: node server.js');

    } catch (error) {
        console.error('Critical Error during fix:', error.message);
        process.exit(1);
    }
}

run();
