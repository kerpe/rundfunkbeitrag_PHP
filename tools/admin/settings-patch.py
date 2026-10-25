#!/usr/bin/env python3
"""Admin settings redesign (Lithuanian, grouped fields) + team link in the admin header.

Patches the built bundle in place: python3 tools/admin/settings-patch.py public_html/assets/entry-XXXX.js
"""
import sys

path = sys.argv[1]
s = open(path, encoding='utf-8').read()

COMPONENTS = r'''
var RbAdmF=(t,p,...c)=>(0,D.jsx)(t,{...p,children:c.length>1?c:c[0]}),RbAdmPlaceholder=v=>/^\s*(tba|todo|tbd|-+|\.+|…|xxx)\s*$/i.test(v||``),RbAdmGroups=[
{id:`company`,title:`Įmonės duomenys`,note:`Klientai mato šiuos duomenis rekvizitų puslapyje, sutartyje, sąskaitose ir laiškuose.`,fields:[
[`providerName`,`Įmonės pavadinimas`,`Tikslus pavadinimas, kaip Registrų centre.`,1],
[`providerAddress`,`Įmonės adresas`,`Gatvė, namas, pašto kodas, miestas, šalis. Galima keliomis eilutėmis.`,1,1],
[`taxIdentifier`,`Įmonės kodas ir PVM informacija`,`Rodoma rekvizituose ir sąskaitoje. Rašykite vokiškai, nes tai mato klientai.`,1],
[`supportEmail`,`Klientų aptarnavimo el. paštas`,`Rodomas klientams. Čia ateina klientų žinutės ir pranešimai apie naujus atsiliepimus.`,1]]},
{id:`bank`,title:`Banko sąskaita mokėjimams`,note:`Klientas mato šiuos duomenis mokėjimo kortelėje, patvirtinimo laiške ir mokėjimo QR kode.`,fields:[
[`beneficiary`,`Gavėjas (sąskaitos savininkas)`,`Įmonės pavadinimas, kaip jis įrašytas banke. Ne banko pavadinimas.`,1],
[`iban`,`IBAN`,`Sąskaitos numeris, į kurį klientai moka.`,1],
[`bic`,`BIC (SWIFT kodas)`,`Banko kodas, pvz. Swedbank Lietuvoje: HABALT22.`,1]]},
{id:`invoice`,title:`Sąskaitos ir mokesčiai`,note:`Naudojama sąskaitose, kurias išrašome klientams.`,fields:[
[`vatRate`,`PVM tarifas, %`,`Jei įmonė nėra PVM mokėtoja, įrašykite 0.`,1],
[`taxStatement`,`Mokesčių pastaba sąskaitoje`,`Sakinys sąskaitos apačioje, vokiškai. Pvz., kad įmonė neregistruota PVM mokėtoja.`,1,1],
[`invoicePrefix`,`Sąskaitų numerio pradžia`,`Tik didžiosios raidės, skaičiai ir brūkšnelis. Pvz. RF-2026 duos numerius RF-2026-1, RF-2026-2…`,1]]},
{id:`legal`,title:`Teisiniai tekstai`,note:`Ilgi tekstai vokiečių kalba. Įklijuokite paruoštus tekstus.`,fields:[
[`termsText`,`Paslaugų teikimo sąlygos`,`Rodoma puslapyje /agb ir pridedama prie kiekvieno užsakymo.`,1,1],
[`withdrawalText`,`Sutarties atsisakymo informacija ir forma`,`Rodoma puslapyje /widerruf.`,1,1],
[`privacyText`,`Privatumo politika`,`Rodoma puslapyje /datenschutz.`,1,1]]},
{id:`authority`,title:`Įgaliojimas`,note:`Kaip klientas mus įgalioja kreiptis į Beitragsservice.`,fields:[
[`authorityMethod`,`Įgaliojimo būdas`,`Vienas sakinys vokiškai. Įrašomas į kiekvieno kliento įgaliojimo tekstą.`,1,1],
[`authorityAcceptanceReference`,`Įrodymas, kad Beitragsservice priima tokį įgaliojimą`,`Vidinė pastaba, pvz. jų laiško data ir numeris.`,0,1]]},
{id:`retention`,title:`Duomenų saugojimas`,note:`Kiek laiko laikome baigtus užsakymus, kol juos galima ištrinti.`,fields:[
[`retentionDays`,`Kiek dienų saugoti baigtus užsakymus`,`Tik skaičius dienomis. Pvz. 1095 (3 metai).`,0],
[`retentionPolicyReference`,`Kur priimtas šis sprendimas`,`Vidinė pastaba tekstu, pvz. „Vidaus sprendimas, 2026-10-25“.`,0]]},
{id:`launch`,title:`Paleidimo patikrinimas`,note:``,fields:[
[`launchApprovalReference`,`Pastaba apie galutinį patikrinimą`,`Vidinė pastaba: kas ir kada patikrino, kad viskas paruošta.`,0,1]]}],
RbAdmInvalid={validEmail:[`supportEmail`,`Neteisingas el. pašto adresas.`],validIban:[`iban`,`IBAN neteisingas. Patikrinkite numerį.`],validVatRate:[`vatRate`,`Įrašykite skaičių nuo 0 iki 30.`],validInvoicePrefix:[`invoicePrefix`,`Tik didžiosios raidės, skaičiai ir brūkšnelis (2–16 simbolių).`],validRetentionDays:[`retentionDays`,`Įrašykite sveikąjį dienų skaičių.`]},
RbAdmChecks=[[`bic`,v=>/^[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?$/.test(v.toUpperCase()),`BIC atrodo neteisingas: jį sudaro 8 arba 11 raidžių ir skaičių.`],[`beneficiary`,v=>!/^(swedbank|seb|luminor|revolut|šiaulių bankas|citadele|paysera)( bankas)?$/i.test(v),`Čia turi būti sąskaitos savininkas (jūsų įmonė), ne banko pavadinimas.`],[`taxStatement`,v=>!/^[\d\s.,-]+$/.test(v),`Čia turi būti sakinys, ne skaičius.`],[`retentionPolicyReference`,v=>!/^\d+$/.test(v),`Čia turi būti tekstinė pastaba. Skaičius dienomis rašomas laukelyje kairėje.`],[`vatRate`,(v,l)=>!(Number(v)>0&&/keine\s+USt|nicht\s+für\s+Mehrwertsteuer/i.test(l.taxIdentifier||``)),`Įmonės duomenyse parašyta, kad PVM kodo nėra, bet tarifas ne 0.`]],
RbAdmMailKind={order:`Užsakymo patvirtinimas`,verify:`El. pašto patvirtinimo kodas`,recover:`Prieigos atkūrimas`,resume:`Tęsimo nuoroda`,"payment-received":`Gautas mokėjimas`,"review-started":`Pradėta peržiūra`,"request-information":`Prašymas papildyti`,"record-submission":`Pateikta Beitragsservice`,"record-response":`Gautas atsakymas`,withdraw:`Sutarties atsisakymas`,complaint:`Kliento žinutė`,"privacy-request":`Duomenų užklausa`,"staff-message":`Pranešimas mums`,"staff-invite":`Kvietimas kolegai`},
RbAdmMailStatus={queued:`Laukia eilėje`,retry:`Bus bandoma dar kartą`,sending:`Siunčiama`,accepted:`Išsiųsta`,delivered:`Pristatyta`,bounced:`Nepristatyta (atmesta)`,complained:`Pažymėta kaip šlamštas`,failed:`Nepavyko`,cancelled:`Atšaukta`,expired:`Nebeaktualu`,needs_review:`Reikia patikrinti`,preview:`Bandomasis`},
RbAdmDate=v=>{let d=new Date(typeof v==`number`?v*1e3:v);return isNaN(d)?String(v||``):d.toLocaleString(`lt-LT`,{year:`numeric`,month:`2-digit`,day:`2-digit`,hour:`2-digit`,minute:`2-digit`})};
function RbAdmSettings({settings:l,setSettings:u,onError:ie}){let[base,setBase]=(0,_.useState)(()=>JSON.stringify(l)),[busy,setBusy]=(0,_.useState)(!1),[msg,setMsg]=(0,_.useState)(``),[err,setErr]=(0,_.useState)(``),dirty=JSON.stringify(l)!==base,bad={};for(let k of Kc(l)){let m=RbAdmInvalid[k];m?bad[m[0]]=m[1]:bad[k]=`Neužpildyta.`}for(let g of RbAdmGroups)for(let[k]of g.fields)!bad[k]&&RbAdmPlaceholder(l[k])&&(bad[k]=`Čia dar laikinas tekstas. Įrašykite tikrą.`);for(let[k,ok,m]of RbAdmChecks)!bad[k]&&String(l[k]||``).trim()&&!ok(String(l[k]).trim(),l)&&(bad[k]=m);let all=RbAdmGroups.flatMap(g=>g.fields),done=all.filter(([k])=>!bad[k]).length;
(0,_.useEffect)(()=>{let f=e=>{dirty&&(e.preventDefault(),e.returnValue=``)};return window.addEventListener(`beforeunload`,f),()=>window.removeEventListener(`beforeunload`,f)},[dirty]);
async function save(next=l){setBusy(!0),setMsg(``),setErr(``);try{let r=(await K(`staff/settings`,{settings:next,expectedVersion:next.version},`PUT`)).settings;u(r),setBase(JSON.stringify(r)),setMsg(`Išsaugota `+new Date().toLocaleTimeString(`lt-LT`,{hour:`2-digit`,minute:`2-digit`}))}catch(e){let c=e instanceof Error?e.message:``;c===`LAUNCH_DETAILS_MISSING`?setErr(`Užsakymų įjungti negalima: dar ne viskas užpildyta (žr. raudonai pažymėtus laukelius).`):c===`EMAIL_NOT_CONFIGURED`?setErr(`Užsakymų įjungti negalima: neįjungtas laiškų siuntimas (meniu „El. paštas“).`):c===`VERSION_CONFLICT`?setErr(`Kažkas ką tik pakeitė nustatymus kitame lange. Atnaujinkite puslapį (jūsų neišsaugoti pakeitimai dings).`):ie(e)}finally{setBusy(!1)}}
let field=([k,label,help,pub,wide])=>{let id=`setting-`+k,v=String(l[k]??``),props={id,value:v,"aria-invalid":bad[k]?!0:void 0,"aria-describedby":id+`-help`,onChange:e=>u({...l,[k]:e.target.value})};return RbAdmF(`div`,{key:k,className:`rb-adm-field`+(wide?` rb-adm-wide`:``)+(bad[k]?` rb-adm-bad`:``)},RbAdmF(`div`,{className:`rb-adm-label`},RbAdmF(`label`,{htmlFor:id},label),RbAdmF(`span`,{className:`rb-adm-tag`+(pub?` rb-adm-pub`:``)},pub?`Mato klientai`:`Tik mums`)),wide?(0,D.jsx)(ms,{...props,rows:k.endsWith(`Text`)?8:3}):(0,D.jsx)(ps,props),RbAdmF(`p`,{id:id+`-help`,className:`rb-adm-help`},help),bad[k]&&RbAdmF(`p`,{className:`rb-adm-err`},bad[k]))};
return RbAdmF(`div`,{className:`rb-adm`},
RbAdmF(`section`,{className:`rb-adm-status mj-card`},
 RbAdmF(`div`,{className:`rb-adm-status-row`},RbAdmF(`div`,null,RbAdmF(`p`,{className:`rb-adm-kicker`},`Mokami užsakymai svetainėje`),RbAdmF(`p`,{className:`rb-adm-big `+(l.liveEnabled?`rb-adm-on`:`rb-adm-off`)},l.liveEnabled?`Įjungti: klientai gali užsisakyti`:`Išjungti: užsakyti negalima`)),RbAdmF(U,{variant:l.liveEnabled?`outline`:`default`,disabled:busy||(!l.liveEnabled&&Kc(l).length>0),onClick:()=>{let n={...l,liveEnabled:!l.liveEnabled};if(l.liveEnabled&&!window.confirm(`Išjungti užsakymus? Klientai nebegalės užsisakyti, kol vėl neįjungsite.`))return;save(n)}},l.liveEnabled?`Išjungti užsakymus`:`Įjungti užsakymus`)),
 RbAdmF(`div`,{className:`rb-adm-progress`},RbAdmF(`div`,{className:`rb-adm-bar`},RbAdmF(`span`,{style:{width:Math.round(done/all.length*100)+`%`}})),RbAdmF(`p`,null,`Tvarkingai užpildyta ${done} iš ${all.length} laukelių.`)),
 done<all.length&&RbAdmF(`div`,{className:`rb-adm-todo`},RbAdmF(`p`,null,RbAdmF(`strong`,null,`Dar reikia sutvarkyti:`)),RbAdmF(`ul`,null,...all.filter(([k])=>bad[k]).map(([k,label])=>RbAdmF(`li`,{key:k},RbAdmF(`a`,{href:`#setting-`+k,onClick:e=>{e.preventDefault();let x=document.getElementById(`setting-`+k);x&&(x.scrollIntoView({behavior:`smooth`,block:`center`}),x.focus({preventScroll:!0}))}},label))))),
 l.liveEnabled&&done<all.length&&RbAdmF(`p`,{className:`rb-adm-warn`},`Dėmesio: užsakymai įjungti, nors dalis duomenų dar laikini. Klientai tai mato.`)),
RbAdmF(`nav`,{className:`rb-adm-toc`,"aria-label":`Nustatymų skyriai`},...RbAdmGroups.map(g=>{let n=g.fields.filter(([k])=>bad[k]).length;return RbAdmF(`a`,{key:g.id,href:`#grp-`+g.id},g.title,n?RbAdmF(`span`,{className:`rb-adm-count`},n):RbAdmF(`span`,{className:`rb-adm-ok`},`✓`))})),
...RbAdmGroups.map(g=>RbAdmF(`section`,{key:g.id,id:`grp-`+g.id,className:`mj-card rb-adm-group`},RbAdmF(`h2`,null,g.title),g.note&&RbAdmF(`p`,{className:`rb-adm-note`},g.note),RbAdmF(`div`,{className:`rb-adm-grid`},...g.fields.map(field)))),
RbAdmF(`div`,{className:`rb-adm-save`+(dirty?` rb-adm-dirty`:``)},RbAdmF(`span`,{role:`status`},err?RbAdmF(`span`,{className:`rb-adm-err`},err):dirty?`Yra neišsaugotų pakeitimų.`:msg||`Visi pakeitimai išsaugoti.`),RbAdmF(U,{disabled:busy||!dirty,onClick:()=>save()},busy?`Saugoma…`:`Išsaugoti pakeitimus`)),
RbAdmF(RbAdmTech,{onError:ie}))}
function RbAdmTech({onError:e}){let[n,r]=(0,_.useState)({}),[i,a]=(0,_.useState)(!1),[c,l]=(0,_.useState)(``),[u,d]=(0,_.useState)(``);async function f(){let[e,t,n]=await Promise.all([K(`staff/notifications`),K(`staff/maintenance`),K(`staff/inbox`)]);r({...e,...t,...n})}(0,_.useEffect)(()=>{f().catch(e)},[]);async function p(t,r,s){a(!0);try{await K(`staff/`+t,r),l(s),await f()}catch(t){e(t)}finally{a(!1)}}let h=n.health||{},j=h.lastBackgroundJob,inbox=n.inbox||[];
return RbAdmF(`details`,{className:`mj-card rb-adm-tech`},RbAdmF(`summary`,null,RbAdmF(`strong`,null,`Techninė informacija`),RbAdmF(`span`,null,` laiškai, automatiniai darbai`),inbox.length?RbAdmF(`span`,{className:`rb-adm-count`},inbox.length):null),
RbAdmF(`dl`,{className:`rb-adm-stats`},RbAdmF(`div`,null,RbAdmF(`dt`,null,`Užsakymų sistemoje`),RbAdmF(`dd`,null,String(h.cases??`–`))),RbAdmF(`div`,null,RbAdmF(`dt`,null,`Laiškų laukia išsiuntimo`),RbAdmF(`dd`,null,String(h.pendingMail?.n??`–`))),RbAdmF(`div`,null,RbAdmF(`dt`,null,`Atvirų duomenų užklausų`),RbAdmF(`dd`,null,String(h.privacyRequests?.n??`–`))),RbAdmF(`div`,null,RbAdmF(`dt`,null,`Automatinis darbas paskutinį kartą`),RbAdmF(`dd`,null,j?RbAdmDate(j.updated_at):`dar nevyko`))),
c&&RbAdmF(`p`,{role:`status`,className:`rb-adm-help`},c),
RbAdmF(`div`,{className:`mj-actions`},RbAdmF(U,{disabled:i,variant:`outline`,onClick:()=>p(`notifications`,{action:`dispatch`},`Laukiantys laiškai išsiųsti.`)},`Išsiųsti laukiančius laiškus dabar`),RbAdmF(U,{disabled:i,variant:`outline`,onClick:()=>p(`maintenance`,{action:`expire-access`},`Pasibaigusios nuorodos išvalytos.`)},`Išvalyti pasibaigusias nuorodas`)),
RbAdmF(`p`,{className:`rb-adm-help`},`Laiškai išsiunčiami automatiškai kas valandą. Mygtuku galite juos išsiųsti iš karto.`),
inbox.length>0&&RbAdmF(`div`,{className:`rb-adm-inbox`},RbAdmF(`h3`,null,`Sutarties atsisakymai, kurių nepavyko priskirti užsakymui`),RbAdmF(`p`,{className:`rb-adm-help`},`Peržiūrėkite ir įrašykite, kaip išsprendėte.`),RbAdmF(`label`,{className:`mj-field`},`Kaip išspręsta`,(0,D.jsx)(ms,{value:u,onChange:e=>d(e.target.value),maxLength:2e3})),...inbox.map(e=>RbAdmF(`div`,{key:e.id,className:`mj-document`},RbAdmF(`p`,null,RbAdmDate(e.created_at)),RbAdmF(`pre`,null,JSON.stringify(JSON.parse(e.payload_json),null,2)),RbAdmF(U,{variant:`outline`,disabled:i||u.trim().length<3,onClick:()=>p(`inbox`,{id:e.id,reason:u},`Įrašyta.`)},`Pažymėti kaip išspręstą`)))),
RbAdmF(`h3`,null,`Paskutiniai laiškai`),
(n.mail||[]).length?RbAdmF(`div`,{className:`rb-adm-table-wrap`},RbAdmF(`table`,{className:`rb-adm-table`},RbAdmF(`thead`,null,RbAdmF(`tr`,null,RbAdmF(`th`,null,`Kada`),RbAdmF(`th`,null,`Laiškas`),RbAdmF(`th`,null,`Kam`),RbAdmF(`th`,null,`Būsena`),RbAdmF(`th`,null,``))),RbAdmF(`tbody`,null,...n.mail.slice(0,40).map(e=>RbAdmF(`tr`,{key:e.id},RbAdmF(`td`,null,RbAdmDate(e.created_at)),RbAdmF(`td`,null,RbAdmMailKind[e.kind]||e.kind),RbAdmF(`td`,null,e.recipient||``),RbAdmF(`td`,{className:[`failed`,`bounced`,`needs_review`,`complained`].includes(e.status)?`rb-adm-err`:``},RbAdmMailStatus[e.status]||e.status,e.error?` (${e.error})`:``),RbAdmF(`td`,null,e.status===`accepted`&&RbAdmF(U,{variant:`ghost`,disabled:i,onClick:()=>p(`notifications`,{action:`refresh`,id:e.id},`Būsena atnaujinta.`)},`Ar pristatyta?`))))))):RbAdmF(`p`,{className:`rb-adm-help`},`Laiškų dar nėra.`))}
'''

