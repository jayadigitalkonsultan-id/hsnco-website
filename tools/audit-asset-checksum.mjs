import fs from 'node:fs';
import crypto from 'node:crypto';
import {execFileSync} from 'node:child_process';
const hash=b=>crypto.createHash('sha256').update(b).digest('hex');
const file='public/assets/css/components.css',working=fs.readFileSync(file),committed=execFileSync('git',['show','HEAD:'+file]);
const current=JSON.parse(fs.readFileSync('docker/assets-manifest.json')).files.find(e=>e.file==='css/components.css');
const head=JSON.parse(execFileSync('git',['show','HEAD:docker/assets-manifest.json']).toString('utf8')).files.find(e=>e.file==='css/components.css');
console.log(JSON.stringify({file,workingBytes:working.length,gitBytes:committed.length,workingHash:hash(working),gitHash:hash(committed),workingLFHash:hash(Buffer.from(working.toString('utf8').replace(/\r\n/g,'\n'))),currentManifest:current.sha256,committedManifest:head.sha256,gitMatchesWorkingAfterLFNormalization:committed.equals(Buffer.from(working.toString('utf8').replace(/\r\n/g,'\n')))},null,2));