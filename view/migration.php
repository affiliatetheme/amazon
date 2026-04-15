<?php
/**
 * Migrations-Guide als gerenderter Admin-Tab.
 * Inhaltliche Quelle: MIGRATION.md (1:1 in HTML übertragen, gestylt im WP-Admin-Look).
 *
 * Erwartet die folgenden Variablen aus dem umschließenden panel.php-Scope:
 *   $has_new, $has_old, $deadline_text, $days_left
 */

// Defensive Defaults, falls die Datei isoliert eingebunden wird.
$has_new                 = isset( $has_new ) ? $has_new : false;
$has_old                 = isset( $has_old ) ? $has_old : false;
$deadline_text           = isset( $deadline_text ) ? $deadline_text : '';
?>
<div id="migration" class="at-api-tab">
	<div class="metabox-holder">

		<!-- Status-Hero-Box -->
		<?php if ( ! $has_new && $has_old ): ?>
			<div class="postbox" style="border-left:4px solid #d63638;">
				<div class="inside">
					<h2 style="margin-top:0;color:#d63638;"><span class="dashicons dashicons-warning" style="color:#d63638;"></span> <?php _e( 'Migration erforderlich', 'affiliatetheme-amazon' ); ?></h2>
					<p style="font-size:15px;"><strong><?php echo esc_html( $deadline_text ); ?></strong></p>
					<p><?php _e( 'Folge dieser Anleitung Schritt für Schritt. Die Migration dauert ca. 10 Minuten, sofern alle Voraussetzungen erfüllt sind.', 'affiliatetheme-amazon' ); ?></p>
				</div>
			</div>
		<?php elseif ( $has_new && ! $has_old ): ?>
			<div class="postbox" style="border-left:4px solid #00a32a;">
				<div class="inside">
					<h2 style="margin-top:0;color:#00a32a;"><span class="dashicons dashicons-yes-alt" style="color:#00a32a;"></span> <?php _e( 'Migration abgeschlossen', 'affiliatetheme-amazon' ); ?></h2>
					<p><?php _e( 'Deine Zugangsdaten sind auf die Creators API umgestellt. Diese Anleitung bleibt als Referenz verfügbar.', 'affiliatetheme-amazon' ); ?></p>
				</div>
			</div>
		<?php elseif ( $has_new && $has_old ): ?>
			<div class="postbox" style="border-left:4px solid #dba617;">
				<div class="inside">
					<h2 style="margin-top:0;"><span class="dashicons dashicons-info-outline"></span> <?php _e( 'Fast fertig — Aufräumen', 'affiliatetheme-amazon' ); ?></h2>
					<p><?php _e( 'Die neuen Zugangsdaten sind aktiv. Bitte entferne jetzt noch die alten AWS-Keys im Einstellungen-Tab.', 'affiliatetheme-amazon' ); ?></p>
				</div>
			</div>
		<?php else: ?>
			<div class="postbox" style="border-left:4px solid #dba617;">
				<div class="inside">
					<h2 style="margin-top:0;"><span class="dashicons dashicons-flag"></span> <?php _e( 'Willkommen', 'affiliatetheme-amazon' ); ?></h2>
					<p><?php _e( 'Folge dieser Anleitung, um das Plugin erstmalig mit deinem Amazon-Partner-Konto zu verbinden.', 'affiliatetheme-amazon' ); ?></p>
				</div>
			</div>
		<?php endif; ?>

		<!-- Einleitung -->
		<div class="postbox">
			<div class="inside">
				<h2 style="margin-top:0;"><?php _e( 'Migration auf die Amazon Creators API', 'affiliatetheme-amazon' ); ?></h2>
				<div class="mig-callout mig-callout-danger">
					<p><strong><?php _e( 'Wichtig — bitte zuerst lesen', 'affiliatetheme-amazon' ); ?></strong></p>
					<p><?php _e( 'Amazon stellt die alte Product Advertising API (PAAPI 5) zum <strong>30. April 2026</strong> ein. Ab diesem Datum kann das Plugin ohne Migration keine neuen Produkte mehr importieren und keine Preise mehr aktualisieren. Die Migration dauert etwa <strong>10 Minuten</strong> und ist für alle Bestandsnutzer verpflichtend.', 'affiliatetheme-amazon' ); ?></p>
				</div>
				<p><?php _e( 'Diese Anleitung führt dich Schritt für Schritt durch die Umstellung. Du benötigst keine technischen Vorkenntnisse — nur einen funktionierenden Zugang zu deinem Amazon-Partnerprogramm-Konto und zu deiner WordPress-Installation.', 'affiliatetheme-amazon' ); ?></p>
			</div>
		</div>

		<!-- Inhaltsverzeichnis -->
		<div class="postbox">
			<div class="inside">
				<h3 style="margin-top:0;"><?php _e( 'Inhalt', 'affiliatetheme-amazon' ); ?></h3>
				<ol class="mig-toc">
					<li><a href="#mig-what-changed"><?php _e( 'Was ändert sich?', 'affiliatetheme-amazon' ); ?></a></li>
					<li><a href="#mig-requirements"><?php _e( 'Voraussetzungen', 'affiliatetheme-amazon' ); ?></a></li>
					<li><a href="#mig-step1"><?php _e( 'Schritt 1: Credentials in Associates Central erstellen', 'affiliatetheme-amazon' ); ?></a></li>
					<li><a href="#mig-step2"><?php _e( 'Schritt 2: Credentials im Plugin eintragen', 'affiliatetheme-amazon' ); ?></a></li>
					<li><a href="#mig-step3"><?php _e( 'Schritt 3: Alte Credentials entfernen', 'affiliatetheme-amazon' ); ?></a></li>
					<li><a href="#mig-step4"><?php _e( 'Schritt 4: Verbindung testen', 'affiliatetheme-amazon' ); ?></a></li>
					<li><a href="#mig-troubleshooting"><?php _e( 'Troubleshooting', 'affiliatetheme-amazon' ); ?></a></li>
					<li><a href="#mig-faq"><?php _e( 'FAQ', 'affiliatetheme-amazon' ); ?></a></li>
					<li><a href="#mig-dev"><?php _e( 'Für Entwickler', 'affiliatetheme-amazon' ); ?></a></li>
				</ol>
			</div>
		</div>

		<!-- Sektion: Was ändert sich? -->
		<div class="postbox" id="mig-what-changed">
			<h3 class="hndle" style="padding:12px 14px;"><span><?php _e( 'Was ändert sich?', 'affiliatetheme-amazon' ); ?></span></h3>
			<div class="inside">
				<p><?php _e( 'Amazon ersetzt die klassische Product Advertising API durch die moderne <strong>Creators API</strong>. Technisch bedeutet das: Anstelle eines klassischen AWS-Key-Paars (Access Key ID + Secret Access Key) verwendet das Plugin ab Version 2.0 ein OAuth-basiertes Credential-Paar (<strong>Credential ID + Credential Secret</strong>), das du separat im Amazon-Partnerprogramm erzeugst. Die gewohnten Plugin-Funktionen — Produktimport, Preis-Updates, Feed-Verwaltung, ASIN-Grabber — bleiben unverändert. Es ändern sich ausschließlich die Zugangsdaten und die interne API-Anbindung.', 'affiliatetheme-amazon' ); ?></p>
				<p><strong><?php _e( 'Kurz gesagt:', 'affiliatetheme-amazon' ); ?></strong></p>
				<ul style="list-style:disc;padding-left:20px;">
					<li><?php _e( '<strong>Vorher:</strong> Access Key ID + Secret Access Key (AWS IAM)', 'affiliatetheme-amazon' ); ?></li>
					<li><?php _e( '<strong>Nachher:</strong> Credential ID + Credential Secret (Associates Central)', 'affiliatetheme-amazon' ); ?></li>
					<li><?php _e( '<strong>Gleich geblieben:</strong> Partner Tag, Marketplace, alle Plugin-Einstellungen, alle bereits importierten Produkte.', 'affiliatetheme-amazon' ); ?></li>
				</ul>
			</div>
		</div>

		<!-- Sektion: Voraussetzungen -->
		<div class="postbox" id="mig-requirements">
			<h3 class="hndle" style="padding:12px 14px;"><span><?php _e( 'Voraussetzungen', 'affiliatetheme-amazon' ); ?></span></h3>
			<div class="inside">
				<p><?php _e( 'Bevor du loslegst, stelle sicher, dass die folgenden Punkte erfüllt sind:', 'affiliatetheme-amazon' ); ?></p>
				<ul class="mig-checklist">
					<li><input type="checkbox"> <?php _e( '<strong>Aktives Amazon-Partnerprogramm-Konto</strong> (Associates / PartnerNet). Dein Konto muss freigeschaltet und nicht gesperrt sein. Wenn dein Konto aufgrund fehlender Umsätze geschlossen wurde, reaktiviere es zuerst über den Amazon-Support.', 'affiliatetheme-amazon' ); ?></li>
					<li><input type="checkbox"> <?php _e( '<strong>PHP 8.2 oder höher</strong> auf dem Webserver. Die neue API-Library benötigt moderne PHP-Features, die erst ab Version 8.2 verfügbar sind. Wenn du nicht weißt, welche PHP-Version dein Server nutzt: frage kurz beim Hoster nach oder sieh in deinem Hosting-Panel unter "PHP-Version" nach. Die meisten Hoster bieten PHP 8.2+ mit wenigen Klicks an.', 'affiliatetheme-amazon' ); ?></li>
					<li><input type="checkbox"> <?php _e( '<strong>Plugin-Version 2.0.0 oder neuer</strong>. Öffne dazu in WordPress das Menü "Plugins" und prüfe bei "AffiliateTheme - Amazon Schnittstelle" die angezeigte Version. Falls du noch auf einer 1.x-Version bist, führe zuerst das Plugin-Update durch.', 'affiliatetheme-amazon' ); ?></li>
					<li><input type="checkbox"> <?php _e( '<strong>Backup deiner WordPress-Installation</strong>. Die Migration ändert lediglich ein paar Einstellungen — kritische Daten werden nicht angefasst — aber ein aktuelles Backup schadet nie.', 'affiliatetheme-amazon' ); ?></li>
				</ul>
				<p><?php _e( 'Sobald alle vier Punkte erfüllt sind, kannst du mit Schritt 1 beginnen.', 'affiliatetheme-amazon' ); ?></p>
			</div>
		</div>

		<!-- Sektion: Schritt 1 -->
		<div class="postbox" id="mig-step1">
			<h3 class="hndle" style="padding:12px 14px;"><span><?php _e( 'Schritt 1: Credentials in Associates Central erstellen', 'affiliatetheme-amazon' ); ?></span></h3>
			<div class="inside">
				<div class="mig-callout mig-callout-danger">
					<p><strong><?php _e( 'Wichtig — vor dem Start:', 'affiliatetheme-amazon' ); ?></strong></p>
					<p><?php _e( 'Lege jetzt ein Backup deiner WordPress-Datenbank an. Während der Migration werden nur die Zugangsdaten ausgetauscht, bestehende Produkte und Inhalte bleiben unberührt. Dennoch ist ein aktuelles Backup bei jedem Plugin-Update eine sinnvolle Vorsichtsmaßnahme.', 'affiliatetheme-amazon' ); ?></p>
				</div>
				<p><?php _e( 'Die neuen Zugangsdaten erstellst du direkt im Amazon-Partnerprogramm. Jeder Marketplace hat seine eigene Oberfläche — nutze die Seite, die deinem Affiliate-Konto entspricht.', 'affiliatetheme-amazon' ); ?></p>

				<h4><?php _e( '1.1 Bei Associates Central anmelden', 'affiliatetheme-amazon' ); ?></h4>
				<ul class="mig-checklist">
					<li><input type="checkbox"> <?php _e( 'Öffne die passende Associates-Seite für deinen Marketplace:', 'affiliatetheme-amazon' ); ?></li>
				</ul>
				<table class="mig-table">
					<thead>
						<tr>
							<th><?php _e( 'Marketplace', 'affiliatetheme-amazon' ); ?></th>
							<th><?php _e( 'URL', 'affiliatetheme-amazon' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr><td><?php _e( 'Deutschland', 'affiliatetheme-amazon' ); ?></td><td><a href="https://partnernet.amazon.de" target="_blank" rel="noopener">partnernet.amazon.de</a></td></tr>
						<tr><td><?php _e( 'USA', 'affiliatetheme-amazon' ); ?></td><td><a href="https://affiliate-program.amazon.com" target="_blank" rel="noopener">affiliate-program.amazon.com</a></td></tr>
						<tr><td><?php _e( 'Vereinigtes Königreich', 'affiliatetheme-amazon' ); ?></td><td><a href="https://affiliate-program.amazon.co.uk" target="_blank" rel="noopener">affiliate-program.amazon.co.uk</a></td></tr>
						<tr><td><?php _e( 'Frankreich', 'affiliatetheme-amazon' ); ?></td><td><a href="https://partenaires.amazon.fr" target="_blank" rel="noopener">partenaires.amazon.fr</a></td></tr>
						<tr><td><?php _e( 'Italien', 'affiliatetheme-amazon' ); ?></td><td><a href="https://programma-affiliazione.amazon.it" target="_blank" rel="noopener">programma-affiliazione.amazon.it</a></td></tr>
						<tr><td><?php _e( 'Spanien', 'affiliatetheme-amazon' ); ?></td><td><a href="https://afiliados.amazon.es" target="_blank" rel="noopener">afiliados.amazon.es</a></td></tr>
						<tr><td><?php _e( 'Kanada', 'affiliatetheme-amazon' ); ?></td><td><a href="https://associates.amazon.ca" target="_blank" rel="noopener">associates.amazon.ca</a></td></tr>
						<tr><td><?php _e( 'Japan', 'affiliatetheme-amazon' ); ?></td><td><a href="https://affiliate.amazon.co.jp" target="_blank" rel="noopener">affiliate.amazon.co.jp</a></td></tr>
						<tr><td><?php _e( 'Australien', 'affiliatetheme-amazon' ); ?></td><td><a href="https://affiliate-program.amazon.com.au" target="_blank" rel="noopener">affiliate-program.amazon.com.au</a></td></tr>
						<tr><td><?php _e( 'andere Länder', 'affiliatetheme-amazon' ); ?></td><td><?php _e( 'analog über die jeweilige Amazon-Länderseite', 'affiliatetheme-amazon' ); ?></td></tr>
					</tbody>
				</table>
				<ul class="mig-checklist">
					<li><input type="checkbox"> <?php _e( 'Melde dich mit dem Amazon-Konto an, das zu deinem Partnerprogramm gehört. Das ist in der Regel dasselbe Konto, das du auch für den bisherigen API-Zugang verwendet hast.', 'affiliatetheme-amazon' ); ?></li>
				</ul>

				<h4><?php _e( '1.2 Menü "Creators API" öffnen', 'affiliatetheme-amazon' ); ?></h4>
				<ul class="mig-checklist">
					<li><input type="checkbox"> <?php _e( 'Navigiere im oberen Menü zu <strong>Tools</strong> und dort zum Eintrag <strong>Creators API</strong> (oder "API Credentials" — Amazon überarbeitet die Navigation gelegentlich, der Menüpunkt kann leicht abweichen).', 'affiliatetheme-amazon' ); ?></li>
					<li><input type="checkbox"> <?php _e( 'Solltest du den Menüpunkt nicht finden: nutze die Suche oben rechts ("Creators API") oder ruf direkt die Hilfe-Seite deines Marketplaces auf. Amazon bietet dort eine aktuelle Schritt-für-Schritt-Anleitung zur Credential-Erstellung.', 'affiliatetheme-amazon' ); ?></li>
				</ul>

				<h4><?php _e( '1.3 Neues Credential-Set erzeugen', 'affiliatetheme-amazon' ); ?></h4>
				<ul class="mig-checklist">
					<li><input type="checkbox"> <?php _e( 'Klicke auf <strong>"Create new credential set"</strong> bzw. die deutsche Entsprechung ("Neues Credential-Set erstellen").', 'affiliatetheme-amazon' ); ?></li>
					<li><input type="checkbox"> <?php _e( 'Wähle den <strong>Marketplace / die Region</strong>, die zu deinem Partner Tag passt. Wenn dein Partner Tag auf <code>-21</code> endet, ist das in der Regel Deutschland; <code>-20</code> ist die USA; <code>-21</code> kann auch UK/FR/IT/ES sein — orientiere dich am Marketplace, auf dem du bisher API-Anfragen gestellt hast.', 'affiliatetheme-amazon' ); ?></li>
					<li><input type="checkbox"> <?php _e( 'Vergib optional einen Namen für das Credential-Set (z.B. "WordPress Plugin" oder den Namen deiner Webseite). Das dient nur der Übersicht in Associates Central.', 'affiliatetheme-amazon' ); ?></li>
					<li><input type="checkbox"> <?php _e( 'Bestätige die Erstellung.', 'affiliatetheme-amazon' ); ?></li>
				</ul>

				<h4><?php _e( '1.4 Credential ID und Credential Secret sichern', 'affiliatetheme-amazon' ); ?></h4>
				<ul class="mig-checklist">
					<li><input type="checkbox"> <?php _e( 'Amazon zeigt dir jetzt <strong>einmalig</strong> zwei Werte an:', 'affiliatetheme-amazon' ); ?>
						<ul style="list-style:disc;padding-left:24px;margin-top:6px;">
							<li><?php _e( '<strong>Credential ID</strong> (eine längere alphanumerische Zeichenkette)', 'affiliatetheme-amazon' ); ?></li>
							<li><?php _e( '<strong>Credential Secret</strong> (ebenfalls alphanumerisch, deutlich länger)', 'affiliatetheme-amazon' ); ?></li>
						</ul>
					</li>
					<li><input type="checkbox"> <?php _e( 'Kopiere <strong>beide</strong> Werte sofort in einen Passwort-Manager oder einen sicheren Notizspeicher. Das <strong>Credential Secret</strong> wird danach nie wieder angezeigt. Wenn du es verlierst, musst du das Credential-Set neu erstellen.', 'affiliatetheme-amazon' ); ?></li>
					<li><input type="checkbox"> <?php _e( 'Schließe das Fenster erst, wenn du beide Werte gesichert hast.', 'affiliatetheme-amazon' ); ?></li>
				</ul>

				<div class="mig-callout">
					<p><strong><?php _e( 'Hinweis zu den alten AWS-Keys', 'affiliatetheme-amazon' ); ?></strong></p>
					<p><?php _e( 'Die alten AWS Access Keys (Access Key ID + Secret Access Key) funktionieren mit der neuen Creators API <strong>nicht</strong>. Du kannst sie nicht umwandeln, konvertieren oder migrieren — du musst ein komplett neues Credential-Paar erstellen wie oben beschrieben. Der Marketplace, der Partner Tag und dein Amazon-Konto bleiben aber unverändert.', 'affiliatetheme-amazon' ); ?></p>
				</div>
			</div>
		</div>

		<!-- Sektion: Schritt 2 -->
		<div class="postbox" id="mig-step2">
			<h3 class="hndle" style="padding:12px 14px;"><span><?php _e( 'Schritt 2: Credentials im Plugin eintragen', 'affiliatetheme-amazon' ); ?></span></h3>
			<div class="inside">
				<p><?php _e( 'Jetzt übernimmst du die eben erzeugten Werte in WordPress.', 'affiliatetheme-amazon' ); ?></p>
				<ul class="mig-checklist">
					<li><input type="checkbox"> <?php _e( 'Melde dich als Administrator in deiner WordPress-Installation an.', 'affiliatetheme-amazon' ); ?></li>
					<li><input type="checkbox"> <?php _e( 'Öffne im linken Menü <strong>"Amazon"</strong> (unter "Import").', 'affiliatetheme-amazon' ); ?></li>
					<li><input type="checkbox"> <?php _e( 'Wechsle oben in den Tab <strong>"Einstellungen"</strong>.', 'affiliatetheme-amazon' ); ?></li>
					<li><input type="checkbox"> <?php _e( 'Trage die neuen Zugangsdaten in die entsprechenden Felder ein:', 'affiliatetheme-amazon' ); ?>
						<ul style="list-style:disc;padding-left:24px;margin-top:6px;">
							<li><?php _e( '<strong>Credential ID</strong> — die ID aus Schritt 1.4', 'affiliatetheme-amazon' ); ?></li>
							<li><?php _e( '<strong>Credential Secret</strong> — das Secret aus Schritt 1.4', 'affiliatetheme-amazon' ); ?></li>
						</ul>
					</li>
					<li><input type="checkbox"> <?php _e( 'Prüfe die weiteren Felder:', 'affiliatetheme-amazon' ); ?>
						<ul style="list-style:disc;padding-left:24px;margin-top:6px;">
							<li><?php _e( '<strong>Partner Tag</strong> — bleibt identisch zu vorher, z.B. <code>deintag-21</code>. Wenn du ihn änderst, werden alle Produkt-Links nach und nach auf den neuen Tag umgeschrieben.', 'affiliatetheme-amazon' ); ?></li>
							<li><?php _e( '<strong>Land</strong> — der Marketplace, den du auch beim Credential-Erstellen ausgewählt hast (z.B. "Deutschland").', 'affiliatetheme-amazon' ); ?></li>
						</ul>
					</li>
					<li><input type="checkbox"> <?php _e( 'Klicke unten auf <strong>"Speichern"</strong>.', 'affiliatetheme-amazon' ); ?></li>
					<li><input type="checkbox"> <?php _e( 'Warte einen Moment. Unten im Dashboard führt das Plugin einen Verbindungstest durch. Nach wenigen Sekunden sollte erscheinen:', 'affiliatetheme-amazon' ); ?>
						<div class="mig-callout mig-callout-info" style="margin:10px 0 0 0;"><p><em><?php _e( 'Verbindung erfolgreich hergestellt.', 'affiliatetheme-amazon' ); ?></em></p></div>
					</li>
				</ul>
				<p>
					<a href="#top#settings" class="button button-primary"><span class="dashicons dashicons-admin-generic" style="vertical-align:middle;"></span> <?php _e( 'Zum Einstellungen-Tab', 'affiliatetheme-amazon' ); ?></a>
				</p>
				<p><?php _e( 'Erscheint stattdessen eine Fehlermeldung, springe zum Abschnitt <a href="#mig-troubleshooting">Troubleshooting</a>.', 'affiliatetheme-amazon' ); ?></p>
			</div>
		</div>

		<!-- Sektion: Schritt 3 -->
		<div class="postbox" id="mig-step3">
			<h3 class="hndle" style="padding:12px 14px;"><span><?php _e( 'Schritt 3: Alte Credentials entfernen', 'affiliatetheme-amazon' ); ?></span></h3>
			<div class="inside">
				<p><?php _e( 'Solange das Plugin noch die alten AWS-Keys gespeichert hat, zeigt es ein gelbes Banner über den alten Feldern an. Diese Felder werden nicht mehr verwendet — du kannst sie jetzt gefahrlos leeren.', 'affiliatetheme-amazon' ); ?></p>
				<ul class="mig-checklist">
					<li><input type="checkbox"> <?php _e( 'Bleibe im Tab <strong>"Einstellungen"</strong>.', 'affiliatetheme-amazon' ); ?></li>
					<li><input type="checkbox"> <?php _e( 'Suche das gelbe Hinweisbanner mit dem Text:', 'affiliatetheme-amazon' ); ?>
						<div class="mig-callout" style="margin:10px 0 0 0;"><p><em><?php _e( 'Diese Felder sind veraltet und werden nicht mehr verwendet. Bitte migriere zu den neuen Credentials.', 'affiliatetheme-amazon' ); ?></em></p></div>
					</li>
					<li><input type="checkbox"> <?php _e( 'Unterhalb des Banners findest du die Felder <strong>"Access Key ID (veraltet)"</strong> und <strong>"Secret Access Key (veraltet)"</strong>.', 'affiliatetheme-amazon' ); ?></li>
					<li><input type="checkbox"> <?php _e( 'Leere beide Felder (markieren, löschen).', 'affiliatetheme-amazon' ); ?></li>
					<li><input type="checkbox"> <?php _e( 'Klicke auf <strong>"Speichern"</strong>.', 'affiliatetheme-amazon' ); ?></li>
					<li><input type="checkbox"> <?php _e( 'Das gelbe Banner und die alten Felder verschwinden. Ab jetzt nutzt das Plugin ausschließlich die neuen Creators-Credentials.', 'affiliatetheme-amazon' ); ?></li>
				</ul>
				<div class="mig-callout mig-callout-info">
					<p><strong><?php _e( 'Hinweis', 'affiliatetheme-amazon' ); ?></strong></p>
					<p><?php _e( 'Wenn du die alten Keys lieber behalten möchtest (z.B. weil du sie anderweitig nutzt): du kannst sie auch stehen lassen. Das Plugin ignoriert sie, sobald gültige Creators-Credentials hinterlegt sind. Aus Übersichtsgründen empfehlen wir aber das Leeren.', 'affiliatetheme-amazon' ); ?></p>
				</div>
			</div>
		</div>

		<!-- Sektion: Schritt 4 -->
		<div class="postbox" id="mig-step4">
			<h3 class="hndle" style="padding:12px 14px;"><span><?php _e( 'Schritt 4: Verbindung testen', 'affiliatetheme-amazon' ); ?></span></h3>
			<div class="inside">
				<p><?php _e( 'Zum Abschluss prüfst du, ob tatsächlich eine funktionierende Verbindung zur neuen API besteht — am einfachsten mit einer echten Produktsuche.', 'affiliatetheme-amazon' ); ?></p>
				<ul class="mig-checklist">
					<li><input type="checkbox"> <?php _e( 'Wechsle im Plugin in den Tab <strong>"Suche"</strong>.', 'affiliatetheme-amazon' ); ?></li>
					<li><input type="checkbox"> <?php _e( 'Gib ein einfaches Suchwort in das Feld "Suche nach Keyword(s)" ein, z.B. <code>Buch</code> oder <code>Kaffeemaschine</code>.', 'affiliatetheme-amazon' ); ?></li>
					<li><input type="checkbox"> <?php _e( 'Wähle eine Kategorie (z.B. "Alle Kategorien").', 'affiliatetheme-amazon' ); ?></li>
					<li><input type="checkbox"> <?php _e( 'Klicke auf <strong>"Suchen"</strong>.', 'affiliatetheme-amazon' ); ?></li>
					<li><input type="checkbox"> <?php _e( 'Nach wenigen Sekunden sollten Produkttreffer mit Vorschaubild, Titel und Preis erscheinen.', 'affiliatetheme-amazon' ); ?></li>
				</ul>
				<p>
					<a href="#top#search" class="button"><?php _e( 'Zum Suche-Tab', 'affiliatetheme-amazon' ); ?></a>
				</p>
				<p><?php _e( 'Wenn du Ergebnisse siehst: <strong>Glückwunsch, die Migration ist abgeschlossen.</strong> Deine bestehenden Produkte werden ab jetzt über die neue API aktualisiert, neue Imports laufen direkt über die Creators API.', 'affiliatetheme-amazon' ); ?></p>
				<p><?php _e( 'Wenn keine Ergebnisse erscheinen oder eine Fehlermeldung auftaucht: lies den nächsten Abschnitt.', 'affiliatetheme-amazon' ); ?></p>
			</div>
		</div>

		<!-- Sektion: Troubleshooting -->
		<div class="postbox" id="mig-troubleshooting">
			<h3 class="hndle" style="padding:12px 14px;"><span><?php _e( 'Troubleshooting', 'affiliatetheme-amazon' ); ?></span></h3>
			<div class="inside">
				<h4><?php _e( '"Verbindung konnte nicht hergestellt werden"', 'affiliatetheme-amazon' ); ?></h4>
				<p><?php _e( 'Die häufigste Ursache für diesen Fehler:', 'affiliatetheme-amazon' ); ?></p>
				<ul style="list-style:disc;padding-left:20px;">
					<li><?php _e( '<strong>Tippfehler in den Credentials.</strong> Trage Credential ID und Credential Secret erneut ein — am besten per Copy &amp; Paste aus dem Passwort-Manager, nicht abtippen. Achte darauf, keine Leerzeichen vor oder hinter den Werten zu haben.', 'affiliatetheme-amazon' ); ?></li>
					<li><?php _e( '<strong>Falscher Marketplace.</strong> Wenn du das Credential-Set für den deutschen Marketplace erstellt hast, muss im Plugin unter "Land" auch "Deutschland" ausgewählt sein. Ein USA-Credential funktioniert nicht für den DE-Marketplace und umgekehrt.', 'affiliatetheme-amazon' ); ?></li>
					<li><?php _e( '<strong>Noch nicht aktiviert.</strong> Neu erstellte Credentials brauchen bei Amazon manchmal <strong>bis zu 10 Minuten</strong>, bis sie serverseitig aktiv sind. Warte kurz und speichere die Einstellungen dann erneut.', 'affiliatetheme-amazon' ); ?></li>
					<li><?php _e( '<strong>Partner Tag passt nicht zum Marketplace.</strong> Prüfe, ob dein Partner Tag tatsächlich zu dem gewählten Marketplace gehört. Ein <code>-21</code>-Tag gehört z.B. zu mehreren europäischen Marketplaces, aber ein <code>-20</code>-Tag ist ausschließlich USA.', 'affiliatetheme-amazon' ); ?></li>
				</ul>

				<h4><?php _e( '"HTTP 429 — Too Many Requests"', 'affiliatetheme-amazon' ); ?></h4>
				<p><?php _e( 'Du hast in kurzer Zeit zu viele Anfragen an Amazon gestellt und bist in das Rate Limit gelaufen.', 'affiliatetheme-amazon' ); ?></p>
				<ul style="list-style:disc;padding-left:20px;">
					<li><?php _e( 'Warte <strong>mindestens eine Stunde</strong> und versuche es erneut.', 'affiliatetheme-amazon' ); ?></li>
					<li><?php _e( 'Importiere Produkte nicht im Sekundentakt — das Plugin verteilt Anfragen normalerweise selbst, aber manuelle Schnellsuchen können das Limit ausreizen.', 'affiliatetheme-amazon' ); ?></li>
					<li><?php _e( 'Wenn du wiederholt 429-Fehler siehst: reduziere das Aktualisierungsintervall unter "Einstellungen → Aktualisierungsintervall für Produkte" auf einen höheren Wert (z.B. 3 oder 4 Stunden).', 'affiliatetheme-amazon' ); ?></li>
				</ul>
				<p><a href="#top#apilog"><?php _e( 'API Log öffnen', 'affiliatetheme-amazon' ); ?></a></p>

				<h4><?php _e( '"InvalidArgumentException cn"', 'affiliatetheme-amazon' ); ?></h4>
				<p><?php _e( 'Du hast unter "Land" <strong>China</strong> (<code>cn</code>) ausgewählt. Der chinesische Marketplace wird von der neuen Creators API derzeit <strong>nicht unterstützt</strong>.', 'affiliatetheme-amazon' ); ?></p>
				<ul style="list-style:disc;padding-left:20px;">
					<li><?php _e( 'Wähle einen anderen Marketplace.', 'affiliatetheme-amazon' ); ?></li>
					<li><?php _e( 'Beobachte die Ankündigungen im Amazon-Partnerprogramm. Sollte China später unterstützt werden, aktualisieren wir das Plugin entsprechend.', 'affiliatetheme-amazon' ); ?></li>
				</ul>

				<h4><?php _e( 'Fatal Error "PHP 8.2" oder "syntax error, unexpected token"', 'affiliatetheme-amazon' ); ?></h4>
				<p><?php _e( 'Dein Server läuft noch auf einer PHP-Version unter 8.2. Die neue API-Library nutzt Sprachfeatures, die es erst ab 8.2 gibt, daher kann das Plugin nicht laden.', 'affiliatetheme-amazon' ); ?></p>
				<ul style="list-style:disc;padding-left:20px;">
					<li><?php _e( 'Logge dich in dein Hosting-Panel ein und wähle <strong>PHP 8.2</strong> oder höher für die betreffende Domain aus. Bei den meisten Hostern ist das ein einziger Klick.', 'affiliatetheme-amazon' ); ?></li>
					<li><?php _e( 'Kannst du die PHP-Version nicht selbst ändern: kontaktiere den Hoster-Support mit der Bitte, auf PHP 8.2+ umzustellen. Das ist eine Standardanfrage und meist innerhalb weniger Minuten erledigt.', 'affiliatetheme-amazon' ); ?></li>
					<li><?php _e( 'Wenn du zeitgleich viele Plugins/Themes einsetzt, die noch auf älteren PHP-Versionen aufbauen: teste vorher auf einer Staging-Umgebung.', 'affiliatetheme-amazon' ); ?></li>
				</ul>

				<h4><?php _e( 'Allgemeine Fehler oder Rückfragen', 'affiliatetheme-amazon' ); ?></h4>
				<ul style="list-style:disc;padding-left:20px;">
					<li><?php _e( 'Prüfe das Tab <strong>"API Log"</strong> im Plugin. Dort werden die letzten 200 Einträge protokolliert — inklusive Fehlermeldungen von Amazon.', 'affiliatetheme-amazon' ); ?> <a href="#top#apilog"><?php _e( 'API Log öffnen', 'affiliatetheme-amazon' ); ?></a></li>
					<li><?php _e( 'Stelle sicher, dass dein Server ausgehende HTTPS-Verbindungen zu <code>api.amazon.com</code> zulässt. Einige strenge Firewall-Setups blockieren neue Endpunkte.', 'affiliatetheme-amazon' ); ?></li>
					<li><?php _e( 'Wenn nichts davon hilft: melde dich im <strong>AffiliateTheme-Forum</strong> oder über den offiziellen Support-Kanal. Halte folgende Angaben bereit:', 'affiliatetheme-amazon' ); ?>
						<ul style="list-style:circle;padding-left:24px;margin-top:6px;">
							<li><?php _e( 'Plugin-Version', 'affiliatetheme-amazon' ); ?></li>
							<li><?php _e( 'PHP-Version', 'affiliatetheme-amazon' ); ?></li>
							<li><?php _e( 'Ausgewählter Marketplace', 'affiliatetheme-amazon' ); ?></li>
							<li><?php _e( 'Fehlermeldung aus dem API Log (die letzten 5 Einträge)', 'affiliatetheme-amazon' ); ?></li>
							<li><?php _e( 'Zeitpunkt der Credential-Erstellung', 'affiliatetheme-amazon' ); ?></li>
						</ul>
					</li>
				</ul>
			</div>
		</div>

		<!-- Sektion: FAQ -->
		<div class="postbox" id="mig-faq">
			<h3 class="hndle" style="padding:12px 14px;"><span><?php _e( 'FAQ', 'affiliatetheme-amazon' ); ?></span></h3>
			<div class="inside">
				<h4><?php _e( 'Müssen meine bestehenden Produkte neu importiert werden?', 'affiliatetheme-amazon' ); ?></h4>
				<p><?php _e( '<strong>Nein.</strong> Alle bereits importierten Produkte bleiben unverändert in der Datenbank. Lediglich die <strong>Preis- und Verfügbarkeits-Updates</strong> sowie <strong>neue Imports</strong> laufen künftig über die Creators API. Du musst also nichts neu anlegen — das Plugin nutzt für laufende Produkte ab der Migration automatisch die neuen Credentials.', 'affiliatetheme-amazon' ); ?></p>

				<h4><?php _e( 'Was passiert, wenn ich nicht migriere?', 'affiliatetheme-amazon' ); ?></h4>
				<p><?php _e( 'Ab dem <strong>30. April 2026</strong> schaltet Amazon die alte Product Advertising API (PAAPI 5) endgültig ab. Konkret:', 'affiliatetheme-amazon' ); ?></p>
				<ul style="list-style:disc;padding-left:20px;">
					<li><?php _e( '<strong>Keine neuen Produkte</strong> können über das Plugin importiert werden.', 'affiliatetheme-amazon' ); ?></li>
					<li><?php _e( '<strong>Keine Preis-Updates</strong> laufen mehr — deine Produkte bleiben mit ihren zuletzt gespeicherten Preisen im Shop. Das ist rechtlich riskant: veraltete Preise in der Preisangabe-Verordnung (PAngV / DSGVO) können abgemahnt werden.', 'affiliatetheme-amazon' ); ?></li>
					<li><?php _e( '<strong>Bestehende Produkte</strong> bleiben erhalten, inklusive aller Bilder, Beschreibungen und Links. Sie altern lediglich auf dem Preisstand vom 30.04.2026 ein.', 'affiliatetheme-amazon' ); ?></li>
					<li><?php _e( '<strong>Feeds</strong> und <strong>ASIN-Grabber</strong> funktionieren nicht mehr, da sie ebenfalls API-Zugriffe benötigen.', 'affiliatetheme-amazon' ); ?></li>
				</ul>
				<p><?php _e( 'Kurz: Ohne Migration bleibt dein Shop als Archiv bestehen, aber du kannst ihn nicht mehr pflegen oder erweitern.', 'affiliatetheme-amazon' ); ?></p>

				<h4><?php _e( 'Kostet die neue API extra?', 'affiliatetheme-amazon' ); ?></h4>
				<p><?php _e( '<strong>Nein.</strong> Die Creators API ist — genau wie PAAPI 5 — <strong>kostenlos</strong> für aktive Associates. Du zahlst nichts zusätzlich und brauchst auch kein separates AWS-Konto mehr. Voraussetzung bleibt ein aktives Partnerkonto mit regelmäßigen Umsätzen (Amazon behält sich vor, inaktive Konten zu sperren; das ist aber unabhängig von der API-Umstellung).', 'affiliatetheme-amazon' ); ?></p>

				<h4><?php _e( 'Kann ich beide APIs parallel betreiben?', 'affiliatetheme-amazon' ); ?></h4>
				<p><?php _e( 'Bis zum 30.04.2026 ja — technisch laufen beide APIs nebeneinander, auch wenn das Plugin intern immer nur eine davon verwendet (bevorzugt die neue, sobald Credentials hinterlegt sind). Nach dem 30.04.2026 schaltet Amazon die alte API ab, dann bleibt nur noch die Creators API.', 'affiliatetheme-amazon' ); ?></p>

				<h4><?php _e( 'Muss ich den Partner Tag neu erstellen?', 'affiliatetheme-amazon' ); ?></h4>
				<p><?php _e( '<strong>Nein.</strong> Der Partner Tag (z.B. <code>deintag-21</code>) ist unabhängig von den API-Credentials. Du behältst ihn unverändert — sonst müssten alle bestehenden Produkt-Links umgeschrieben werden.', 'affiliatetheme-amazon' ); ?></p>

				<h4><?php _e( 'Funktioniert die Migration auch auf Multisite-Installationen?', 'affiliatetheme-amazon' ); ?></h4>
				<p><?php _e( '<strong>Ja.</strong> Die Migration läuft pro Site. Wenn du mehrere WordPress-Sites mit dem Plugin betreibst, führe die Schritte für jede Site einmal durch. Du kannst dabei <strong>dasselbe Credential-Set</strong> für mehrere Sites verwenden — Amazon koppelt die Credentials an dein Associates-Konto, nicht an eine spezifische Domain.', 'affiliatetheme-amazon' ); ?></p>

				<h4><?php _e( 'Mein Credential Secret ist weg — was nun?', 'affiliatetheme-amazon' ); ?></h4>
				<p><?php _e( 'Das Secret wird nur einmal angezeigt. Wenn du es verloren hast:', 'affiliatetheme-amazon' ); ?></p>
				<ol style="padding-left:20px;">
					<li><?php _e( 'Gehe in Associates Central zurück zu "Tools → Creators API".', 'affiliatetheme-amazon' ); ?></li>
					<li><?php _e( 'Lösche das alte Credential-Set (oder lasse es deaktiviert liegen).', 'affiliatetheme-amazon' ); ?></li>
					<li><?php _e( 'Erstelle ein neues Credential-Set — diesmal Secret direkt sichern.', 'affiliatetheme-amazon' ); ?></li>
					<li><?php _e( 'Trage das neue Paar im Plugin ein.', 'affiliatetheme-amazon' ); ?></li>
				</ol>

				<h4><?php _e( 'Wo finde ich Updates zum Status der Umstellung?', 'affiliatetheme-amazon' ); ?></h4>
				<ul style="list-style:disc;padding-left:20px;">
					<li><?php _e( 'Offizielle Amazon-Ankündigungen findest du im Associates-Newsletter und im Developer-Portal deiner Marketplace-Region.', 'affiliatetheme-amazon' ); ?></li>
					<li><?php _e( 'AffiliateTheme informiert über neue Plugin-Versionen im WordPress-Dashboard unter "Plugins".', 'affiliatetheme-amazon' ); ?></li>
				</ul>
			</div>
		</div>

		<!-- Sektion: Für Entwickler -->
		<div class="postbox" id="mig-dev">
			<h3 class="hndle" style="padding:12px 14px;"><span><?php _e( 'Für Entwickler', 'affiliatetheme-amazon' ); ?></span></h3>
			<div class="inside">
				<p><?php _e( 'Dieser Abschnitt ist optional und richtet sich an Entwickler, die das Plugin erweitern oder in eigene Workflows einbinden.', 'affiliatetheme-amazon' ); ?></p>

				<h4><?php _e( 'Interne API-Klasse', 'affiliatetheme-amazon' ); ?></h4>
				<p><?php _e( 'Der zentrale Einstiegspunkt für API-Aufrufe aus eigenem Code:', 'affiliatetheme-amazon' ); ?></p>
<pre class="at-code"><code>use Endcore\AmazonApi;

$api = AmazonApi::fromWpOptions();
$results = $api-&gt;search('Buch');</code></pre>
				<p><?php _e( '<code>AmazonApi::fromWpOptions()</code> liest die Credentials direkt aus den WordPress-Optionen (<code>amazon_credential_id</code>, <code>amazon_credential_secret</code>, <code>amazon_partner_id</code>, <code>amazon_country</code>) und liefert eine konfigurierte Instanz zurück.', 'affiliatetheme-amazon' ); ?></p>

				<h4><?php _e( 'Library', 'affiliatetheme-amazon' ); ?></h4>
				<ul style="list-style:disc;padding-left:20px;">
					<li><?php _e( '<strong>Paket:</strong> <code>Jakiboy/apaapi</code> v2.x', 'affiliatetheme-amazon' ); ?></li>
					<li><?php _e( '<strong>Einbindung:</strong> vendored in <code>lib/apaapi/</code> (nicht über Composer auto-loaded vom Host-Projekt, sondern über den plugin-eigenen Bootstrap in <code>lib/bootstrap.php</code>).', 'affiliatetheme-amazon' ); ?></li>
					<li><?php _e( '<strong>Repository:</strong>', 'affiliatetheme-amazon' ); ?> <a href="https://github.com/Jakiboy/apaapi" target="_blank" rel="noopener">github.com/Jakiboy/apaapi</a></li>
				</ul>

				<h4><?php _e( 'Breaking Changes gegenüber Plugin 1.x', 'affiliatetheme-amazon' ); ?></h4>
				<ul style="list-style:disc;padding-left:20px;">
					<li><?php _e( 'Die <strong>Request-Objekte der alten PAAPI 5</strong> (z.B. <code>SearchItemsRequest</code>, <code>GetItemsRequest</code> aus dem alten <code>paapi5-php-sdk</code>) werden nicht mehr unterstützt. Wenn du eigene Filter oder Hooks gebaut hast, die direkt PAAPI-5-Request-Objekte manipuliert haben: diese müssen auf die neue Library umgestellt werden.', 'affiliatetheme-amazon' ); ?></li>
					<li><?php _e( 'Die Konstanten <code>AWS_API_KEY</code> und <code>AWS_API_SECRET_KEY</code> existieren aus Kompatibilitätsgründen weiterhin, sind aber nach erfolgreicher Migration leer. Neuer Code sollte stattdessen <code>AWS_CREDENTIAL_ID</code> und <code>AWS_CREDENTIAL_SECRET</code> verwenden.', 'affiliatetheme-amazon' ); ?></li>
					<li><?php _e( 'Der Cron-Hash (<code>AWS_CRON_HASH</code>) basiert nun bevorzugt auf den neuen Credentials und fällt nur auf die alten Keys zurück, wenn noch keine Creators-Credentials gesetzt sind. So bleiben bestehende <code>wp_cron</code>-Einträge beim Migrations-Wechsel gültig.', 'affiliatetheme-amazon' ); ?></li>
				</ul>

				<h4><?php _e( 'Minimales PHP-Beispiel', 'affiliatetheme-amazon' ); ?></h4>
<pre class="at-code"><code>if ( ! class_exists( 'Endcore\AmazonApi' ) ) {
    return;
}

$api = \Endcore\AmazonApi::fromWpOptions();

try {
    $item = $api-&gt;lookup( 'B08N5WRWNW' ); // Beispiel-ASIN
    var_dump( $item-&gt;getTitle(), $item-&gt;getPrice() );
} catch ( \Throwable $e ) {
    error_log( 'Amazon API Fehler: ' . $e-&gt;getMessage() );
}</code></pre>
			</div>
		</div>

		<p style="text-align:right;color:#8c8f94;font-style:italic;margin-top:10px;">
			<?php _e( 'Stand: April 2026 — Version 2.0 der AffiliateTheme Amazon Schnittstelle.', 'affiliatetheme-amazon' ); ?>
		</p>

	</div>
</div>
