// Puts RbDate (rbdate.js) into the entry bundle and routes date/dob/month fields to it.
// Run once from the repo root: node tools/datefield/patch.js
const fs=require('fs'),crypto=require('crypto');
const man='public_html/.vite/manifest.json',m=JSON.parse(fs.readFileSync(man,'utf8'));
const entry=Object.values(m).find(e=>e.isEntry),old='public_html/'+entry.file;
let s=fs.readFileSync(old,'utf8');
if(s.includes('function RbDate('))throw new Error('already patched');
const fn=fs.readFileSync(__dirname+'/rbdate.js','utf8').replace(/^\/\/.*\n/gm,'').replace(/\n\s*/g,'');
const anchor='function RbRouteUrl(';
const field='g?(0,D.jsx)(__rbAddr,{field:e,inputProps:c,value:t,onChange:i,lang:a}):';
if(!s.includes(anchor)||s.split(field).length!==2)throw new Error('anchor not found');
s=s.replace(anchor,fn+anchor).replace(field,field+'[`date`,`dob`,`month`].includes(e.type)?(0,D.jsx)(RbDate,{field:e,inputProps:c,value:t,onChange:i,lang:a}):');
const file='assets/entry-'+crypto.createHash('sha256').update(s).digest('hex').slice(0,8)+'.js';
fs.writeFileSync('public_html/'+file,s);fs.unlinkSync(old);
entry.file=file;fs.writeFileSync(man,JSON.stringify(m,null,2)+'\n');
console.log(file);
