import net from 'node:net';
import {spawn} from 'node:child_process';
let received=0;const sockets=new Set();
const server=net.createServer(socket=>{
 sockets.add(socket);socket.on('close',()=>sockets.delete(socket));socket.setTimeout(10000,()=>socket.destroy());socket.write('220 localhost HSNCO test SMTP\r\n');let buffer='',data=false;
 socket.on('data',chunk=>{buffer+=chunk.toString();let end;while((end=buffer.indexOf('\r\n'))>=0){const line=buffer.slice(0,end);buffer=buffer.slice(end+2);if(data){if(line==='.'){data=false;received++;socket.write('250 accepted locally\r\n');}continue;}if(/^EHLO|^HELO/i.test(line))socket.write('250-localhost\r\n250 PIPELINING\r\n');else if(/^DATA/i.test(line)){data=true;socket.write('354 End with dot\r\n');}else if(/^QUIT/i.test(line)){socket.end('221 bye\r\n');}else socket.write('250 OK\r\n');}});
});
await new Promise(resolve=>server.listen(0,'127.0.0.1',resolve));const port=server.address().port;
const env={...process.env,MAIL_MAILER:'smtp',MAIL_SCHEME:'smtp',MAIL_URL:'null',MAIL_HOST:'127.0.0.1',MAIL_PORT:String(port),MAIL_USERNAME:'',MAIL_PASSWORD:'',MAIL_FROM_ADDRESS:'no-reply@example.test',MAIL_FROM_NAME:'HSNCO SMTP test'};
const child=spawn('C:/laragon/bin/php/php-8.2.24-nts-Win32-vs16-x64/php.exe',['artisan','hsnco:mail-test','arvinz24wicaksono@gmail.com'],{env,windowsHide:true});let output='';child.stdout.on('data',data=>output+=data);child.stderr.on('data',data=>output+=data);const timeout=setTimeout(()=>child.kill(),20000);const exitCode=await new Promise(resolve=>child.on('exit',resolve));clearTimeout(timeout);sockets.forEach(socket=>socket.destroy());await new Promise(resolve=>server.close(resolve));
if(exitCode!==0||received!==1)throw Error('Local SMTP test failed: '+output);
console.log('PASS: Laravel SMTP transport delivered one message to loopback test server. No external email was sent.');