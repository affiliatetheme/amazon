# Migration auf die Amazon Creators API

> **Wichtig — bitte zuerst lesen**
>
> Amazon stellt die alte Product Advertising API (PAAPI 5) zum **30. April 2026**
> ein. Ab diesem Datum kann das Plugin ohne Migration keine neuen Produkte mehr
> importieren und keine Preise mehr aktualisieren. Die Migration dauert etwa
> **10 Minuten** und ist für alle Bestandsnutzer verpflichtend.

Diese Anleitung führt dich Schritt für Schritt durch die Umstellung. Du
benötigst keine technischen Vorkenntnisse — nur einen funktionierenden Zugang
zu deinem Amazon-Partnerprogramm-Konto und zu deiner WordPress-Installation.

---

## Inhaltsverzeichnis

1. [Was ändert sich?](#was-ändert-sich)
2. [Voraussetzungen](#voraussetzungen)
3. [Schritt 1: Credentials in Associates Central erstellen](#schritt-1-credentials-in-associates-central-erstellen)
4. [Schritt 2: Credentials im Plugin eintragen](#schritt-2-credentials-im-plugin-eintragen)
5. [Schritt 3: Alte Credentials entfernen](#schritt-3-alte-credentials-entfernen)
6. [Schritt 4: Verbindung testen](#schritt-4-verbindung-testen)
7. [Troubleshooting](#troubleshooting)
8. [Für Entwickler](#für-entwickler)
9. [FAQ](#faq)

---

## Was ändert sich?

Amazon ersetzt die klassische Product Advertising API durch die moderne
**Creators API**. Technisch bedeutet das: Anstelle eines klassischen AWS-Key-
Paars (Access Key ID + Secret Access Key) verwendet das Plugin ab Version 2.0
ein OAuth-basiertes Credential-Paar (**Credential ID + Credential Secret**),
das du separat im Amazon-Partnerprogramm erzeugst. Die gewohnten Plugin-
Funktionen — Produktimport, Preis-Updates, Feed-Verwaltung, ASIN-Grabber —
bleiben unverändert. Es ändern sich ausschließlich die Zugangsdaten und die
interne API-Anbindung.

Kurz gesagt:

- **Vorher:** Access Key ID + Secret Access Key (AWS IAM)
- **Nachher:** Credential ID + Credential Secret (Associates Central)
- **Gleich geblieben:** Partner Tag, Marketplace, alle Plugin-Einstellungen,
  alle bereits importierten Produkte.

---

## Voraussetzungen

Bevor du loslegst, stelle sicher, dass die folgenden Punkte erfüllt sind:

- [ ] **Aktives Amazon-Partnerprogramm-Konto** (Associates / PartnerNet).
      Dein Konto muss freigeschaltet und nicht gesperrt sein. Wenn dein
      Konto aufgrund fehlender Umsätze geschlossen wurde, reaktiviere es
      zuerst über den Amazon-Support.
- [ ] **PHP 8.2 oder höher** auf dem Webserver. Die neue API-Library
      benötigt moderne PHP-Features, die erst ab Version 8.2 verfügbar sind.
      Wenn du nicht weißt, welche PHP-Version dein Server nutzt: frage
      kurz beim Hoster nach oder sieh in deinem Hosting-Panel unter
      "PHP-Version" nach. Die meisten Hoster bieten PHP 8.2+ mit wenigen
      Klicks an.
- [ ] **Plugin-Version 2.0.0 oder neuer**. Öffne dazu in WordPress das
      Menü "Plugins" und prüfe bei "AffiliateTheme - Amazon Schnittstelle"
      die angezeigte Version. Falls du noch auf einer 1.x-Version bist,
      führe zuerst das Plugin-Update durch.
- [ ] **Backup deiner WordPress-Installation**. Die Migration ändert
      lediglich ein paar Einstellungen — kritische Daten werden nicht
      angefasst — aber ein aktuelles Backup schadet nie.

Sobald alle vier Punkte erfüllt sind, kannst du mit Schritt 1 beginnen.

---

## Schritt 1: Credentials in Associates Central erstellen

> **Wichtig — vor dem Start:**
> Lege jetzt ein Backup deiner WordPress-Datenbank an. Während der Migration werden nur die Zugangsdaten ausgetauscht, bestehende Produkte und Inhalte bleiben unberührt. Dennoch ist ein aktuelles Backup bei jedem Plugin-Update eine sinnvolle Vorsichtsmaßnahme.

Die neuen Zugangsdaten erstellst du direkt im Amazon-Partnerprogramm. Jeder
Marketplace hat seine eigene Oberfläche — nutze die Seite, die deinem
Affiliate-Konto entspricht.

### 1.1 Bei Associates Central anmelden

- [ ] Öffne die passende Associates-Seite für deinen Marketplace:

  | Marketplace            | URL                                         |
  | ---------------------- | ------------------------------------------- |
  | Deutschland            | https://partnernet.amazon.de                |
  | USA                    | https://affiliate-program.amazon.com        |
  | Vereinigtes Königreich | https://affiliate-program.amazon.co.uk      |
  | Frankreich             | https://partenaires.amazon.fr               |
  | Italien                | https://programma-affiliazione.amazon.it    |
  | Spanien                | https://afiliados.amazon.es                 |
  | Kanada                 | https://associates.amazon.ca                |
  | Japan                  | https://affiliate.amazon.co.jp              |
  | Australien             | https://affiliate-program.amazon.com.au     |
  | andere Länder          | analog über die jeweilige Amazon-Länderseite |

- [ ] Melde dich mit dem Amazon-Konto an, das zu deinem Partnerprogramm
      gehört. Das ist in der Regel dasselbe Konto, das du auch für den
      bisherigen API-Zugang verwendet hast.

### 1.2 Menü "Creators API" öffnen

- [ ] Navigiere im oberen Menü zu **Tools** und dort zum Eintrag
      **Creators API** (oder "API Credentials" — Amazon überarbeitet die
      Navigation gelegentlich, der Menüpunkt kann leicht abweichen).
- [ ] Solltest du den Menüpunkt nicht finden: nutze die Suche oben rechts
      ("Creators API") oder ruf direkt die Hilfe-Seite deines
      Marketplaces auf. Amazon bietet dort eine aktuelle Schritt-für-
      Schritt-Anleitung zur Credential-Erstellung.

### 1.3 Neues Credential-Set erzeugen

- [ ] Klicke auf **"Create new credential set"** bzw. die deutsche
      Entsprechung ("Neues Credential-Set erstellen").
- [ ] Wähle den **Marketplace / die Region**, die zu deinem Partner Tag
      passt. Wenn dein Partner Tag auf `-21` endet, ist das in der Regel
      Deutschland; `-20` ist die USA; `-21` kann auch UK/FR/IT/ES sein —
      orientiere dich am Marketplace, auf dem du bisher API-Anfragen
      gestellt hast.
- [ ] Vergib optional einen Namen für das Credential-Set (z.B.
      "WordPress Plugin" oder den Namen deiner Webseite). Das dient nur
      der Übersicht in Associates Central.
- [ ] Bestätige die Erstellung.

### 1.4 Credential ID und Credential Secret sichern

- [ ] Amazon zeigt dir jetzt **einmalig** zwei Werte an:
  - **Credential ID** (eine längere alphanumerische Zeichenkette)
  - **Credential Secret** (ebenfalls alphanumerisch, deutlich länger)
- [ ] Kopiere **beide** Werte sofort in einen Passwort-Manager oder
      einen sicheren Notizspeicher. Das **Credential Secret** wird
      danach nie wieder angezeigt. Wenn du es verlierst, musst du das
      Credential-Set neu erstellen.
- [ ] Schließe das Fenster erst, wenn du beide Werte gesichert hast.

> **Hinweis zu den alten AWS-Keys**
>
> Die alten AWS Access Keys (Access Key ID + Secret Access Key) funktionieren
> mit der neuen Creators API **nicht**. Du kannst sie nicht umwandeln,
> konvertieren oder migrieren — du musst ein komplett neues Credential-Paar
> erstellen wie oben beschrieben. Der Marketplace, der Partner Tag und dein
> Amazon-Konto bleiben aber unverändert.

---

## Schritt 2: Credentials im Plugin eintragen

Jetzt übernimmst du die eben erzeugten Werte in WordPress.

- [ ] Melde dich als Administrator in deiner WordPress-Installation an.
- [ ] Öffne im linken Menü **"Amazon"** (unter "Import").
- [ ] Wechsle oben in den Tab **"Einstellungen"**.
- [ ] Trage die neuen Zugangsdaten in die entsprechenden Felder ein:

  - **Credential ID** — die ID aus Schritt 1.4
  - **Credential Secret** — das Secret aus Schritt 1.4

- [ ] Prüfe die weiteren Felder:

  - **Partner Tag** — bleibt identisch zu vorher, z.B. `deintag-21`.
    Wenn du ihn änderst, werden alle Produkt-Links nach und nach auf
    den neuen Tag umgeschrieben.
  - **Land** — der Marketplace, den du auch beim Credential-Erstellen
    ausgewählt hast (z.B. "Deutschland").

- [ ] Klicke unten auf **"Speichern"**.
- [ ] Warte einen Moment. Unten im Dashboard führt das Plugin einen
      Verbindungstest durch. Nach wenigen Sekunden sollte erscheinen:

  > Verbindung erfolgreich hergestellt.

Erscheint stattdessen eine Fehlermeldung, springe zum Abschnitt
[Troubleshooting](#troubleshooting).

---

## Schritt 3: Alte Credentials entfernen

Solange das Plugin noch die alten AWS-Keys gespeichert hat, zeigt es ein
gelbes Banner über den alten Feldern an. Diese Felder werden nicht mehr
verwendet — du kannst sie jetzt gefahrlos leeren.

- [ ] Bleibe im Tab **"Einstellungen"**.
- [ ] Suche das gelbe Hinweisbanner mit dem Text:

  > Diese Felder sind veraltet und werden nicht mehr verwendet. Bitte
  > migriere zu den neuen Credentials.

- [ ] Unterhalb des Banners findest du die Felder **"Access Key ID
      (veraltet)"** und **"Secret Access Key (veraltet)"**.
- [ ] Leere beide Felder (markieren, löschen).
- [ ] Klicke auf **"Speichern"**.
- [ ] Das gelbe Banner und die alten Felder verschwinden. Ab jetzt nutzt
      das Plugin ausschließlich die neuen Creators-Credentials.

> **Hinweis**
>
> Wenn du die alten Keys lieber behalten möchtest (z.B. weil du sie
> anderweitig nutzt): du kannst sie auch stehen lassen. Das Plugin
> ignoriert sie, sobald gültige Creators-Credentials hinterlegt sind.
> Aus Übersichtsgründen empfehlen wir aber das Leeren.

---

## Schritt 4: Verbindung testen

Zum Abschluss prüfst du, ob tatsächlich eine funktionierende Verbindung zur
neuen API besteht — am einfachsten mit einer echten Produktsuche.

- [ ] Wechsle im Plugin in den Tab **"Suche"**.
- [ ] Gib ein einfaches Suchwort in das Feld "Suche nach Keyword(s)"
      ein, z.B. `Buch` oder `Kaffeemaschine`.
- [ ] Wähle eine Kategorie (z.B. "Alle Kategorien").
- [ ] Klicke auf **"Suchen"**.
- [ ] Nach wenigen Sekunden sollten Produkttreffer mit Vorschaubild,
      Titel und Preis erscheinen.

Wenn du Ergebnisse siehst: **Glückwunsch, die Migration ist abgeschlossen.**
Deine bestehenden Produkte werden ab jetzt über die neue API aktualisiert,
neue Imports laufen direkt über die Creators API.

Wenn keine Ergebnisse erscheinen oder eine Fehlermeldung auftaucht:
lies den nächsten Abschnitt.

---

## Troubleshooting

### "Verbindung konnte nicht hergestellt werden"

Die häufigste Ursache für diesen Fehler:

- **Tippfehler in den Credentials.** Trage Credential ID und Credential
  Secret erneut ein — am besten per Copy & Paste aus dem Passwort-Manager,
  nicht abtippen. Achte darauf, keine Leerzeichen vor oder hinter den
  Werten zu haben.
- **Falscher Marketplace.** Wenn du das Credential-Set für den
  deutschen Marketplace erstellt hast, muss im Plugin unter "Land" auch
  "Deutschland" ausgewählt sein. Ein USA-Credential funktioniert nicht
  für den DE-Marketplace und umgekehrt.
- **Noch nicht aktiviert.** Neu erstellte Credentials brauchen bei
  Amazon manchmal **bis zu 10 Minuten**, bis sie serverseitig aktiv
  sind. Warte kurz und speichere die Einstellungen dann erneut.
- **Partner Tag passt nicht zum Marketplace.** Prüfe, ob dein Partner
  Tag tatsächlich zu dem gewählten Marketplace gehört. Ein `-21`-Tag
  gehört z.B. zu mehreren europäischen Marketplaces, aber ein
  `-20`-Tag ist ausschließlich USA.

### "HTTP 429 — Too Many Requests"

Du hast in kurzer Zeit zu viele Anfragen an Amazon gestellt und bist in
das Rate Limit gelaufen.

- Warte **mindestens eine Stunde** und versuche es erneut.
- Importiere Produkte nicht im Sekundentakt — das Plugin verteilt
  Anfragen normalerweise selbst, aber manuelle Schnellsuchen können
  das Limit ausreizen.
- Wenn du wiederholt 429-Fehler siehst: reduziere das
  Aktualisierungsintervall unter "Einstellungen → Aktualisierungs-
  intervall für Produkte" auf einen höheren Wert (z.B. 3 oder 4
  Stunden).

### "InvalidArgumentException cn"

Du hast unter "Land" **China** (`cn`) ausgewählt. Der chinesische
Marketplace wird von der neuen Creators API derzeit **nicht unterstützt**.

- Wähle einen anderen Marketplace.
- Beobachte die Ankündigungen im Amazon-Partnerprogramm. Sollte China
  später unterstützt werden, aktualisieren wir das Plugin entsprechend.

### Fatal Error "PHP 8.2" oder "syntax error, unexpected token"

Dein Server läuft noch auf einer PHP-Version unter 8.2. Die neue API-
Library nutzt Sprachfeatures, die es erst ab 8.2 gibt, daher kann das
Plugin nicht laden.

- Logge dich in dein Hosting-Panel ein und wähle **PHP 8.2** oder
  höher für die betreffende Domain aus. Bei den meisten Hostern ist
  das ein einziger Klick.
- Kannst du die PHP-Version nicht selbst ändern: kontaktiere den
  Hoster-Support mit der Bitte, auf PHP 8.2+ umzustellen. Das ist
  eine Standardanfrage und meist innerhalb weniger Minuten erledigt.
- Wenn du zeitgleich viele Plugins/Themes einsetzt, die noch auf
  älteren PHP-Versionen aufbauen: teste vorher auf einer Staging-
  Umgebung.

### Allgemeine Fehler oder Rückfragen

- Prüfe das Tab **"API Log"** im Plugin. Dort werden die letzten 200
  Einträge protokolliert — inklusive Fehlermeldungen von Amazon.
- Stelle sicher, dass dein Server ausgehende HTTPS-Verbindungen zu
  `api.amazon.com` zulässt. Einige strenge Firewall-Setups blockieren
  neue Endpunkte.
- Wenn nichts davon hilft: melde dich im **AffiliateTheme-Forum** oder
  über den offiziellen Support-Kanal. Halte folgende Angaben bereit:
  - Plugin-Version
  - PHP-Version
  - Ausgewählter Marketplace
  - Fehlermeldung aus dem API Log (die letzten 5 Einträge)
  - Zeitpunkt der Credential-Erstellung

---

## Für Entwickler

Dieser Abschnitt ist optional und richtet sich an Entwickler, die das
Plugin erweitern oder in eigene Workflows einbinden.

### Interne API-Klasse

Der zentrale Einstiegspunkt für API-Aufrufe aus eigenem Code:

```php
use Endcore\AmazonApi;

$api = AmazonApi::fromWpOptions();
$results = $api->search('Buch');
```

`AmazonApi::fromWpOptions()` liest die Credentials direkt aus den
WordPress-Optionen (`amazon_credential_id`, `amazon_credential_secret`,
`amazon_partner_id`, `amazon_country`) und liefert eine konfigurierte
Instanz zurück.

### Library

- **Paket:** `Jakiboy/apaapi` v2.x
- **Einbindung:** vendored in `lib/apaapi/` (nicht über Composer
  auto-loaded vom Host-Projekt, sondern über den plugin-eigenen
  Bootstrap in `lib/bootstrap.php`).
- **Repository:** https://github.com/Jakiboy/apaapi

### Breaking Changes gegenüber Plugin 1.x

- Die **Request-Objekte der alten PAAPI 5** (z.B. `SearchItemsRequest`,
  `GetItemsRequest` aus dem alten `paapi5-php-sdk`) werden nicht mehr
  unterstützt. Wenn du eigene Filter oder Hooks gebaut hast, die
  direkt PAAPI-5-Request-Objekte manipuliert haben: diese müssen auf
  die neue Library umgestellt werden.
- Die Konstanten `AWS_API_KEY` und `AWS_API_SECRET_KEY` existieren aus
  Kompatibilitätsgründen weiterhin, sind aber nach erfolgreicher
  Migration leer. Neuer Code sollte stattdessen `AWS_CREDENTIAL_ID`
  und `AWS_CREDENTIAL_SECRET` verwenden.
- Der Cron-Hash (`AWS_CRON_HASH`) basiert nun bevorzugt auf den neuen
  Credentials und fällt nur auf die alten Keys zurück, wenn noch
  keine Creators-Credentials gesetzt sind. So bleiben bestehende
  `wp_cron`-Einträge beim Migrations-Wechsel gültig.

### Minimales PHP-Beispiel

```php
if ( ! class_exists( 'Endcore\AmazonApi' ) ) {
    return;
}

$api = \Endcore\AmazonApi::fromWpOptions();

try {
    $item = $api->lookup( 'B08N5WRWNW' ); // Beispiel-ASIN
    var_dump( $item->getTitle(), $item->getPrice() );
} catch ( \Throwable $e ) {
    error_log( 'Amazon API Fehler: ' . $e->getMessage() );
}
```

---

## FAQ

### Müssen meine bestehenden Produkte neu importiert werden?

**Nein.** Alle bereits importierten Produkte bleiben unverändert in der
Datenbank. Lediglich die **Preis- und Verfügbarkeits-Updates** sowie
**neue Imports** laufen künftig über die Creators API. Du musst also
nichts neu anlegen — das Plugin nutzt für laufende Produkte ab der
Migration automatisch die neuen Credentials.

### Was passiert, wenn ich nicht migriere?

Ab dem **30. April 2026** schaltet Amazon die alte Product Advertising
API (PAAPI 5) endgültig ab. Konkret:

- **Keine neuen Produkte** können über das Plugin importiert werden.
- **Keine Preis-Updates** laufen mehr — deine Produkte bleiben mit
  ihren zuletzt gespeicherten Preisen im Shop. Das ist rechtlich
  riskant: veraltete Preise in der Preisangabe-Verordnung (PAngV /
  DSGVO) können abgemahnt werden.
- **Bestehende Produkte** bleiben erhalten, inklusive aller Bilder,
  Beschreibungen und Links. Sie altern lediglich auf dem Preisstand
  vom 30.04.2026 ein.
- **Feeds** und **ASIN-Grabber** funktionieren nicht mehr, da sie
  ebenfalls API-Zugriffe benötigen.

Kurz: Ohne Migration bleibt dein Shop als Archiv bestehen, aber du
kannst ihn nicht mehr pflegen oder erweitern.

### Kostet die neue API extra?

**Nein.** Die Creators API ist — genau wie PAAPI 5 — **kostenlos** für
aktive Associates. Du zahlst nichts zusätzlich und brauchst auch kein
separates AWS-Konto mehr. Voraussetzung bleibt ein aktives Partnerkonto
mit regelmäßigen Umsätzen (Amazon behält sich vor, inaktive Konten
zu sperren; das ist aber unabhängig von der API-Umstellung).

### Kann ich beide APIs parallel betreiben?

Bis zum 30.04.2026 ja — technisch laufen beide APIs nebeneinander, auch
wenn das Plugin intern immer nur eine davon verwendet (bevorzugt die
neue, sobald Credentials hinterlegt sind). Nach dem 30.04.2026 schaltet
Amazon die alte API ab, dann bleibt nur noch die Creators API.

### Muss ich den Partner Tag neu erstellen?

**Nein.** Der Partner Tag (z.B. `deintag-21`) ist unabhängig von den
API-Credentials. Du behältst ihn unverändert — sonst müssten alle
bestehenden Produkt-Links umgeschrieben werden.

### Funktioniert die Migration auch auf Multisite-Installationen?

**Ja.** Die Migration läuft pro Site. Wenn du mehrere WordPress-Sites
mit dem Plugin betreibst, führe die Schritte für jede Site einmal
durch. Du kannst dabei **dasselbe Credential-Set** für mehrere Sites
verwenden — Amazon koppelt die Credentials an dein Associates-Konto,
nicht an eine spezifische Domain.

### Mein Credential Secret ist weg — was nun?

Das Secret wird nur einmal angezeigt. Wenn du es verloren hast:

1. Gehe in Associates Central zurück zu "Tools → Creators API".
2. Lösche das alte Credential-Set (oder lasse es deaktiviert liegen).
3. Erstelle ein neues Credential-Set — diesmal Secret direkt sichern.
4. Trage das neue Paar im Plugin ein.

### Wo finde ich Updates zum Status der Umstellung?

- Offizielle Amazon-Ankündigungen findest du im Associates-Newsletter
  und im Developer-Portal deiner Marketplace-Region.
- AffiliateTheme informiert über neue Plugin-Versionen im WordPress-Dashboard
  unter "Plugins".

---

*Stand: April 2026 — Version 2.0 der AffiliateTheme Amazon Schnittstelle.*