def rep(old, new, count=1):
    global s
    n = s.count(old)
    if n != count:
        sys.exit(f'anchor found {n}x, expected {count}: {old[:80]}')
    s = s.replace(old, new)

# 1. new components, right before the staff workspace component
rep('function bl({signedInEmail:e}={}){', COMPONENTS.strip() + '\nfunction bl({signedInEmail:e}={}){')

# 2. settings view: grouped Lithuanian form instead of the flat field list + old tech card
start = s.index('(0,D.jsxs)(D.Fragment,{children:[(0,D.jsx)(yl,{onError:ie}),(0,D.jsxs)(`section`,{className:`mj-card`,children:[(0,D.jsx)(tl,{children:n(`Complete your business')
end_marker = 'children:n(`Save settings`,`Einstellungen speichern`)})]})]})'
end = s.index(end_marker, start) + len(end_marker)
s = s[:start] + '(0,D.jsx)(RbAdmSettings,{settings:l,setSettings:u,onError:ie})' + s[end:]

# 3. header: Lithuanian navigation, team page link, signed-in email
rep('(0,D.jsx)(`a`,{href:`/admin/configuration`,children:`E-Mails & Einstellungen`}),e&&(0,D.jsx)(`span`,{className:`mj-help`,children:e}),',
    '(0,D.jsx)(`a`,{href:`/admin/team`,children:`Komanda`}),(0,D.jsx)(`a`,{href:`/admin/configuration`,children:`El. paštas`}),e&&(0,D.jsx)(`span`,{className:`mj-help`,children:e}),')
