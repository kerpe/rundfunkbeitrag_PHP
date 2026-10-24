// Regenerates routes/questions/decisions/variants/sets/situations in rules.json from the compiled bundle.
// Usage: node tools/rules/generate-rules.js public_html/assets/entry-<hash>.js private/app/rules.json private/app/rules.json
const fs=require('fs');
const [bundlePath,rulesPath,outPath]=process.argv.slice(2);
const s=fs.readFileSync(bundlePath,'utf8');
const a=s.indexOf('var W=(e,t)=>({en:e,de:t}),RbCo=');
const pcStart=s.indexOf('function Pc(e,t){',a);
const b=s.indexOf('function Fc(',pcStart);
let code=s.slice(a,b);
const el=s.indexOf('var RbElig=');const sitEnd=s.indexOf(',nl={situation',el);
code+='\n'+s.slice(el,sitEnd)+';\n';
const RbDob=n=>{let d=new Date().toISOString().slice(0,10);return String(+d.slice(0,4)-n)+d.slice(4)};
const f=new Function('RbDob',code+';return {Tc,Dc,Oc,jc,Pc,RbSit};');
const {Tc,Dc,Oc,jc,Pc,RbSit}=f(RbDob);
const rules=JSON.parse(fs.readFileSync(rulesPath,'utf8'));
const routes=Object.keys(Tc);
rules.routes=Object.fromEntries(routes.map(r=>[r,Tc[r]]));
rules.questions=Object.fromEntries(routes.map(r=>[r,Dc[r]]));
rules.decisions={};
for(const r of routes){const qs=Dc[r];let combos=[[]];for(const q of qs){const n=[];for(const c of combos)for(const v of ['',...q.options.map(o=>o[0])])n.push([...c,v]);combos=n}
 rules.decisions[r]={};for(const c of combos){const ans={};qs.forEach((q,i)=>{if(c[i])ans[q.key]=c[i]});const d=Oc(r,ans);const o={status:d.status,text:d.text};if(d.target!==undefined)o.target=d.target;o.source=d.source;rules.decisions[r][c.join('|')]=o}}
const sets=[],idx=new Map();rules.variants={};
const postTos=['','home','new','different','help','main'];
// Death case with a remaining resident: extra key segment "<remainingWho>,<remainingRegistered>".
const rxs=(r,rem)=>r==='death'&&rem==='yes'?['','self','other'].flatMap(w=>['','yes','no'].map(g=>w+','+g)):[''];
for(const r of routes)for(const fw of ['self','other'])for(const rem of ['','yes','no','unsure'])for(const rx of rxs(r,rem))for(let m=0;m<4;m++)for(const pt of postTos)for(const pc of ['','DE','other','intl']){
 const d=fw==='other'?{forWhom:fw}:{};if(rem)d.remaining=rem;if(rx){const [w,g]=rx.split(',');if(w)d.remainingWho=w;if(g)d.remainingRegistered=g}const ch=[];if(m&1)ch.push('name');if(m&2)ch.push('address');if(ch.length)d.changes=ch;if(pt)d.postTo=pt;if(pc)d.postCountry=pc==='intl'?'AT':pc;
 const set={fields:jc(r,d),documents:Pc(r,d)};const j=JSON.stringify(set);if(!idx.has(j)){idx.set(j,sets.length);sets.push(set)}
 rules.variants[[r,fw,rem,m,pt,pc,rx].join('|')]=idx.get(j)}
rules.sets=sets;
rules.situations=RbSit;
fs.writeFileSync(outPath,JSON.stringify(rules));
console.log('routes',routes.length,'sets',sets.length,'variants',Object.keys(rules.variants).length);
