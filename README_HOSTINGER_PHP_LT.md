# Rundfunkbeitrag Support – Hostinger PHP versija

Ši diegimo šaka skirta įprastam PHP ir MySQL hostingui. Hostinge nereikia Node.js, npm, Composer, Docker ar nuolat veikiančio serverio proceso. Naršyklės JavaScript ir CSS jau paruošti kataloge `public_html`, o PHP bibliotekos įtrauktos į `private/vendor`.

Išlaikytas patvirtintas antenos logotipas, tamsiai mėlynas dizainas, subtilūs vokiškų spalvų akcentai, Manrope tipografija, nepriklausomumo paaiškinimas viršuje ir poraštėje bei DE/EN klientų formos. Klientams nerodomi testinių duomenų ir simuliuoto mokėjimo valdikliai. Visos bylos kuriamos tik kaip realios bylos, paslaugą įjungus administratoriui. Paslaugos mokestis – 40 EUR, tik bankiniu pavedimu; bankas neintegruotas, gavimą patvirtina administratorius.

## 1. Ko reikia

- Hostinger planas su PHP ir MySQL bei prieiga prie failų tvarkyklės.
- PHP 8.3 ar naujesnė versija; ši versija patikrinta su PHP 8.3.
- Įjungti `pdo_mysql`, `mbstring`, `curl`, `fileinfo`, `dom`, `iconv`, `ctype`, `xml`, `simplexml` plėtiniai. PDF kūrimui rekomenduojama bent 128 MB PHP atmintis, geriau 256 MB.
- Domenas su veikiančiu HTTPS sertifikatu. Diegimas produkcijoje vyksta tik per HTTPS.
- Atskira MySQL duomenų bazė ir jai skirtas vartotojas.
- Laiškams – jūsų Resend API raktas ir siuntėjo adresas iš patvirtinto domeno. Raktą galima įvesti po diegimo, tačiau el. pašto patvirtinimas ir realūs užsakymai be laiškų siuntimo neįsijungs.

