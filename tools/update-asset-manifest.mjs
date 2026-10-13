import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import {fileURLToPath} from 'node:url';
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'..'),base=path.join(root,'public/assets');
const textExtensions=new Set(['.css','.js','.json','.map','.svg','.txt','.md']);
const walk=dir=>fs.readdirSync(dir,{withFileTypes:true}).flatMap(entry=>entry.isDirectory()?walk(path.join(dir,entry.name)):[path.join(dir,entry.name)]);
const files=walk(base).sort().map(file=>{
    const data=fs.readFileSync(file);
    const mode=textExtensions.has(path.extname(file).toLowerCase())||/^LICENSE(?:[.-].*)?$/.test(path.basename(file))?'lf-text':'binary';
    // Do not rewrite source files. Git text checkout changes CRLF to LF.
    const canonical=mode==='lf-text'?Buffer.from(data.toString('utf8').replace(/\r\n/g,'\n'),'utf8'):data;
    if(mode==='lf-text'&&!Buffer.from(data.toString('utf8'),'utf8').equals(data))throw new Error('Non-UTF8 text asset: '+file);
    return {file:path.relative(base,file).replaceAll('\\','/'),mode,sha256:crypto.createHash('sha256').update(canonical).digest('hex')};
});
fs.writeFileSync(path.join(root,'docker/assets-manifest.json'),JSON.stringify({version:2,files},null,2)+'\n');
console.log('Recorded '+files.length+' asset hashes: LF-canonical UTF-8 text, byte-exact binary.');