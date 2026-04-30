const fs = require('fs');
const path = require('path');

function walkDir(dir, callback) {
    fs.readdirSync(dir).forEach(f => {
        let dirPath = path.join(dir, f);
        let isDirectory = fs.statSync(dirPath).isDirectory();
        isDirectory ? walkDir(dirPath, callback) : callback(path.join(dir, f));
    });
}

walkDir('/home/bimasakti/dev/laravel/kebunku/resources/js', function(filePath) {
    if (filePath.endsWith('.vue')) {
        let content = fs.readFileSync(filePath, 'utf8');
        
        // Clean up chained dark classes
        let newContent = content.replace(/(dark:text-[^\s"']+)(?:\s+dark:text-[^\s"']+)+/g, '$1');
        newContent = newContent.replace(/(dark:bg-[^\s"']+)(?:\s+dark:bg-[^\s"']+)+/g, '$1');
        newContent = newContent.replace(/(dark:hover:bg-[^\s"']+)(?:\s+dark:hover:bg-[^\s"']+)+/g, '$1');
        newContent = newContent.replace(/(dark:border-[^\s"']+)(?:\s+dark:border-[^\s"']+)+/g, '$1');

        if (content !== newContent) {
            fs.writeFileSync(filePath, newContent, 'utf8');
            console.log('Cleaned:', filePath);
        }
    }
});
