// Date picker for the order form (field types date, dob, month).
// Inserted into the entry bundle by tools/datefield/patch.js; uses the bundle's
// React (_), JSX runtime (D) and limit helpers (RbDob, RbMin, RbMax).
// Value stays ISO (YYYY-MM-DD / YYYY-MM) so validation and the server are unchanged.
function RbDate({field:f,inputProps:c,value:v,onChange:ch,lang:a}){
  const en=a===`en`,mo=f.type===`month`,sep=en?`/`:`.`,
    min=f.type===`dob`?RbDob(128):RbMin(f),max=f.type===`dob`?RbDob(18):RbMax(f),
    names=en?[`January`,`February`,`March`,`April`,`May`,`June`,`July`,`August`,`September`,`October`,`November`,`December`]:[`Januar`,`Februar`,`März`,`April`,`Mai`,`Juni`,`Juli`,`August`,`September`,`Oktober`,`November`,`Dezember`],
    wd=en?[`Mo`,`Tu`,`We`,`Th`,`Fr`,`Sa`,`Su`]:[`Mo`,`Di`,`Mi`,`Do`,`Fr`,`Sa`,`So`],
    T=(x,y)=>en?x:y,p2=n=>String(n).padStart(2,`0`),
    today=new Date().toISOString().slice(0,10),
    iso=/^\d{4}-\d{2}(-\d{2})?$/,
    show=x=>iso.test(x||``)?(mo?x.slice(5,7)+sep+x.slice(0,4):x.slice(8,10)+sep+x.slice(5,7)+sep+x.slice(0,4)):x||``,
    parse=t=>{let d=t.replace(/\D/g,``);if(mo)return d.length===6?d.slice(2)+`-`+d.slice(0,2):null;return d.length===8?d.slice(4)+`-`+d.slice(2,4)+`-`+d.slice(0,2):null},
    [txt,setTxt]=(0,_.useState)(show(v)),[open,setOpen]=(0,_.useState)(!1),
    [view,setView]=(0,_.useState)(mo?`months`:`days`),
    start=()=>{let s=iso.test(v||``)?v:today;if(s>max)s=max;if(s<min)s=min;return[+s.slice(0,4),+s.slice(5,7)-1]},
    [ym,setYm]=(0,_.useState)(start),
    [focus,setFocus]=(0,_.useState)(null),
    box=(0,_.useRef)(null),inp=(0,_.useRef)(null),grid=(0,_.useRef)(null);
  (0,_.useEffect)(()=>{if(iso.test(v||``)){if(parse(txt)!==v)setTxt(show(v))}else if(!v)setTxt(``)},[v,a]);
  (0,_.useEffect)(()=>{if(!open)return;let h=e=>{box.current&&!box.current.contains(e.target)&&setOpen(!1)};document.addEventListener(`mousedown`,h);document.addEventListener(`touchstart`,h);return()=>{document.removeEventListener(`mousedown`,h);document.removeEventListener(`touchstart`,h)}},[open]);
  (0,_.useEffect)(()=>{if(!open)return;let el=grid.current&&(grid.current.querySelector(`[data-focus]`)||grid.current.querySelector(`.is-sel`)||grid.current.querySelector(`.is-today:not(:disabled)`)||grid.current.querySelector(`button:not(:disabled)`));el&&(view===`years`?(grid.current.scrollTop=el.offsetTop-grid.current.clientHeight/2+el.offsetHeight/2):el.focus())},[open,view,focus,ym]);
  function type(t){
    let d=t.replace(/\D/g,``).slice(0,mo?6:8),o=``;
    if(mo)o=d.length>2?d.slice(0,2)+sep+d.slice(2):d;
    else o=d.length>4?d.slice(0,2)+sep+d.slice(2,4)+sep+d.slice(4):d.length>2?d.slice(0,2)+sep+d.slice(2):d;
    setTxt(o);let p=parse(o);ch(f.key,p||(o?o:``))
  }
  function toggle(){if(open){setOpen(!1);return}setYm(start());setView(mo?`months`:`days`);setFocus(null);setOpen(!0)}
  function pick(x){ch(f.key,x);setTxt(show(x));setOpen(!1);inp.current&&inp.current.focus()}
  function shift(n){let m=ym[1]+n,y=ym[0]+Math.floor(m/12);m=(m%12+12)%12;setYm([y,m])}
  const y0=+min.slice(0,4),y1=+max.slice(0,4),ymKey=(y,m)=>y+`-`+p2(m+1),
    canPrev=ymKey(ym[0],ym[1])>min.slice(0,7),canNext=ymKey(ym[0],ym[1])<max.slice(0,7);
  function days(){
    let [y,m]=ym,first=(new Date(Date.UTC(y,m,1)).getUTCDay()+6)%7,n=new Date(Date.UTC(y,m+1,0)).getUTCDate(),cells=[];
    for(let i=0;i<first;i++)cells.push((0,D.jsx)(`span`,{},`e`+i));
    for(let d=1;d<=n;d++){let x=ymKey(y,m)+`-`+p2(d),off=x<min||x>max,sel=x===v;
      cells.push((0,D.jsx)(`button`,{type:`button`,disabled:off,className:`rb-cal-day${sel?` is-sel`:``}${x===today?` is-today`:``}`,"aria-pressed":sel,"aria-label":d+`. `+names[m]+` `+y,"data-day":x,tabIndex:focus?(focus===x?0:-1):(String(v||``).slice(0,7)===ymKey(y,m)?(sel?0:-1):today.slice(0,7)===ymKey(y,m)&&!off?(x===today?0:-1):(d===1?0:-1)),"data-focus":focus===x?``:void 0,onClick:()=>pick(x),children:d},x))}
    return cells
  }
  function key(e){
    if(e.key===`Escape`){e.preventDefault();setOpen(!1);inp.current&&inp.current.focus();return}
    if(view!==`days`)return;
    let st={ArrowLeft:-1,ArrowRight:1,ArrowUp:-7,ArrowDown:7}[e.key];if(!st)return;e.preventDefault();
    let cur=e.target.dataset&&e.target.dataset.day||focus||(iso.test(v||``)&&v.length===10&&v.slice(0,7)===ymKey(ym[0],ym[1])?v:ymKey(ym[0],ym[1])+`-01`),dt=new Date(cur+`T12:00:00Z`);
    dt.setUTCDate(dt.getUTCDate()+st);let nx=dt.toISOString().slice(0,10);if(nx<min||nx>max)return;
    setFocus(nx);setYm([+nx.slice(0,4),+nx.slice(5,7)-1])
  }
  let years=[];for(let y=y1;y>=y0;y--)years.push((0,D.jsx)(`button`,{type:`button`,className:`rb-cal-year${y===ym[0]?` is-sel`:``}`,onClick:()=>{setYm([y,ym[1]]);setView(`months`)},children:y},y));
  let months=names.map((nm,m)=>{let k=ymKey(ym[0],m),off=k<min.slice(0,7)||k>max.slice(0,7),sel=mo?v===k:(iso.test(v||``)&&v.slice(0,7)===k);
    return (0,D.jsx)(`button`,{type:`button`,disabled:off,className:`rb-cal-month${sel?` is-sel`:``}`,onClick:()=>{if(mo)pick(k);else{setYm([ym[0],m]);setView(`days`)}},children:nm.slice(0,3)},m)});
  const title=view===`days`?names[ym[1]]+` `+ym[0]:view===`months`?String(ym[0]):T(`Choose year`,`Jahr wählen`);
  return (0,D.jsxs)(`div`,{className:`rb-date`,ref:box,children:[
    (0,D.jsx)(ps,{...c,ref:inp,type:`text`,inputMode:`numeric`,autoComplete:f.type===`dob`?`bday`:`off`,placeholder:mo?T(`MM/YYYY`,`MM.JJJJ`):T(`DD/MM/YYYY`,`TT.MM.JJJJ`),value:txt,onChange:e=>type(e.target.value),onKeyDown:e=>{e.key===`ArrowDown`&&e.altKey&&(e.preventDefault(),toggle())},maxLength:mo?7:10}),
    (0,D.jsx)(`button`,{type:`button`,className:`rb-date-btn`,"aria-label":T(`Open calendar`,`Kalender öffnen`),"aria-expanded":open,onClick:toggle,children:(0,D.jsxs)(`svg`,{viewBox:`0 0 24 24`,width:20,height:20,"aria-hidden":!0,fill:`none`,stroke:`currentColor`,strokeWidth:1.8,strokeLinecap:`round`,children:[(0,D.jsx)(`rect`,{x:3.5,y:5,width:17,height:15.5,rx:3}),(0,D.jsx)(`path`,{d:`M3.5 10h17M8 3v4M16 3v4`})]})}),
    open&&(0,D.jsxs)(`div`,{className:`rb-cal`,role:`dialog`,"aria-label":f.label[a],onKeyDown:key,children:[
      (0,D.jsxs)(`div`,{className:`rb-cal-head`,children:[
        view===`days`?(0,D.jsx)(`button`,{type:`button`,className:`rb-cal-nav`,disabled:!canPrev,"aria-label":T(`Previous month`,`Vorheriger Monat`),onClick:()=>shift(-1),children:`‹`}):view===`months`?(0,D.jsx)(`button`,{type:`button`,className:`rb-cal-nav`,disabled:ym[0]<=y0,"aria-label":T(`Previous year`,`Vorheriges Jahr`),onClick:()=>setYm([ym[0]-1,ym[1]]),children:`‹`}):(0,D.jsx)(`span`,{}),
        (0,D.jsxs)(`button`,{type:`button`,className:`rb-cal-title`,"aria-live":`polite`,onClick:()=>setView(view===`years`?(mo?`months`:`days`):`years`),children:[title,(0,D.jsx)(`span`,{className:`rb-cal-caret`,"aria-hidden":!0,children:`▾`})]}),
        view===`days`?(0,D.jsx)(`button`,{type:`button`,className:`rb-cal-nav`,disabled:!canNext,"aria-label":T(`Next month`,`Nächster Monat`),onClick:()=>shift(1),children:`›`}):view===`months`?(0,D.jsx)(`button`,{type:`button`,className:`rb-cal-nav`,disabled:ym[0]>=y1,"aria-label":T(`Next year`,`Nächstes Jahr`),onClick:()=>setYm([ym[0]+1,ym[1]]),children:`›`}):(0,D.jsx)(`span`,{})]}),
      view===`days`&&(0,D.jsx)(`div`,{className:`rb-cal-wd`,"aria-hidden":!0,children:wd.map(x=>(0,D.jsx)(`span`,{children:x},x))}),
      (0,D.jsx)(`div`,{ref:grid,className:view===`days`?`rb-cal-days`:view===`months`?`rb-cal-months`:`rb-cal-years`,children:view===`days`?days():view===`months`?months:years}),
      (0,D.jsxs)(`div`,{className:`rb-cal-foot`,children:[
        f.type!==`dob`&&(mo?today.slice(0,7):today)>=min&&(mo?today.slice(0,7):today)<=max?(0,D.jsx)(`button`,{type:`button`,className:`rb-cal-link`,onClick:()=>pick(mo?today.slice(0,7):today),children:mo?T(`This month`,`Dieser Monat`):T(`Today`,`Heute`)}):(0,D.jsx)(`span`,{}),
        (0,D.jsx)(`button`,{type:`button`,className:`rb-cal-link`,onClick:()=>{setOpen(!1);inp.current&&inp.current.focus()},children:T(`Close`,`Schließen`)})]})]})]})
}
