#!/usr/bin/env python3
"""Render public EN/DE documents from docs/content.json; no network or publication."""
from pathlib import Path
import json,re
root=Path(__file__).resolve().parents[1];source=json.loads((root/'docs/content.json').read_text());meta=source['metadata'];repo=meta['repository'];version=meta['version']
words={
'en':{'about':'Display Shopware products in your WordPress content using native Gutenberg blocks and Bricks elements. Shopware supplies the product data and prices.','glance':'At a glance','install':'Installation and first steps','features':'Main features','faq':'FAQ','screens':'Settings','history':'Changelog','author':'About the project','support':'Support and security','terms':'Copyright and licenses'},
'de':{'about':'Shopware-Produkte mit nativen Gutenberg-Blöcken und Bricks-Elementen in WordPress-Inhalten anzeigen. Shopware liefert Produktdaten und Preise.','glance':'Auf einen Blick','install':'Installation und erste Schritte','features':'Hauptfunktionen','faq':'FAQ','screens':'Einstellungen','history':'Änderungsverlauf','author':'Über das Projekt','support':'Hilfe und Sicherheit','terms':'Copyright und Lizenzen'}}
glance={'en':['Products and exact variants, gallery, descriptions, data sheets and manufacturer.','Shopware prices, reference/list prices, availability and direct product links.','Article-related products, dynamic groups, standalone buttons and optional Quick View.','Shared display controls for Gutenberg and Bricks; optional Bricks Components.','Site-scoped connection, diagnostics and 15/30/60-minute cache.'], 'de':['Produkte und konkrete Varianten, Galerie, Beschreibungen, Datenblätter und Hersteller.','Shopware-Preise, Grund-/Streichpreise, Verfügbarkeit und direkte Produktlinks.','Passende Artikelprodukte, dynamische Gruppen, Produktbuttons und optionale Schnellansicht.','Gemeinsame Darstellung in Gutenberg und Bricks; optionale Bricks Components.','Websitebezogene Verbindung, Diagnose und Cache für 15/30/60 Minuten.']}
features={'en':[('Products in content','Search by name or product number. Select a family or an exact size/color variant. Choose the fields, image layout, gallery, detail tabs and shop button.'),('Shopware keeps the rules and prices','Dynamic grids use active Shopware categories backed by product groups. Product data, availability, prices and rule evaluation remain in Shopware. There is no WordPress cart or checkout.'),('Clear setup and safe updates','Configure one shop per site, keep the key server-side and test the connection. Cache and outage handling protect both installations. Native Bricks elements work without installing optional Components.')], 'de':[('Produkte im Inhalt','Nach Name oder Artikelnummer suchen. Eine Familie oder eine konkrete Gebinde-/Farbvariante auswählen. Felder, Bildanordnung, Galerie, Detailtabs und Shopbutton bestimmen.'),('Regeln und Preise bleiben in Shopware','Dynamische Raster verwenden aktive Shopware-Kategorien mit Produktgruppen. Produktdaten, Verfügbarkeit, Preise und Regelauswertung bleiben in Shopware. Es gibt keinen WordPress-Warenkorb oder Checkout.'),('Klare Einrichtung und sichere Updates','Pro Website einen Shop konfigurieren, Schlüssel serverseitig halten und Verbindung testen. Cache und Ausfallbehandlung schützen beide Installationen. Native Bricks-Elemente funktionieren ohne die optionalen Components.')]}
for locale in ['en','de']:
 de=locale=='de';w=words[locale];guide='Deutsch' if de else 'English';faqName='FAQ-Deutsch' if de else 'FAQ-English';historyName='Changelog-Deutsch' if de else 'Changelog-English';other='English' if de else 'Deutsch';short=[];full=[];md=[]
 order=['Neu','Verbessert','Behoben','Sonstiges'] if de else ['New','Improved','Fixed','Misc']
 for release in source['history']:
  cats=[order.index(x['category']) for x in release['entries'][locale]];assert cats==sorted(cats)
  title=release['version']+' · '+release['date'];text='= '+title+' =\n'+'\n'.join('* '+x['category']+': '+x['text'] for x in release['entries'][locale]);full.append(text)
  md.append('## '+title+'\n\n'+'\n'.join('- **'+x['category']+':** '+x['text'] for x in release['entries'][locale]))
 # Up to seven releases; fewer exist at the start of a product's public history.
 short=full[:7];fullHistory='\n\n'.join(full)+'\n';shortHistory='\n\n'.join(short)+'\n'
 (root/('CHANGELOG.de.txt' if de else 'CHANGELOG.txt')).write_text(fullHistory)
 (root/'docs'/('changelog-de.txt' if de else 'changelog.txt')).write_text('== Changelog ==\n\n'+shortHistory)
 (root/'docs/wiki'/(historyName+'.md')).write_text('# '+w['history']+'\n\n['+other+']('+('Changelog-English' if de else 'Changelog-Deutsch')+'.md) · ['+('Anleitung' if de else 'Guide')+']('+guide+'.md)\n\n'+'\n\n'.join(md)+'\n')
 faq=source['readme_faq'][locale];assert len(faq)<=7
 introduction=('1. Das Plugin-ZIP aus dem [aktuellen Release]('+repo+'/releases/latest) herunterladen und in WordPress unter Plugins → Installieren → Plugin hochladen installieren.\n2. Unter Einstellungen → Connect for Shopware die öffentliche HTTPS-Shop-Adresse speichern.\n3. `DW_SW_ACCESS_KEY` in wp-config.php oder der PHP-Umgebung bereitstellen und die Verbindung testen.' if de else '1. Download the plugin ZIP from the [latest release]('+repo+'/releases/latest) and upload it under Plugins → Add New → Upload Plugin.\n2. Save the public HTTPS shop URL under Settings → Connect for Shopware.\n3. Provide `DW_SW_ACCESS_KEY` through wp-config.php or the PHP environment and test the connection.')
 requirements=('**Version:** '+version+' · WordPress ≥ '+meta['wp']+' · PHP ≥ '+meta['php']+' · Bricks Components ≥ '+meta['bricks']+' · Shopware '+meta['shopware']+' Store API.')
 banner='banner-github-de-1280x640.png' if de else 'banner-github-1280x640.png'
 parts=['# '+meta['name'],'![Connect for Shopware](assets-github/'+banner+')',w['about'],requirements,'['+other+']('+('README.md' if de else 'README-de.md')+') · ['+('Anleitung' if de else 'Guide')+']('+repo+'/wiki/'+guide+') · ['+('Fragen nach Themen' if de else 'FAQ by topic')+']('+repo+'/wiki/'+faqName+')', ' · '.join('['+w[k]+'](#'+k+')' for k in ['glance','install','features','faq','screens','history','author','support'])]
 def section(key,text):parts.extend(['<a id="'+key+'"></a>','## '+w[key],text])
 section('glance','\n'.join('- '+x for x in glance[locale]))
 section('install',introduction)
 section('features','\n\n'.join('### '+title+'\n\n'+text for title,text in features[locale]))
 section('faq','\n\n'.join('**'+x['q']+'**\n\n'+x['a'] for x in faq)+'\n\n['+('Alle Fragen nach Themen' if de else 'Complete FAQ by topic')+']('+repo+'/wiki/'+faqName+').')
 section('screens','![Connect for Shopware '+w['screens']+'](assets/screenshots/settings-'+locale+'.png)')
 section('history','\n\n'.join(x.replace('## ','### ',1) for x in md[:7]))
 section('author',('Connect for Shopware wird von [David Decker – DECKERWEB](https://github.com/deckerweb) entwickelt und herausgegeben. Es gehört zur Connect-Serie und verbindet bestehende Systeme, ohne deren Produktpflege zu duplizieren.' if de else 'Connect for Shopware is developed and published by [David Decker – DECKERWEB](https://github.com/deckerweb). It is part of the Connect series and links existing systems without duplicating product maintenance.'))
 section('support','[Issues]('+repo+'/issues) · [Discussions]('+repo+'/discussions) · ['+('Private Sicherheitsmeldung' if de else 'Private security reporting')+']('+repo+'/security/advisories/new)\n\n['+('Das Projekt unterstützen' if de else 'Support the project')+']: [Ko-fi](https://ko-fi.com/deckerweb) · [Buy Me a Coffee](https://buymeacoffee.com/daveshine) · [PayPal](https://paypal.me/deckerweb)')
 # Correct the intentionally plain support lead-in; only URLs form Markdown links.
 parts[-1]=parts[-1].replace('['+('Das Projekt unterstützen' if de else 'Support the project')+']: ',('Das Projekt unterstützen' if de else 'Support the project')+': ')
 notice=source['notices']['shopware_trademark'][locale]
 parts.extend(['<a id="trademark"></a>', '## '+notice['title'], '\n\n'.join(notice['paragraphs'])])
 (root/'docs'/('TRADEMARK-de.md' if de else 'TRADEMARK.md')).write_text('# '+notice['title']+'\n\n'+'\n\n'.join(notice['paragraphs'])+'\n')
 parts+=['© 2026 David Decker – DECKERWEB · GPL v2 or later · SPDX GPL-2.0-or-later.','['+('Herkunft und Lizenzen' if de else 'Credits and licenses')+'](docs/'+('CREDITS-de.md' if de else 'CREDITS.md')+').']
 (root/('README-de.md' if de else 'README.md')).write_text('\n\n'.join(parts)+'\n')
 txt=['=== '+meta['name']+' ===','Contributors: daveshine','Tags: shopware, gutenberg, bricks, products','Requires at least: '+meta['wp'],'Tested up to: 7.1.2','Requires PHP: '+meta['php'],'Stable tag: '+version,'License: GPLv2 or later','License URI: https://www.gnu.org/licenses/gpl-2.0.html','',w['about'],'','== Description ==','', '\n'.join('- '+x for x in glance[locale]),'',requirements,'','== Installation ==','',re.sub(r'\[([^]]+)\]\(([^)]+)\)',r'\1: \2',introduction).replace('`',''),'','== Frequently Asked Questions ==','']
 for q in faq:txt+=['= '+q['q']+' =',q['a'],'']
 txt+= [('Vollständige Fragen nach Themen: ' if de else 'Complete FAQ by topic: ')+repo+'/wiki/'+faqName,'','== Screenshots ==','', '1. '+('Einstellungen: Verbindung, Cache, Diagnose und optionale Bricks Components.' if de else 'Settings: connection, cache, diagnostics and optional Bricks Components.'),'','== Changelog ==','',shortHistory,'',('== Herkunft und Lizenzen ==' if de else '== Credits and licenses =='),'',('© 2026 David Decker – DECKERWEB. Gemeinsamer deckerweb Updater und Plugin Library sowie aktive Grafiken: GPL-2.0-or-later.' if de else '© 2026 David Decker – DECKERWEB. Shared deckerweb Updater, Plugin Library and active artwork: GPL-2.0-or-later.'),'', '== Support ==','',repo+'/issues',repo+'/security/advisories/new','https://ko-fi.com/deckerweb','https://buymeacoffee.com/daveshine','https://paypal.me/deckerweb']
 txt+=['','== '+notice['title']+' ==','','\n\n'.join(notice['paragraphs'])]
 (root/('readme-de.txt' if de else 'readme.txt')).write_text('\n'.join(txt)+'\n')
 c=source['content'][locale]
 (root/'docs/wiki'/(guide+'.md')).write_text(c['guide'])
 (root/'docs/wiki'/(faqName+'.md')).write_text(c['faq'])
 credits=('# Herkunft und Lizenzen\n\n[English](CREDITS.md)\n\nConnect for Shopware, Signet und aktive Grafiken: © 2026 David Decker – DECKERWEB, GPL-2.0-or-later.\n\nDie unverändert eingebetteten gemeinsamen Komponenten deckerweb Updater 2.1.0 und Plugin Library 0.6.0 stammen von David Decker – DECKERWEB und stehen unter GPL-2.0-or-later. Ihr Copyright und die beiliegende Library-Lizenz bleiben erhalten. Der gemeinsame lokale Changelog-Renderer stammt aus den deckerweb-Plugin-Komponenten und steht unter derselben Lizenz.\n\nBricks und Shopware werden über ihre Schnittstellen eingebunden; deren Anwendungscode wird nicht mitgeliefert. Es werden keine Fontdateien ausgeliefert. Die Dokumentationswebsite verwendet das [Cayman-Theme](https://github.com/pages-themes/cayman) unter CC0-1.0; diese Website-Dateien sind nicht Teil des installierbaren Plugins.\n' if de else '# Credits and licenses\n\n[Deutsch](CREDITS-de.md)\n\nConnect for Shopware, its mark and active artwork: © 2026 David Decker – DECKERWEB, GPL-2.0-or-later.\n\nThe unchanged embedded deckerweb Updater 2.1.0 and Plugin Library 0.6.0 originate from David Decker – DECKERWEB and are GPL-2.0-or-later. Their copyright notices and included Library license are retained. The shared local changelog renderer comes from the deckerweb plugin components under the same license.\n\nBricks and Shopware are integrated through their APIs; their application code is not bundled. No font files are distributed. The documentation website uses the [Cayman theme](https://github.com/pages-themes/cayman) under CC0-1.0; website files are excluded from the installable plugin.\n')
 (root/'docs'/('CREDITS-de.md' if de else 'CREDITS.md')).write_text(credits)
