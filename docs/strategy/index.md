# Strategie

Woraus besteht das (alte) CMS eigentlich?

- Navigations-Pflege
- ContentStream-Pflege
- User-Verwaltung
- Automatisches CRUD für Entities
- Entity-Versions

## Navigations-Pflege

Es ist zu klären, ob die Komponente in psc-cms sooo mega groß sein kann. Ich glaube es wird aus historischen Gründen zu viel zwischen mehreren Repräsentationen gewechselt.
Die JS Komponente muss von joose befreit werden.

=> Rewrite

## ContentStreams

Das komplizierteste ist hier die Un-Serializiation der ContentStream-Elemente. Diese ist aber auch besonders kompliziert wegen der Dependency Injection, die es in psc-cms noch nicht wirklich gab.

## Entity Versions

Das ist natürlich sehr komplex, und wir im Moment sehr abstrakt im Controller gelöst. Vielleicht können wir dies mehr Low-Level lösen, sodass der Code einfacher wird?

## User Verwaltung

Rewrite durch FOSUserBundle - no doubt.

## CRUD für Entities

Dies Konzept hat sich nicht ganz bewährt. Es wäre hilfreich einen Widget-Mapper im Frontend zu haben, um das "Formular erstellen" zu vereinfachen. Jedoch gibt es auch genug Custom-Properties, die unbedingt im Frontend in komplexe widgets umgesetzt werden. Es gibt sozusagen kein 1:1 Mapping von Properties<->Widgets. Man könnte jedoch einen guten "best of two worlds" approach machen. Sodass das CMS einen beim Erstellen der einfachen widgets (wie Texte, Integer, und Entity-Relation-Pickers) hilft, aber eben nicht alles automatisch macht.

=> Rewrite

# Wie updaten wir von 2.x auf 3.x?

Die Entities muss nach model.js umgewandelt werden. Dies hat für "nicht so komplexe" Entities, wie bei tiptoi sehr gut funktioniert. Da diese Entities sehr low-level waren. Während beim Psc-CMS die Entities vielleicht etwas zu komplex sind (i18n properties, etc?)

Die Controller können wir Stück für Stück in Symfony-Controller umwandeln (wie wir das gerade bei tiptoi tun). Dies ist aber im Zwischengang sehr kompliziert - aber nicht unmöglich.