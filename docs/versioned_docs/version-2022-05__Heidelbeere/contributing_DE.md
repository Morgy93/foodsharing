# Einführung in Git und unser Arbeiten

English short version of this file: [Making and submitting changes](./contributing)

Was du hier findest:

- [Wie du unserem Projekt beitrittst](#wie-du-unserem-projekt-beitrittst)
- [Wie du einen Starter Task findest](#wie-du-einen-starter-task-findest)
- [Tutorials](#tutorials)
- [Auf welchen Ebenen Git funktioniert](#auf-welchen-ebenen-git-funktioniert)
- [Workflow](#workflow)
- [Was es zum Merge Request zu sagen gibt](#was-es-zum-merge-request-zu-sagen-gibt)
- [Wie du einen Rebase machst](#wie-du-einen-rebase-machst)
- [Wie du mit Rebase-Konflikten in unseren Abhängigkeiten umgehst](#wie-du-mit-rebase-konflikten-in-unseren-abhängigkeiten-umgehst)
- [Merge in den Master](#merge-in-den-master)
- [Ein Issue anlegen](#ein-issue-anlegen)
- [Testen](#testen)

## Wie du unserem Projekt beitrittst

In der foodsharing-IT organisieren wir unsere Arbeit über Slack (<https://slackin.yunity.org>).
Dafür musst du dir ein Konto bei Slack mit einer funktionierenden E-Mail-Adresse anlegen.
Im Kanal `#foodsharing-dev` (auf <https://slackin.yunity.org>) besprechen wir, was zu tun ist.
Sag da am besten auf Englisch Hallo und stell dich dem Team vor - mit deinen Skills und woran du gern arbeiten würdest.

Der gesamte Code liegt im Repository unter <https://gitlab.com/foodsharing-dev/foodsharing>.
Da gibt es neben der "Project ID: 1454647" die Möglichkeit, auf `Request Access` zu klicken.
Sag uns in [yunity Slack](https://slackin.yunity.org/) im Kanal `#foodsharing-dev` Bescheid und wir geben dir die Bearbeitungsrechte.

Als Mitglied auf GitLab kannst du:

- Branches innerhalb des Repository erstellen und verschieben (außer Master, mehr zu *Branches* unten)
- Vertrauliche Issues sehen
- Labels vergeben (supernützlich!)
- Dich Issues zuordnen (um anderen zu sagen, dass sie nicht damit anfangen müssen)

Zum Mitarbeiten brauchst du dann noch deine lokale [Programmierumgebung](./running-the-code).

## Wie du einen Starter Task findest

Mit welcher Aufgabe du anfangen könntest, hängt natürlich von deinen Vorkenntnissen ab.
Frag am besten in Slack nach, was ein guter Starter Task wäre.

Du kannst dir auch selbst etwas aussuchen unter <https://gitlab.com/foodsharing-dev/foodsharing/-/issues?label_name%5B%5D=Starter+task>

Ordne dich bitte dem Issue zu, das du bearbeitest. (Rechts oben im Issue: `assign yourself`)

## Tutorials

Tutorial-Empfehlungen für den Umgang mit Git:

- Für die Basics: <http://rogerdudler.github.io/git-guide/>
- Für Rebase etc.: <http://think-like-a-git.net>, <https://git-scm.com/doc> und <https://learngitbranching.js.org/>

## Auf welchen Ebenen Git funktioniert

**1. Ebene**: Deine Arbeitsumgebung.
Wenn du Sachen änderst, wird erstmal nichts hochgeladen.
Sobald du anfängst, Änderungen zu schreiben, empfiehlt es sich, einen *Branch* zu erstellen.
Wie bei einem Baum ist der Zweig etwas, was von dem Stamm abgeht.
Alle haben den Master (Baum), aber erstmal hast nur du deinen Zweig (Branch).
<https://confluence.atlassian.com/bitbucket/branching-a-repository-223217999.html>

**2. Ebene**: Du möchtest, dass auch andere deinen Branch sehen und darüber philosophieren können.
Dafür musst du deine Änderungen bestätigen, *to commit*.
Damit hebst du die Dateien einzeln auf die Upload-Ebene.
Sobald du anfängst, zu committen, lohnt es sich einen *Merge Request* zu erstellen.
Den Merge Request erstellst du im GitLab selbst: <https://gitlab.com/foodsharing-dev/foodsharing/-/merge_requests/new>
Der MR sagt aus: "Ich arbeite daran und werde in absehbarer Zeit fertig".
Solange es noch ein *Work in Progress* ist, solltest du den MR mit 'Draft: ' am Anfang benennen.
Sobald der MR fertig ist, benennst du ihn um, bittest im Kanal um ein review, und wenn jemand gesagt hat: Yo, läuft, dann kann der Merge Request gemerged werden, also mit dem Master zusammengeführt werden.
Dafür musst du in der Regel erst deinen Zweig lokal mit dem Master zusammenführen: *rebase*.
Wenn ein MR übernommen ist, hat dein Code die **3. Ebene** erreicht und alle, die sich das Repository ziehen, haben ihn ebenfalls im Master.

**3. Ebene** ist dann, wenn von der Test/Beta-Version in die produktive Version rübergeschaufelt wird.
Wenn du Probleme hast, frag am besten jemand im Kanal, ob er/sie dir helfen kann, die Git-Kommandos in der richtigen Reihenfolge auszuführen.

## Workflow

Ein Workflow kann so aussehen:
TL/DR: Branch erstellen -> ein oder mehrere commits machen -> pushen -> MR erstellen

```shell
git checkout master
git pull
git checkout -b 123-fix-for-issue-123
```

(Erstellt den Branch 123-fix-for-issue-123. Es ist gut, wenn du die Issue-Zahl vorne an deinen Branch setzt, damit wir sehen können, wozu er gehört. Du kannst gerne genauer beschreiben, worum es geht: `git checkout -b 812-1-make-devdocs-contributing-a-german-text`)

Anschließend an Dateien arbeiten.

`git add src/EditedFile.php`

(Fügt die bearbeitete Datei `src/EditedFile.php` zu denen hinzu, die hochgeladen werden sollen. Es ist empfehlenswert, `git add -p path_to_file` zu benutzen, damit du genau weißt, was du zum Commit hinzufügst.)

`git status`
ODER
`git diff --staged`

(Nützlich, um sich vor dem commit anzuschauen was gerade der Status ist oder was man gerade in seiner staging area hat und was genau zu dem aktuellen commit hinzugefügt wird)

`./scripts/fix-codestyle-local` (oder, wenn das nicht funktioniert, benutz das langsamere `./scripts/fix`)
Damit überprüfst du deinen [Code style](./codestyle).

`./scripts/test`
Damit lässt du lokal die Tests durchlaufen.
Für spätere Wiederholungstests nutze: `./scripts/test-rerun` (viel schneller!)

```shell
git commit -m "Changed something in EditedFile.php"
git push --set-upstream origin 123-fix-for-issue-123
```

(Bündelt die Änderungen mit der öffentlichen Notiz "..." und lädt sie hoch. Es kann sein, dass ein Kommando vorschlägt, das noch zu geben ist. Insbesondere bietet es sich hier an, einen MR für den Branch zu erstellen und ihn als Draft zu markieren.)

Anschließend weiterarbeiten

```shell
git add src/AnotherFile.php
git commit -m "Changed something in AnotherFile.php"
git push
```

// Jetzt funktioniert es //

Wenn du das Changelog bearbeitet hast:

```shell
git add CHANGELOG.md
git commit -m "Updated Changelog"
git push
```

(Nach `git push` kann man als Kommando `-o ci.skip` hinzufügen. Das überspringt Tests, was etwas Energie spart. Tests sind am Ende allerdings wichtig, wenn alle Dateien fertig sind.)

Wenn du fertig bist:

```shell
git checkout master
git pull
git checkout 123-fix-for-issue-123
git rebase master
// Bezogen darauf, wie alt dein Branch und andere Änderungen sind, wird dies wahlweise direkt funktionieren oder mehr Arbeit erfordern. Folge einfach den Anweisungen von git.

git push -f
// Ein "force push" wird deine Änderungen an der Quelle überschreiben, da der Rebase den Verlauf deines Branch verändert hat
```

## Was es zum Merge Request zu sagen gibt

Je übersichtlicher und besser beschrieben der Merge Request (MR) ist, desto einfach ist es für andere, ihn zu reviewen. Das heißt:

- Gib dem MR einen aussagekräftigen Namen

- Verwende bitte ein Template für die Beschreibung (`Default` aus der dropdown-Liste) und beschreibe, was die Änderung macht.

- Wenn es eine Änderung an der UI ist, füg gerne auch ein paar Screenshots ein.

- Geh einmal die Checklist (ganz unten im Template) durch und schau, ob du alle Punkte beachtet hast, bzw. Punkte nicht zutreffen.
  Beispielsweise ist nicht immer ein Test notwendig oder möglich.
  Die Ausnahme ist der letzte Punkt ("Once your MR has been merged...") der das Testen nach dem mergen betrifft (s.u.)

- Gib dem MR noch ein paar Label, die ihn einordnen. Insbesondere ein "state"-Label hilft zu sehen, ob der MR fertig ist.

Unten im MR kann dieser diskutiert werden. Anmerkungen am Code kannst du am besten unter "Changes" direkt an der entsprechenden Zeile einfügen.

## Wie du einen Rebase machst

1. Du holst dir mit `git checkout master` und `git pull` die aktuellen Änderungen vom master Branch.

2. Wechsele danach wieder mit `git checkout BRANCHNAME` in deinen branch.

3. `git rebase master` ist der Befehl deiner Wahl. (Den Unterschied zwischen rebase und merge findest du hier: <https://git-scm.com/book/en/v2/Git-Branching-Rebasing>)
   Falls du das Programm phpstorm nutzt, klickst du rechts unten auf deinen Branchnamen. Dadurch geht ein Menü auf, in dem du den Master auswählst und anklickst: "checkout and rebase onto current".

4. Sollte das rebasen zu kompliziert sein oder nicht funktionieren: **Fallen lassen wie eine heiße Kartoffel** ;-) mit dem Befehl `git rebase --abort`
   Führe stattdessen ein merge durch, da dies einfacher ist, da nur die Änderungen von master branch eingefügt werden. Hierzu kannst du den Befehl `git merge master` anwenden.
   In phpstorm gibt es zwei Menüpunkte unter dem obigen: "Merge into current". Du findest unten rechts eine Möglichkeit, dir die Versionsunterschiede anzeigen zu lassen.
   Mit dem Zauberstab-Knopf oben kannst du konfliktfreie Änderungen automatisch vornehmen lassen.
   "ours" und "theirs" entspricht den Pfeilen am Diff-Rand.
   (... Hier könnte jemand irgendwann Screenshots einfügen ...)

5. Danach kannst du mit einem `git commit` und `git push` die Änderungen hochladen, wenn du alle Konflikte bereinigt bekommst.

## Wie du mit Rebase-Konflikten in unseren Abhängigkeiten umgehst

Hast du in deinem Branch Änderungen an der `composer.json` und / oder `client/packages.json` durchgeführt und gleichzeitig hat jemand auch an diesen Dateien Änderungen in den master gemergt, kommt es in `composer.lock` und `yarn.lock` zu einem Konflikt.

1. Führe den Befehl `git checkout master -- chat/yarn.lock, client/yarn.lock or composer.lock` aus

2. Danach loggst du Dich mit `./scripts/docker-compose run --rm client sh` in den Docker-Container "Client" ein.

3. Führe darin den Befehl `yarn` aus und beende mit `exit`, wenn dieser fertig ist. (bzw. für composer wäre es `./scripts/composer install`)

4. Danach kannst du mit einem `git add chat/yarn.lock, client/yarn.lock or composer.lock` und `git rebase --continue` den Rebase fortsetzen.

(Auf der [Rebase-Seite](./rebase) gibt es auch ein Beispiel)

## Merge in den Master

Wenn du fertig bist, meldest du dich im Slack-Dev-Kanal und schreibst da einen Link zu deinem MR hin, mit der bitte um Rückmeldungen.
Andere Devs werden sich dann mit Feedback oder Änderungswünschen an Dich wenden.
Bitte habe etwas Geduld, wenn das nicht sofort passiert.

Sobald der/die Genehmigende deinen MR für fertig befindet, wird er ihn in den Master übernehmen.

Der Master-Zweig wird automatisch auf beta.foodsharing.de bereitgestellt, wo er getestet werden kann.
In der Arbeitsgruppe **Beta Testing** auf [foodsharing.de](https://foodsharing.de/) solltest du jetzt im Forum einen Beitrag für ein Testszenario erstellen, 
so dass es von anderen ausführlich getestet werden kann. Der Beitrag sollte das Format haben: 
- Titel: 
  - Test-Aufgabe: Problem in einem Satz
- Nachricht:
  - Was ist das Problem?
  - Wie kann man das Problem Schritt-für-Schritt reproduzieren?
  - Was ist der Soll-Zustand?
  - Deadline (Zeitpunkt, bis zu dem das Testen erledigt sein sollte)
  
Dort gibt es dann noch einmal gegebenenfalls Rückmeldungen zu Fehlermeldungen.
(Besser hier als auf foodsharing.de! :-) )

... für einen Überblick über verschiedene Umgebungen: [Environments on GitLab](https://gitlab.com/foodsharing-dev/foodsharing/environments)

Am Ende werden die beta-Änderungen auf die Produktiv-Seite übernommen - und dein MR ist abgehakt.

Und dann ... Zeit für eine neue Aufgabe ...?

**Wenn du eine Frage hast, erreichst du uns über [yunity Slack](https://slackin.yunity.org/): komm in den Kanal `#foodsharing-dev`.**

## Ein Issue anlegen

Wenn dir etwas an der foodsharing-Webseite aufgefallen ist: prüf bitte, ob das auf beta.foodsharing.de und www.foodsharing.de auftaucht.

- www und beta = es ist ein unbearbeitetes Verhalten = prüf bitte, ob das Issue bereits bei GitLab eingetragen ist: <https://gitlab.com/foodsharing-dev/foodsharing/issues> ... wenn es noch nicht existiert: **leg das issue bitte mit möglichst genauen Informationen an**

- www und nicht beta = wir haben uns schon drum gekümmert

- nicht www aber beta = wir haben es verursacht = **bitte im Slack-Beta-Kanal melden**

## Testen

Du kannst die Tests mit `./scripts/test` bzw. `./scripts/test-rerun` durchführen (siehe oben).
Solange wir die Tests so schreiben, dass sie idempotent ablaufen, nutz bitte *test-rerun*!

Bis jetzt funktionieren die Ende-zu-Ende-Tests (in der Codeception *acceptance test* genannt) gut.
Sie laufen mit einem kopflosen Firefox und Selenium innerhalb des Dockers und sie werden auch auf CI-Basis ausgeführt.

Wir sind dabei, den Code umzustrukturieren, um Unit-Tests zu ermöglichen: <https://gitlab.com/foodsharing-dev/foodsharing/issues/68>

Der während des Testens erzeugte Zustand wird nicht weggeworfen, und du kannst die Test-App nutzen:
Im Browser: <http://localhost:28080/>
und es hat seinen eigenen phpmyadmin: <http://localhost:28081/>

Wenn du die Tests mit eingeschaltetem Debug-Modus durchführen willst, verwende `./scripts/test --debug`.

Wenn du nur einen Test ausführen willst, gib den Pfad zu diesem Test als Argument an, z.B: `./scripts/test tests/acceptance/LoginCept.php`.

[Ausführlicheres zu Tests findest du hier.](./testing)
