const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

function checkSqlite3() {
    try {
        console.log('Checking sqlite3...');
        require('sqlite3');
        return true;
    } catch (err) {
        return false;
    }
}

function run() {
    const isWorking = checkSqlite3();

    if (isWorking) {
        console.log('sqlite3 is working. No changes needed.');
        return;
    }

    console.log('sqlite3 is broken. Fixing...');

    try {
        console.log('Uninstalling sqlite3...');
        execSync('npm uninstall sqlite3', { stdio: 'inherit' });

        console.log('Installing better-sqlite3...');
        execSync('npm install better-sqlite3', { stdio: 'inherit' });

        console.log('Updating server.js...');
        const serverPath = path.join(__dirname, 'server.js');
        let content = fs.readFileSync(serverPath, 'utf8');

        content = content.replace(
            /require\(['"]sqlite3['"]\)/g,
            "require('better-sqlite3')"
        );

        fs.writeFileSync(serverPath, content);

        console.log('Done! Run node server.js');

    } catch (error) {
        console.error('Error:', error.message);
    }
}

run();