rep('children:n(`Sign out`,`Abmelden`)})]}),(0,D.jsx)(`a`,{href:`/antrag`,children:n(`Customer journey`,`Kundenablauf`)}),h&&(0,D.jsx)(U,{variant:`outline`,onClick:()=>{f(!d),x(``)},children:d?n(`Case workspace`,`Sachbearbeitung`):n(`Launch settings`,`Starteinstellungen`)})',
    'children:`Atsijungti`})]}),(0,D.jsx)(`a`,{href:`/antrag`,children:`Svetainė`}),h&&(0,D.jsx)(U,{variant:`outline`,onClick:()=>{f(!d),x(``)},children:d?`← Užsakymai`:`Nustatymai`})')
rep('children:d?n(`Prepare for launch`,`Produktivstart vorbereiten`):', 'children:d?`Nustatymai`:')
rep('children:n(`STAFF WORKSPACE`,`SACHBEARBEITUNG`)', 'children:d?`ADMINISTRAVIMAS`:n(`STAFF WORKSPACE`,`SACHBEARBEITUNG`)')

# 4. show the colleague's name instead of a hash for the assigned person
rep('i(e.cases||[]),g(!!t.admin)', 'i(e.cases||[]),window.__rbStaffNames=e.staffNames||{},g(!!t.admin)')
rep('j.assignedTo]', '(window.__rbStaffNames||{})[j.assignedTo]||j.assignedTo]')
rep('n(`Assigned staff ID`,`Zugeordnete Mitarbeiter-ID`)', 'n(`Assigned to`,`Zugeordnet`)')

open(path, 'w', encoding='utf-8').write(s)
print('patched', path)
