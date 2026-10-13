import fs from 'node:fs';
import path from 'node:path';
const dictionary=new Map(fs.readFileSync('tools/frontend-id.tsv','utf8').split(/\r?\n/).filter(Boolean).map(line=>line.split('|||')));
const translate=s=>dictionary.get(s.replaceAll('&amp;','&'))||s;
const walk=dir=>fs.readdirSync(dir,{withFileTypes:true}).flatMap(e=>e.isDirectory()?walk(path.join(dir,e.name)):[path.join(dir,e.name)]);
for(const file of ['resources/views/frontend','resources/views/layouts','resources/views/components/frontend'].flatMap(walk)){
 let text=fs.readFileSync(file,'utf8');
 text=text.replace(/>([^<>]+)</g,(_,content)=>{const trimmed=content.trim();if(!trimmed||trimmed.includes('{{')||trimmed.includes('@'))return '>'+content+'<';return '>'+content.replace(trimmed,translate(trimmed))+'<';});
 text=text.replace(/(alt|aria-label|placeholder|current|eyebrow)="([^"]+)"/g,(_,attr,value)=>{let target=translate(value);if(target===value&&value.startsWith('Explore '))target='Jelajahi '+translate(value.slice(8));if(target===value&&value.startsWith('View '))target='Lihat '+value.slice(5).replace('senior associate profile','profil asosiat senior').replace('leadership profile','profil pimpinan').replace('partner profile','profil mitra').replace('associate profile','profil asosiat');return attr+'="'+target.replaceAll('"','&quot;')+'"';});
 text=text.replace(/data-search="([^"]+)"/g,(_,value)=>{for(const [from,to] of [...dictionary].sort((a,b)=>b[0].length-a[0].length))if(from.length>5)value=value.replaceAll(from,to);return 'data-search="'+value+'"';});
 text=text.replace(/@section\('(title|description)'\)\s*([\s\S]*?)\s*@endsection/g,(_,key,value)=>"@section('"+key+"')\n"+translate(value.trim())+'\n@endsection');
 text=text.replace('lang="en"','lang="id"').replace("'HSNCO website'","'Situs web HSNCO'");fs.writeFileSync(file,text);
}
const file='public/assets/js/content-data.js';const data=JSON.parse(fs.readFileSync(file,'utf8').replace(/^window\.HSNCO_CONTENT\s*=\s*/,'').replace(/;\s*$/,''));
const recursive=(value,key='')=>typeof value==='string'?(['id','image'].includes(key)?value:translate(value)):Array.isArray(value)?value.map(v=>recursive(v)):Object.fromEntries(Object.entries(value).map(([k,v])=>[k,recursive(v,k)]));
fs.writeFileSync(file,'window.HSNCO_CONTENT = '+JSON.stringify(recursive(data))+';\n');
for(const file of ['public/assets/js/components.js','public/assets/js/main.js','public/assets/plugins-init/jstree-init.js']){let text=fs.readFileSync(file,'utf8');for(const [from,to] of [...dictionary].sort((a,b)=>b[0].length-a[0].length)){const quote="'"+from+"'";if(text.includes(quote))text=text.replaceAll(quote,"'"+to.replaceAll("'","\\'")+"'");}text=text.replace("`${visible} profile preview${visible === 1 ? '' : 's'}`","`${visible} profil contoh`");fs.writeFileSync(file,text);}
console.log('Translated frontend static copy, metadata, accessibility and dynamic content.');