# POT-Datei aktualisieren

Die `affiliatetheme-amazon.pot` enthält noch die Strings von v1.8 (Stand 2020-08-28).

Seit v2.0 (Migration auf Creators API) neu:

- Credential ID
- Credential Secret
- Access Key ID (veraltet)
- Secret Access Key (veraltet)
- Migration erforderlich
- Amazon hat die alte Product Advertising API (AWS Access Keys) abgekündigt...
- Willkommen — noch keine Zugangsdaten hinterlegt
- Anleitung öffnen
- Weitere Hilfe im Web
- Einrichtungsanleitung
- derzeit nicht unterstützt
- Verbindung hergestellt, aber Amazon-Rate-Limit erreicht...

Empfohlen: `wp i18n make-pot . languages/affiliatetheme-amazon.pot` aus Plugin-Root.
Danach en_GB/en_US/es_ES .po-Dateien mergen lassen (`msgmerge`).
