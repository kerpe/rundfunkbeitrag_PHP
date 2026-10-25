#!/usr/bin/env python3
"""Admin order list redesign + Lithuanian admin.

On /admin the bundle renders its English texts and a small DOM translator swaps every
text that has an entry in tools/admin/lt.json for its Lithuanian version.
The case queue becomes a full-width table with filters and search; a case opens on its own.

Patches the built bundle in place: python3 tools/admin/cases-patch.py public_html/assets/entry-XXXX.js
"""
import json, os, sys

path = sys.argv[1]
s = open(path, encoding='utf-8').read()
lt = json.load(open(os.path.join(os.path.dirname(__file__), 'lt.json'), encoding='utf-8'))

COMPONENTS = r'''
var RbAdmIs=()=>typeof location<`u`&&/^\/(admin|staff)(\/|$)/.test(location.pathname),RbAdmDict=__DICT__,RbL=x=>RbAdmDict[x]??x;
(function(){if(typeof document>`u`||!RbAdmIs())return;document.documentElement.classList.add(`rb-adm-page`);let pre=Object.keys(RbAdmDict).filter(x=>x.endsWith(`: `)),fix=t=>{let v=t.nodeValue,k=v.trim();if(!k)return;let r=RbAdmDict[k];if(r===void 0){let x=pre.find(x=>k.startsWith(x)&&k.length>x.length);if(!x)return;let rest=k.slice(x.length);r=RbAdmDict[x]+(RbAdmDict[rest]??rest)}let p=t.parentElement;if(p&&p.closest(`textarea,pre,[data-rb-raw]`))return;t.nodeValue=v.replace(k,r)},attrs=n=>{for(let a of[`placeholder`,`aria-label`,`title`]){let v=n.getAttribute(a);v&&RbAdmDict[v.trim()]&&n.setAttribute(a,RbAdmDict[v.trim()])}},walk=n=>{if(n.nodeType===3)return fix(n);if(n.nodeType!==1)return;attrs(n);n.querySelectorAll(`[placeholder],[aria-label],[title]`).forEach(attrs);let w=document.createTreeWalker(n,4),x;for(;x=w.nextNode();)fix(x)},go=()=>{walk(document.body);new MutationObserver(m=>{for(let r of m)r.type===`characterData`?fix(r.target):r.addedNodes.forEach(walk)}).observe(document.body,{subtree:!0,childList:!0,characterData:!0})};document.body?go():document.addEventListener(`DOMContentLoaded`,go)})();
var RbAdmState={draft:[`Juodraštis`,`grey`],ordered:[`Naujas`,`blue`],in_review:[`Tikrinamas`,`amber`],needs_information:[`Laukia kliento`,`purple`],submitting:[`Pateikiamas`,`amber`],submitted:[`Pateiktas`,`green`],closed:[`Baigtas`,`grey`],hold:[`Sustabdytas`,`red`],withdrawn:[`Klientas atsisakė`,`red`]},
RbAdmSt=e=>e.operations?.withdrawal?`withdrawn`:e.operations?.hold?`hold`:e.state,
RbAdmBadge=e=>{let[l,c]=RbAdmState[RbAdmSt(e)]||[e.state,`grey`];return(0,D.jsx)(`span`,{className:`rb-adm-badge rb-adm-`+c,children:l})},
RbAdmWho=e=>{let d=e.data||{},n=d.applicantFirst?[d.applicantFirst,d.applicantLast]:[d.firstName,d.lastName];return n.filter(Boolean).join(` `)||String(d.email||`—`)},
RbAdmStaff=a=>{if(!a)return`—`;let n=(window.__rbStaffNames||{})[a]||a;return n.replace(/ \(.*\)$/,``).replace(/@.*/,``)},
RbAdmAgo=v=>{let d=new Date(v),m=(Date.now()-d)/6e4;return isNaN(d)?``:m<2?`ką tik`:m<60?Math.round(m)+` min. prieš`:m<1440?Math.round(m/60)+` val. prieš`:m<10080?Math.round(m/1440)+` d. prieš`:RbAdmDate(v).slice(0,10)},
RbAdmFilters=[[`active`,`Aktyvūs`,e=>![`closed`,`withdrawn`].includes(RbAdmSt(e))],[`ordered`,`Nauji`,e=>RbAdmSt(e)===`ordered`],[`in_review`,`Tikrinami`,e=>RbAdmSt(e)===`in_review`],[`needs_information`,`Laukia kliento`,e=>RbAdmSt(e)===`needs_information`],[`unpaid`,`Neapmokėti`,e=>!e.checks?.paid&&![`closed`,`withdrawn`,`draft`].includes(RbAdmSt(e))],[`submitted`,`Pateikti`,e=>[`submitting`,`submitted`].includes(RbAdmSt(e))],[`closed`,`Baigti`,e=>[`closed`,`withdrawn`].includes(RbAdmSt(e))],[`all`,`Visi`,()=>!0]];
function RbAdmQueue({cases:r,open:k,refresh:oe}){let[q,setQ]=(0,_.useState)(``),[f,setF]=(0,_.useState)(`active`),[busy,setBusy]=(0,_.useState)(!1),flt=RbAdmFilters.find(x=>x[0]===f)[2],needle=q.trim().toLowerCase(),rows=r.filter(flt).filter(e=>!needle||[e.order?.reference,RbAdmWho(e),e.data?.email,e.data?.firstName,e.data?.lastName,RbL(Tc[e.route]?.title.en)].join(` `).toLowerCase().includes(needle));
return RbAdmF(`section`,{className:`rb-adm-queue`},
RbAdmF(`div`,{className:`rb-adm-toolbar`},RbAdmF(`div`,{className:`rb-adm-chips`,role:`tablist`,"aria-label":`Filtrai`},...RbAdmFilters.map(([id,label,fn])=>{let n=r.filter(fn).length;return RbAdmF(`button`,{key:id,type:`button`,role:`tab`,"aria-selected":f===id,className:`rb-adm-chip`+(f===id?` rb-adm-chip-on`:``),onClick:()=>setF(id)},label,RbAdmF(`span`,null,String(n)))})),
RbAdmF(`div`,{className:`rb-adm-search`},RbAdmF(`input`,{type:`search`,value:q,onChange:e=>setQ(e.target.value),placeholder:`Ieškoti: numeris, vardas, el. paštas`,"aria-label":`Ieškoti užsakymų`}),RbAdmF(`button`,{type:`button`,className:`rb-adm-refresh`,disabled:busy,onClick:async()=>{setBusy(!0);try{await oe()}finally{setBusy(!1)}}},busy?`Atnaujinama…`:`↻ Atnaujinti`))),
rows.length?RbAdmF(`div`,{className:`rb-adm-table-card`},RbAdmF(`table`,{className:`rb-adm-cases`},RbAdmF(`thead`,null,RbAdmF(`tr`,null,RbAdmF(`th`,null,`Užsakymas`),RbAdmF(`th`,null,`Paslauga`),RbAdmF(`th`,null,`Klientas`),RbAdmF(`th`,null,`Būsena`),RbAdmF(`th`,null,`Mokėjimas`),RbAdmF(`th`,null,`Atsakingas`),RbAdmF(`th`,null,`Atnaujinta`))),
RbAdmF(`tbody`,null,...rows.map(e=>RbAdmF(`tr`,{key:e.id,tabIndex:0,onClick:()=>k(e.id),onKeyDown:t=>{(t.key===`Enter`||t.key===` `)&&(t.preventDefault(),k(e.id))}},
RbAdmF(`td`,{"data-l":`Užsakymas`},RbAdmF(`strong`,{className:`rb-adm-ref`},(e.mode===`test`?`TEST · `:``)+(e.order?.reference||`—`)),RbAdmF(`small`,null,RbAdmDate(e.order?.at||e.createdAt))),
RbAdmF(`td`,{"data-l":`Paslauga`},RbL(Tc[e.route]?.title.en||e.route)),
RbAdmF(`td`,{"data-l":`Klientas`,"data-rb-raw":``},RbAdmF(`span`,null,RbAdmWho(e)),RbAdmF(`small`,null,String(e.data?.email||``))),
RbAdmF(`td`,{"data-l":`Būsena`},RbAdmBadge(e)),
RbAdmF(`td`,{"data-l":`Mokėjimas`},e.checks?.paid?RbAdmF(`span`,{className:`rb-adm-paid`},`✓ Apmokėta`):RbAdmF(`span`,{className:`rb-adm-unpaid`},`Laukiama`)),
RbAdmF(`td`,{"data-l":`Atsakingas`},RbAdmStaff(e.assignedTo)),
RbAdmF(`td`,{"data-l":`Atnaujinta`},RbAdmAgo(e.updatedAt))))))):
RbAdmF(`div`,{className:`rb-adm-empty mj-card`},r.length?`Pagal šį filtrą užsakymų nėra.`:`Užsakymų dar nėra. Kai klientas užsisakys, užsakymas atsiras čia.`))}
function RbAdmMeta({c}){let d=c.data||{},fee=((c.order?.feeCents??4900)/100).toFixed(2).replace(`.`,`,`)+` €`,it=(l,v,raw)=>RbAdmF(`div`,raw?{"data-rb-raw":``}:null,RbAdmF(`dt`,null,l),RbAdmF(`dd`,null,v));
return RbAdmF(`dl`,{className:`rb-adm-meta`},it(`Būsena`,RbAdmBadge(c)),it(`Klientas`,RbAdmWho(c),1),it(`El. paštas`,d.email?RbAdmF(`a`,{href:`mailto:`+d.email},String(d.email)):`—`,1),d.phone?it(`Telefonas`,String(d.phone),1):null,it(`Užsakyta`,RbAdmDate(c.order?.at||c.createdAt)),it(`Mokėjimas`,c.checks?.paid?RbAdmF(`span`,{className:`rb-adm-paid`},`✓ Apmokėta`):RbAdmF(`span`,{className:`rb-adm-unpaid`},`Laukiama `+fee)),it(`Atsakingas`,RbAdmStaff(c.assignedTo)))}
'''.replace('__DICT__', json.dumps(lt, ensure_ascii=False))

