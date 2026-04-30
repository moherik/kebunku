const fs = require('fs');
const path = require('path');

function walkDir(dir, callback) {
    fs.readdirSync(dir).forEach(f => {
        let dirPath = path.join(dir, f);
        let isDirectory = fs.statSync(dirPath).isDirectory();
        isDirectory ? walkDir(dirPath, callback) : callback(path.join(dir, f));
    });
}

const replacements = [
    { regex: /text-gray-900(?!\s+dark:text)/g, replacement: 'text-gray-900 dark:text-white' },
    { regex: /text-gray-800(?!\s+dark:text)/g, replacement: 'text-gray-800 dark:text-gray-200' },
    { regex: /text-gray-500(?!\s+dark:text)/g, replacement: 'text-gray-500 dark:text-gray-400' },
    { regex: /text-gray-400(?!\s+dark:text)/g, replacement: 'text-gray-400 dark:text-gray-500' },
    { regex: /bg-gray-50(?!\s+dark:bg)/g, replacement: 'bg-gray-50 dark:bg-white/5' },
    { regex: /hover:bg-gray-50(?!\s+dark:hover:bg)/g, replacement: 'hover:bg-gray-50 dark:hover:bg-white/5' },
    { regex: /bg-gray-100(?!\s+dark:bg)/g, replacement: 'bg-gray-100 dark:bg-white/10' },
    { regex: /hover:bg-gray-100(?!\s+dark:hover:bg)/g, replacement: 'hover:bg-gray-100 dark:hover:bg-white/10' },
    { regex: /border-gray-100(?!\s+dark:border)/g, replacement: 'border-gray-100 dark:border-white/5' },
    { regex: /border-gray-200(?!\s+dark:border)/g, replacement: 'border-gray-200 dark:border-white/10' },
    { regex: /bg-white(?!\s+dark:bg)(?!\/)/g, replacement: 'bg-white dark:bg-[#131b17]' }, // avoid matching bg-white/20
];

walkDir('/home/bimasakti/dev/laravel/kebunku/resources/js/Pages', function(filePath) {
    if (filePath.endsWith('.vue')) {
        let content = fs.readFileSync(filePath, 'utf8');
        let newContent = content;
        
        replacements.forEach(rule => {
            newContent = newContent.replace(rule.regex, rule.replacement);
        });

        if (content !== newContent) {
            fs.writeFileSync(filePath, newContent, 'utf8');
            console.log('Updated:', filePath);
        }
    }
});