Hostinger pagalba: [PHP versija](https://www.hostinger.com/support/1575755-how-to-change-the-php-version-of-your-hostinger-hosting-plan/), [PHP plėtiniai](https://www.hostinger.com/support/4667515-how-to-manage-php-extensions-and-options-in-hostinger/), [MySQL ir PDO](https://www.hostinger.com/support/which-databases-and-data-tools-are-supported-at-hostinger/). Resend: [patvirtintas siuntėjo domenas](https://resend.com/docs/dashboard/domains/introduction), [API raktai](https://resend.com/docs/dashboard/api-keys/introduction).

## 2. Diegimas iš GitHub

Pirmiausia pasidarykite dabartinės svetainės failų ir duomenų bazės atsarginę kopiją. Pirmam patikrinimui patogiausia naudoti atskirą subdomeną su atskira duomenų baze. Paketas nekopijuoja esamų klientų duomenų iš ChatGPT Sites ar ankstesnės Node.js svetainės.

Hostinger atidarykite **Websites → jūsų svetainė → Dashboard → Advanced → Git** ir prijunkite GitHub. Pasirinkite saugyklą `evaldas0905/business-mvp`, o **Branch** lauke būtinai pasirinkite **`hostinger-php-2026-10-12`**. Numatytasis pasirinkimas `main` tebėra ankstesnė Node.js versija. PHP svetainei naudokite PHP/HTML Git integraciją, o ne „Deploy Web App“ Node.js vedlį.

Šioje diegimo šakoje nebėra npm `package.json` / užrakto failų, Vite konfigūracijos ar Node.js testavimo programos. Redaguojami išvaizdos kūrimo šaltiniai ir testai išsaugoti [atskiroje `php-source-2026-10-12` šakoje](https://github.com/evaldas0905/business-mvp/tree/php-source-2026-10-12). Naršyklės JavaScript failai `public_html/assets` yra paruošti ir veikia be Node.js serveryje.

Git nustatymų **Root directory** yra vieta **Hostinger serveryje**, į kurią bus įkelta saugykla. Tai nėra šaltinių poaplankio pasirinkimas. Ši struktūra numato diegimą domeno kataloge **vienu lygiu aukščiau už `public_html`**. Jei jūsų skydelis leidžia, pasirinkite būtent domeno katalogą. Prieš diegdami patikrinkite tikrą savo failų kelią ir išsaugokite esamos svetainės kopiją. Jei skydelis leidžia klonuoti tik `public_html` viduje, klonuokite į laikiną neveikiantį aplanką, tada per failų tvarkyklę perkelkite saugyklos `public_html` turinį į tikrą viešąjį katalogą, o `private` – už jo ribų, šalia jo. Naudojant rankinį perkėlimą, automatinį Git diegimą laikykite išjungtą, kol nenustatytas teisingas galutinis kelias.

Katalogai `public_html` ir `private` turi būti vienas šalia kito:

| Katalogas / failas | Vieta ir paskirtis |
| --- | --- |
| `public_html/index.php`, `.htaccess`, `assets`, `brand`, `fonts` | Domeno viešasis `public_html` katalogas. Kopijuokite ir `.htaccess`, ir `.vite/manifest.json`. |
| `private/app`, `private/vendor`, `private/cron.php` | Domeno kataloge šalia `public_html`, už viešojo katalogo ribų. |
| `private/installation-key.example.php` | Prieš pirmą paleidimą nukopijuokite į `private/installation-key.php` ir įrašykite savo atsitiktinį raktą. |
| `README_HOSTINGER_PHP_LT.md` | Diegimo instrukcija. Laikykite už viešojo katalogo ribų. |
| `VALIDATION.md` | Vietinių patikrų rezultatai; kūrimo šaltiniai yra atskiroje šakoje. |

Nediekite visos saugyklos tiesiai į viešąjį katalogą ir nepalikite papildomo `public_html/public_html` katalogo. Patikrinkite galutinę katalogų struktūrą. Naudokite švarų viešąjį katalogą arba pašalinkite ankstesnės svetainės failus tik turėdami jos atsarginę kopiją.

PHP procesas turi galėti skaityti `private` ir rašyti jo viduje. Diegimas sukurs `private/config.php` ir `private/data`. Paprastai Hostinger failų tvarkyklėje įkelti failai jau priklauso teisingam vartotojui; nenaudokite viešai rašomų 777 teisių. Konfigūracija ir klientų failai kuriami su 600 teisėmis, duomenų katalogai – 700.

## 3. Pirmasis paleidimas

1. Hostinger skydelyje pasirinkite PHP 8.3 arba naujesnę versiją ir įjunkite reikalingus plėtinius.
2. Sukurkite tuščią MySQL duomenų bazę bei jos vartotoją. Nusikopijuokite **visus** skydelyje rodomus pavadinimus su Hostinger prefiksais, serverio adresą ir slaptažodį.
3. Failų tvarkyklėje nukopijuokite `private/installation-key.example.php` į `private/installation-key.php`. Pakeiskite `CHOOSE_AT_LEAST_24_RANDOM_CHARACTERS` į savo bent 24 atsitiktinių simbolių raktą, pvz., sugeneruotą slaptažodžių tvarkykle. GitHub šio privataus rakto nėra.
4. Atidarykite `https://jusu-domenas.lt/install` ir įveskite pasirinktą diegimo raktą. Jo niekam nesiųskite ir nelaikykite viešajame kataloge.
5. Įveskite tikslų svetainės adresą, MySQL duomenis, administratoriaus el. paštą ir savo naują slaptažodį. Slaptažodis turi būti bent 14 simbolių ir ne ilgesnis nei 72 UTF-8 baitai.
6. Jei turite, įveskite Resend API raktą ir patvirtintą siuntėją, pvz. `Rundfunkbeitrag Support <support@jusu-domenas.lt>`.
7. Paspauskite „Paruošti svetainę“. Vedlys automatiškai sukuria lentelę, privatų konfigūracijos failą ir slaptažodžio maišą. Po sėkmingo diegimo `/install` išsijungia, o vienkartinis rakto failas ištrinamas.

Hostingui nereikia paleisti terminalo komandų. Duomenų bazės slaptažodžiai ir laiškų API raktai yra jūsų paskyrų duomenys, todėl pakete jie sąmoningai neužpildyti.

## 4. Administratoriaus prisijungimas ir paslaugos įjungimas

Administratoriui: **`https://jusu-domenas.lt/admin/login`**. Prisijunkite vedlyje pasirinktu el. paštu ir slaptažodžiu. Skydelis: `/admin`. Klientų svetainėje administratoriaus mygtuko nėra; nuorodą išsisaugokite naršyklėje. Slapta nuoroda nėra vienintelė apsauga – būtinas tikras prisijungimas.

Prisijungimas galioja iki 8 valandų. Visos svetainės sesijos saugomos privačiai; slapukai per HTTPS yra Secure, HttpOnly ir SameSite Strict. Norint pakeisti pamirštą administratoriaus slaptažodį, reikia Hostinger failų prieigos ir naujo PHP `password_hash` rezultato `private/config.php` lauke `admin_hash`; viešo slaptažodžio atkūrimo nėra. Neištrinkite konfigūracijos, kad „perdiegtumėte“ esamą duomenų bazę.

Skydelyje „E-Mails & Einstellungen“ (`/admin/configuration`) galima papildyti Resend raktą, siuntėją ir vėliau leisti paieškos sistemų indeksavimą. Tuščias API rakto laukas išsaugo ankstesnį raktą. Pradžioje indeksavimas išjungtas.

Administratoriaus paslaugos nustatymuose įrašykite savo tikrus įmonės / veiklos duomenis, kontaktus, gavėjo pavadinimą, IBAN, BIC, paslaugos, privatumo ir atsisakymo tekstus, įgaliojimo priėmimo, mokesčių, dokumentų saugojimo ir paleidimo patvirtinimo informaciją. Tada įjunkite realius užsakymus. Šie paleidimo nustatymai nėra testavimo režimas: jie neleidžia priimti mokamų užsakymų, kol nėra jūsų rekvizitų ir veikiančio el. pašto siuntimo. Pakete nėra išgalvotų verslo rekvizitų.

## 5. Kur matysite klientų duomenis

| Informacija | Kur ją matysite |
| --- | --- |
| Kliento pateiktas užsakymas | `/admin` bylų sąraše su užsakymo nuoroda, paslauga ir būsena. Sąraše rodoma iki 200 naujausių pateiktų bylų; nebaigti juodraščiai į šią eilę nepatenka. |
| Vardas, adresas, el. paštas, datos ir kiti tekstiniai duomenys | Pasirinktos bylos formos laukuose. Skydelyje galima atsisiųsti bylos JSON kopiją. |
| Kliento įkeltas PDF arba nuotrauka | Byloje pateikiamas failų sąrašas su pavadinimu ir patikros būsena; dokumentą atsisiunčiate apsaugota nuoroda. Failai nėra viešai pasiekiami. |
| Užsakymo patvirtinimas ir įgaliojimas | Byloje kaip sugeneruotas PDF / HTML, užfiksuojant užsakymo metu galiojusias sąlygas. Klientui siunčiamas patvirtinimo laiškas su dokumentais. |
| Mokėjimas | Administratorius banke patikrina pavedimą, bylos patikrose pažymi gavimą ir nurodo pavedimo nuorodą. Svetainė pati netikrina banko. |
| Pateikimas ir oficialus atsakymas | Administratorius įrašo pateikimo nuorodą, įkelia kvitą ir vėliau oficialų atsakymą. Svetainė pati automatiškai neteikia prašymų Beitragsservice. |

Tekstiniai duomenys, bylos, jų įvykių istorija, laiškų eilė, vienkartinės prieigos ir sąskaitų numeracija saugomi MySQL lentelėje `rb_records`; struktūriniai bylos duomenys – JSON formatu. Originalūs PDF/JPG/PNG laikomi `private/data/uploads` kataloge. Dokumentų nuorodos tikrina administratoriaus arba konkretaus kliento prieigą. Vienoje byloje galima iki 5 įprastų dokumentų, iš viso iki 20 MB; pateikimo kvitai ir oficialūs atsakymai įkeliami administratoriaus darbo eigoje.

Klientas gali tęsti bylą savo naršyklėje, susikurti privačią tęstinumo nuorodą arba atkurti prieigą per el. paštą ir užsakymo nuorodą. Atkūrimo nuoroda vienkartinė, galioja 30 minučių ir pakeičia ankstesnio naršyklės seanso prieigą. El. pašto patvirtinimas būtinas prieš pateikiant mokamą užsakymą.

## 6. Laiškai ir periodinė užduotis

Po kliento veiksmo svetainė iš karto bando išsiųsti vieną eilės laišką. Laiškų priėmimas siuntimo sistemoje ir pristatymas klientui yra atskiros būsenos; administratoriaus laiškų skydelyje galima atnaujinti pristatymo informaciją.

Hostinger „Cron Jobs“ skiltyje rekomenduojama sukurti PHP užduotį, kas minutę arba jūsų plano leidžiamu trumpiausiu intervalu paleidžiančią **`private/cron.php`**. Pasirinkite PHP 8.3 vykdymą ir savo failų tvarkyklėje matomą absoliutų kelią. Pavyzdinis failo kelias: `/home/u123456789/domains/jusu-domenas.lt/private/cron.php`; pakeiskite jį savo tikru keliu. Jei skydelis pats prideda PHP vykdymo komandą, įrašykite tik failo kelią. PHP CLI procesas turi naudoti tas pačias reikalingas plėtinių versijas.

Ši trumpa PHP užduotis apdoroja likusią laiškų eilę, laikinų klaidų pakartojimus ir pasibaigusias prieigas. Ji nėra Node.js programa ar nuolat veikiantis procesas. Be cron eilę galima apdoroti rankiniu administratoriaus veiksmu, tačiau automatiniai pakartojimai tylos metu nevyks.

## 7. Patikrinimas po įkėlimo

Atidarykite pagrindinį puslapį, paslaugų puslapius, DE/EN kalbos pasirinkimą, klientų formas ir `/admin/login`. Patikrinkite per HTTPS, kad klientai nematytų testinių valdiklių ir administratoriaus mygtuko. Prisijunkite, išsaugokite savo nustatymus, patikrinkite vieną savo el. pašto patvirtinimą, dokumento įkėlimą ir gavimą administratoriaus skydelyje. Realų bandomą užsakymą kurkite tik suprasdami, kad jis saugomas kaip tikras užsakymas. Paketas pats neatlieka bankinių pavedimų.

Jei gaunate 404 kitame puslapyje nei pagrindinis, patikrinkite, ar įkeltas `.htaccess`. Jei gaunate 503, patikrinkite PHP versiją, plėtinius, MySQL duomenis, privačių katalogų teises ir Hostinger klaidų žurnalą. Jei puslapis be stiliaus, patikrinkite `assets`, `fonts` ir `.vite/manifest.json`. Šriftams PDF nepalaikant tam tikrų ženklų, galima atsisiųsti HTML dokumentą; šis variantas išsaugo visą UTF-8 tekstą.

## 8. Atsarginės kopijos ir atnaujinimai

Kopijuokite kartu MySQL duomenų bazę ir `private/data/uploads`; saugiai atskirai išsaugokite `private/config.php`. Duomenų bazę eksportuokite per Hostinger phpMyAdmin arba hPanel atsargines kopijas. Nuosekliai kopijai laikinai sustabdykite užsakymus, administratoriaus pakeitimus ir cron, padarykite abiejų saugyklų kopiją, tada vėl įjunkite. Nelaikykite kopijų viešajame kataloge ir retkarčiais patikrinkite atkūrimą.

Atnaujindami kodą išsaugokite esamus `private/config.php` ir `private/data`. Nekopijuokite naujo `installation-key.php` į jau įdiegtą svetainę. Išsaugotas įmonės nustatymų tekstas nekeičia anksčiau pateiktų užsakymų dokumentų. Klientų duomenų ištrynimas leidžiamas tik užbaigtai bylai, pasibaigus jūsų nustatytam saugojimo laikui ir aiškiai patvirtinus ištrynimą administratoriaus skydelyje.

### Automatinis atnaujinimas iš GitHub (FTP)

Hostinger „Git“ automatinio diegimo **nenaudokite**: jis visą saugyklą nukopijuoja į vieną aplanką, todėl atsiranda `public_html/public_html` ir svetainė rodo 403. Vietoj to kiekvieną `main` šakos pakeitimą įkelia GitHub Actions (`.github/workflows/deploy.yml`): `public_html` turinys patenka į svetainės `public_html`, o `private` – šalia jo. Failų `private/config.php`, `private/installation-key.php` ir `private/data` jis neliečia.

1. hPanel → **Files → FTP Accounts**: susiraskite FTP serverį (hostname arba IP), vartotojo vardą ir, jei reikia, nustatykite naują slaptažodį.
2. GitHub saugykloje → **Settings → Secrets and variables → Actions → New repository secret**: sukurkite `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`.
3. Jei prisijungus per FTP aplankas `public_html` nėra pačiame pradiniame kataloge (pvz., yra `domains/jusu-domenas/public_html`), skirtuke **Variables** nustatykite `FTP_PUBLIC_DIR` ir `FTP_PRIVATE_DIR` (pvz., `domains/jusu-domenas/public_html/` ir `domains/jusu-domenas/private/`). Jei FTPS neveikia, nustatykite `FTP_PROTOCOL` = `ftp`.
4. GitHub → **Actions → Deploy to Hostinger → Run workflow**, kad paleistumėte pirmą kartą. Toliau jis pasileis pats po kiekvieno pakeitimo `main` šakoje.

## 9. Kūrimo šaltiniai

`frontend` yra pasirenkamas React / Vite išvaizdos kūrimo projektas atskiroje [šaltinių šakoje](https://github.com/evaldas0905/business-mvp/tree/php-source-2026-10-12). Diegimo šakoje šio katalogo nėra. Tik jei ateityje norėsite patys keisti šaltinius, savo kompiuteryje su Node.js 24 vykdykite `npm ci`, `npm run check`, `npm run build` šiame kataloge. Rezultatas patenka į `public_html`; hostinge Node.js nereikia. PDF bibliotekos jau įtrauktos, o jų versijos užfiksuotos `private/composer.lock`.

Šis paketas paruoštas pagal paskutinį išsaugotą „Version 19 review“ šaltinį, commit `00cf71e076afe7fa338a5510bb7cfbf4ff115182`. Tai atskira PHP adaptacija. ChatGPT Sites peržiūra lieka ankstesnė Sites versija; PHP paketo įkėlimas į jūsų Hostinger dar neatliktas. Detalios vietinės patikros ir jų ribos – `VALIDATION.md`.