def rep(old, new, count=1):
    global s
    n = s.count(old)
    if n != count:
        sys.exit(f'anchor found {n}x, expected {count}: {old[:90]}')
    s = s.replace(old, new)

# components go right after the settings components from settings-patch.py (RbAdmF, RbAdmDate live there)
rep('function RbAdmSettings(', COMPONENTS.strip() + '\nfunction RbAdmSettings(')

# admin renders English internally; the DOM translator turns it into Lithuanian
rep('function el(){let e=O();return{lang:e,t:(t,n)=>e===`en`?t:n}}',
    'function el(){let e=O();return RbAdmIs()?{lang:`en`,t:t=>t}:{lang:e,t:(t,n)=>e===`en`?t:n}}')

# queue: full-width table while no case is open; a case opens on its own page with a back link
start = s.index('(0,D.jsxs)(`div`,{className:`mj-workspace`,children:[(0,D.jsxs)(`aside`,{className:`mj-queue`')
end = s.index('(0,D.jsx)(`div`,{className:`mj-case-detail`', start)
s = s[:start] + '(0,D.jsxs)(`div`,{className:`mj-workspace rb-adm-ws`,children:[!j&&(0,D.jsx)(RbAdmQueue,{cases:r,open:k,refresh:oe}),' + s[end:]
rep('(0,D.jsx)(`div`,{className:`mj-case-detail`,children:j?(0,D.jsxs)(D.Fragment,{children:[(0,D.jsxs)(`section`,{className:`mj-card`,children:[',
    '(0,D.jsx)(`div`,{className:`mj-case-detail`,children:j?(0,D.jsxs)(D.Fragment,{children:[(0,D.jsx)(`button`,{type:`button`,className:`rb-adm-back`,onClick:()=>{o(null),oe()},children:`← Visi užsakymai`}),(0,D.jsxs)(`section`,{className:`mj-card rb-adm-casehead`,children:[')