(root/'docs/wiki/Home.md').write_text('''# Connect for Shopware

![Connect for Shopware](https://raw.githubusercontent.com/deckerweb/connect-for-shopware/main/assets-github/banner-github-1280x640.png)

**Your shop. Your content. Connected.** · **Dein Shop. Deine Inhalte. Verbunden.**

[English](English.md) · [Deutsch](Deutsch.md)

Products and variants in native Gutenberg blocks and Bricks elements. Shopware supplies the catalog, prices and dynamic-group rules. No duplicate product maintenance, WordPress cart or checkout.

Produkte und Varianten in nativen Gutenberg-Blöcken und Bricks-Elementen. Shopware liefert Katalog, Preise und die Regeln dynamischer Gruppen. Keine doppelte Produktpflege, kein WordPress-Warenkorb oder Checkout.

## English

- [Setup and guide](English.md): connection, product cards, grids and optional Components.
- [FAQ by topic](FAQ-English.md): everyday use, permissions, caching and Multisite.
- [Changelog](Changelog-English.md): published versions and relevant changes.

## Deutsch

- [Einrichtung und Anleitung](Deutsch.md): Verbindung, Produktkarten, Raster und optionale Components.
- [Fragen nach Themen](FAQ-Deutsch.md): Alltag, Berechtigungen, Cache und Multisite.
- [Änderungsverlauf](Changelog-Deutsch.md): Veröffentlichungen und relevante Änderungen.

[Downloads](https://github.com/deckerweb/connect-for-shopware/releases/latest) · [Issues](https://github.com/deckerweb/connect-for-shopware/issues) · [Private security reporting / Sicherheitsmeldung](https://github.com/deckerweb/connect-for-shopware/security/advisories/new)
''')
home=root/'docs/wiki/Home.md'
home.write_text(home.read_text()+'\n\n'+'\n\n'.join('## '+source['notices']['shopware_trademark'][locale]['title']+'\n\n'+'\n\n'.join(source['notices']['shopware_trademark'][locale]['paragraphs']) for locale in ['en','de'])+'\n')
(root/'docs/wiki/_Sidebar.md').write_text('''[Home](Home)

### English
- [Setup and guide](English)
- [FAQ by topic](FAQ-English)
- [Changelog](Changelog-English)

### Deutsch
- [Einrichtung und Anleitung](Deutsch)
- [Fragen nach Themen](FAQ-Deutsch)
- [Änderungsverlauf](Changelog-Deutsch)

### Project / Projekt
- [Downloads](https://github.com/deckerweb/connect-for-shopware/releases/latest)
- [Issues](https://github.com/deckerweb/connect-for-shopware/issues)
- [Discussions](https://github.com/deckerweb/connect-for-shopware/discussions)
- [Security / Sicherheit](https://github.com/deckerweb/connect-for-shopware/security/advisories/new)
''')
# The same rendered content supplies the optional Pages site; no extra copy of facts.
site=root/'docs' if (root/'docs/_config.yml').exists() else root/'docs/site';site.mkdir(exist_ok=True)
for locale in ['en','de']:
    de=locale=='de';readme=(root/('README-de.md' if de else 'README.md')).read_text()
    readme=readme.replace('(README.md)','(../)').replace('(README-de.md)','(de/)')
    prefix='../' if de else ''
    readme=readme.replace('(assets-github/','('+prefix+'assets/brand/').replace('(assets/screenshots/','('+prefix+'assets/screenshots/')
    readme=readme.replace('(docs/CREDITS.md)','('+repo+'/blob/main/docs/CREDITS.md)').replace('(docs/CREDITS-de.md)','('+repo+'/blob/main/docs/CREDITS-de.md)')
    out=site/'de/index.md' if de else site/'index.md';out.parent.mkdir(parents=True,exist_ok=True)
    out.write_text('---\nlayout: default\nlang: '+locale+'\n---\n'+readme)
    assets=site/'assets/screenshots';assets.mkdir(parents=True,exist_ok=True)
    import shutil
    picture=root/'assets/screenshots'/('settings-'+locale+'.png')
    if picture.exists():shutil.copy2(picture,assets/picture.name)
print('Generated synchronized Readmes, seven topical short FAQs, history, guides, credits and grouped Wiki navigation.')

(root/"docs/GLOSSARY.md").write_text("# Terminology / Begriffe\n\n| English | Deutsch (Du) | Deutsch (Sie) |\n| --- | --- | --- |\n" + "\n".join("| " + " | ".join(row) + " |" for row in source["glossary"]) + "\n")
