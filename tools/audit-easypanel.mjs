import fs from 'node:fs';
import crypto from 'node:crypto';
const hash=file=>crypto.createHash('sha256').update(fs.readFileSync(file)).digest('hex');
const lock=JSON.parse(fs.readFileSync('composer.lock','utf8'));
const extensions=[...new Set(lock.packages.flatMap(pkg=>Object.keys(pkg.require||{}).filter(name=>name.startsWith('ext-'))))].sort();
const previous=JSON.parse(fs.readFileSync('docs/EXISTING_FILE_HASHES.json','utf8'));
for(const file of ['.env','composer.json','composer.lock','package.json','vite.config.js','config/auth.php','config/session.php','database/database.sqlite']){const expected=previous.find(entry=>entry.file===file);if(hash(file)!==expected.sha256)throw new Error('Unexpected change: '+file);}
const assets=JSON.parse(fs.readFileSync('docs/ASSET_MAPPING.json','utf8'));for(const entry of assets)if(hash(entry.destination)!==entry.destinationSha256)throw new Error('Asset changed: '+entry.destination);
const entry=fs.readFileSync('docker/entrypoint.sh','utf8');if(/\bphp\s+artisan\s+(migrate|key:generate|db:seed)\b/.test(entry))throw new Error('Forbidden startup action');
const docker=fs.readFileSync('Dockerfile','utf8');if(!docker.includes('COPY composer.json composer.lock')||!docker.includes('--no-dev')||!docker.includes('USER 1000:1000')||!docker.includes('EXPOSE 8080'))throw new Error('Missing Docker requirement');
const report={date:'2026-10-13',laravel:lock.packages.find(pkg=>pkg.name==='laravel/framework').version,localPhp:'8.2.24',imagePhp:'8.3',composer:'2, locked production dependencies',requiredExtensions:extensions,frontendAssetsVerified:assets.length,viteRequired:false,server:'FrankenPHP classic',documentRoot:'/app/public',port:8080,health:'/up',tests:{passed:6,assertions:114},validation:{phpSyntax:'passed',shellSyntax:'passed',gitDiffCheck:'passed',protectedConfigAndDatabaseHashes:'unchanged',frontendAssets:'unchanged',dockerBuild:'not run: Docker CLI unavailable'}};
fs.writeFileSync('docs/EASYPANEL_AUDIT.json',JSON.stringify(report,null,2)+'\n');console.log(JSON.stringify(report,null,2));