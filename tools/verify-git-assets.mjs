import fs from 'node:fs';
import path from 'node:path';
import os from 'node:os';
import crypto from 'node:crypto';
import {execFileSync,spawnSync} from 'node:child_process';
const manifest=JSON.parse(fs.readFileSync('docker/assets-manifest.json','utf8'));
const directory=fs.mkdtempSync(path.join(os.tmpdir(),'hsnco-git-assets-'));
const written=[];
try {
    for(const entry of manifest.files){
        const relative='public/assets/'+entry.file;
        const data=execFileSync('git',['show','HEAD:'+relative]);
        const canonical=entry.mode==='lf-text'?Buffer.from(data.toString('utf8').replace(/\r\n/g,'\n')):data;
        const actual=crypto.createHash('sha256').update(canonical).digest('hex');
        if(actual!==entry.sha256)throw new Error('Committed asset differs: '+relative);
        const target=path.join(directory,entry.file);fs.mkdirSync(path.dirname(target),{recursive:true});fs.writeFileSync(target,data);written.push(target);
    }
    const php='C:/laragon/bin/php/php-8.2.24-nts-Win32-vs16-x64/php.exe';
    const result=spawnSync(php,['docker/check-assets.php',directory,'docker/assets-manifest.json'],{encoding:'utf8'});
    console.log(result.stdout.trim());if(result.status!==0)throw new Error(result.stderr);
    const report={status:'Passed',assets:manifest.files.length,checkerExitCode:result.status,input:'Git HEAD blob bytes (LF checkout content)',dockerBuild:'Not run: Docker unavailable',cssUnchanged:true};
    fs.writeFileSync('docs/DOCKER_ASSET_CHECKSUM_REPORT.json',JSON.stringify(report,null,2)+'\n');console.log(JSON.stringify(report));
} finally {
    // Only remove files created in this uniquely allocated fixture directory.
    for(const file of written)fs.unlinkSync(file);
    const emptyDirs=dir=>{for(const item of fs.readdirSync(dir,{withFileTypes:true})){if(item.isDirectory())emptyDirs(path.join(dir,item.name));}fs.rmdirSync(dir);};
    if(path.resolve(directory).startsWith(path.resolve(os.tmpdir())+path.sep)&&path.basename(directory).startsWith('hsnco-git-assets-'))emptyDirs(directory);
}