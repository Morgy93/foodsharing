# Getting & running the code
*Unten gibt es eine deutsche Übersetzung.*

We use the version control system [Git](https://git-scm.com/) and the code is hosted on [GitLab](https://gitlab.com/foodsharing-dev/foodsharing). We are open source so you can just clone the repository and start exploring.

For Git, we recommend to use SSH (and the following documentation is supposing that you do so). See e.g. [this documentation](https://docs.gitlab.com/ce/ssh/README.html) if you need to configure GitLab for this.

For first use of Git search for tutorials, there are a lot good ones out there.

For exploring the project, check [Setting things up](./setting-things-up) afterwards as well and make sure that you followed the [Tools installation](./tools-installation) already. For contributing like writing issues and creating merge requests, check out the [Contributing section](./contributing), and join our mentioned Slack channel ([slackin.yunity.org](https://slackin.yunity.org) , channel #foodsharing-dev).

## Get the code

Get the source code:
```
git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing
```

## Start the containers

Switch into the source code directory and start the containers:
```
cd foodsharing
./scripts/start
```

After running the code for the first time, when visiting the local website, you might get an error like `Unable to write to the "/app/var/cache/dev" directory`. Stopping (`./scripts/stop`) and re-running (`./scripts/start`) the code should fix the problem. Otherwise, check the [Troubleshooting](./troubleshooting).

----

# Den Code ausführen

Wir verwenden das Versionskontrollsystem [Git](https://git-scm.com/) und der Code wird auf [GitLab](https://gitlab.com/foodsharing-dev/foodsharing) gehostet.
Wir sind Open Source, so dass Du das Repository einfach klonen und mit der Erkundung beginnen kannst.

Für Git empfehlen wir die Verwendung von SSH (das Secure Shell Netzwerkprotokoll, die folgende Dokumentation setzt das voraus). Wenn du GitLab dafür konfigurieren musst, hilft vermutlich [diese Dokumentation](https://docs.gitlab.com/ce/ssh/README.html) (en).

Für die erste Benutzung von Git such online nach einem Tutorial. Es gibt eine Menge gute Anleitungen.

Um das Projekt zu erkunden, schaue dir danach [Setting things up](./setting-things-up) an und stelle sicher, dass du die [Installation der nötigen Software](./tools-installation_DE) bereits abgeschlossen hast. Für Beiträge wie das Schreiben von issues und das Erstellen von Merge-Requests schau in den Abschnitt [Einführung in Git und unser Arbeiten](./contributing_DE) an, und komm in unseren Slack-Kanal ([slackin.yunity.org](https://slackin.yunity.org) , Kanal #foodsharing-dev).

## Den Code bekommen

Klone das Repository:
```
git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing
```

## Die Container starten

Wechsel in das gerade geklonte Verzeichnis und starte die Container:
```
cd foodsharing
./scripts/start
```

Wenn du den Code zum ersten Mal ausführst und die lokale Website öffnest, bekommst du möglicherweise einen Fehler wie `Unable to write to the "/app/var/cache/dev" directory`. Ein Stoppen (`./scripts/stop`) und Neustarten (`./scripts/start`) des Codes sollte das Problem beheben. Falls nicht, schau in das [Problemlösungskapitel](./troubleshooting).