rep('children:n(`Refresh case`,`Vorgang aktualisieren`)})]}),', 'children:n(`Refresh case`,`Vorgang aktualisieren`)})]}),(0,D.jsx)(RbAdmMeta,{c:j}),')
start = s.index(':(0,D.jsxs)(`section`,{className:`mj-card`,children:[(0,D.jsx)(`h2`,{children:n(`Select a case to start`')
end_marker = 'tatsächlich eingereicht hat.`)})]})'
end = s.index(end_marker, start) + len(end_marker)
s = s[:start] + ':null' + s[end:]

# readable history (newest first) and dates
rep('S.map((e,t)=>(0,D.jsxs)(`li`,{children:[e.at,` · `,e.action', 'S.slice().reverse().map((e,t)=>(0,D.jsxs)(`li`,{children:[RbAdmDate(e.at),` · `,RbL(e.action)+(e.actor&&e.actor!==`customer`?` (`+RbAdmStaff(e.actor)+`)`:e.actor===`customer`?` (klientas)`:``)')
rep('j.order.at,', 'RbAdmDate(j.order.at),')
rep('j.order.permittedStartAt||', '(j.order.permittedStartAt&&RbAdmDate(j.order.permittedStartAt))||')

open(path, 'w', encoding='utf-8').write(s)
print('patched', path)
