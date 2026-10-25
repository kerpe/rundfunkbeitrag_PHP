#!/usr/bin/env python3
"""Admin top bar with tabs (Užsakymai, Nustatymai, Komanda, Sistema) and the signed-in account.

Run after settings-patch.py and cases-patch.py: python3 tools/admin/shell-patch.py public_html/assets/entry-XXXX.js
The server-rendered admin pages (private/app/staff.php admin_page) print the same markup.
"""
import sys

path = sys.argv[1]
s = open(path, encoding='utf-8').read()

def rep(old, new):
    assert s.count(old) == 1, (old[:80], s.count(old))
    return s.replace(old, new)

COMPONENT = r'''function RbAdmTop({active:a,email:m,onOrders:o,onSettings:st}){let tab=(id,label,href,click)=>RbAdmF(`a`,{key:id,href,className:`rb-adm-tab`+(a===id?` is-active`:``),"aria-current":a===id?`page`:void 0,onClick:click?e=>{e.preventDefault(),click()}:void 0},label);
return RbAdmF(`header`,{className:`rb-adm-top`},RbAdmF(`div`,{className:`rb-adm-top-in`},
RbAdmF(`a`,{className:`rb-adm-logo`,href:`/admin`,onClick:e=>{e.preventDefault(),o()}},(0,D.jsx)(se,{})),
RbAdmF(`nav`,{className:`rb-adm-tabs`,"aria-label":`Administravimas`},tab(`orders`,`Užsakymai`,`/admin`,o),tab(`settings`,`Nustatymai`,`/admin#nustatymai`,st),tab(`team`,`Komanda`,`/admin/team`),tab(`system`,`Sistema`,`/admin/configuration`)),
RbAdmF(`div`,{className:`rb-adm-user`},RbAdmF(`a`,{className:`rb-adm-me`,href:`/admin/account`,title:`Mano paskyra`},RbAdmF(`span`,{className:`rb-adm-avatar`,"aria-hidden":`true`},(m||`?`).slice(0,1).toUpperCase()),RbAdmF(`span`,{className:`rb-adm-me-mail`},m)),RbAdmF(`a`,{className:`rb-adm-site`,href:`/`,target:`_blank`,rel:`noopener`},`Svetainė ↗`),RbAdmF(`form`,{method:`post`,action:`/api/admin/logout`},RbAdmF(`input`,{type:`hidden`,name:`csrf`,value:window.PHPBOOT.csrf}),RbAdmF(`button`,{type:`submit`,className:`rb-adm-logout`},`Atsijungti`)))))}
'''
s = rep('function RbAdmSettings(', COMPONENT + 'function RbAdmSettings(')

# settings open straight from /admin#nustatymai (links from the server-rendered admin pages)
s = rep('[d,f]=(0,_.useState)(!1),[p,m]=', '[d,f]=(0,_.useState)(()=>typeof location<`u`&&location.hash===`#nustatymai`),[p,m]=')

start = s.index('(0,D.jsxs)(`header`,{className:`mj-header`,children:[(0,D.jsx)(`a`,{className:`mj-brand`')
end_marker = 'children:d?`← Užsakymai`:`Nustatymai`})]})]}),'
end = s.index(end_marker, start) + len(end_marker)
s = s[:start] + '(0,D.jsx)(RbAdmTop,{active:d?`settings`:`orders`,email:e,onOrders:()=>{f(!1),o(null),x(``),history.replaceState(null,``,`/admin`),oe()},onSettings:()=>{f(!0),x(``),history.replaceState(null,``,`/admin#nustatymai`)}}),' + s[end:]

open(path, 'w', encoding='utf-8').write(s)
