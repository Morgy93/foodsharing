# Installation der nötigen Software <!-- omit in toc -->

[Click here for the **English version**.](tools-installation.md)

Wir benutzen [Docker](https://www.docker.com/)-Container und [Docker Compose](https://docs.docker.com/compose/) für unsere Build- und Testumgebung.

Du kannst das "Docker Compose"-Setup benutzen, falls du mit einemder folgenden Systeme arbeitest:

- Linux (64bit)
- OSX Yosemite 10.10.3 oder neuer
- Windows 10 Pro oder neuer

In den folgenden Abschnitten ist die Installation für die verschiedenen Systeme beschrieben:

- [Linux](#linux)
- [OSX Yosemite 10.10.3 oder darüber](#osx-yosemite-10103-oder-darüber)
- [Windows](#windows)
  - [Windows Subsystem for Linux 2 (WSL 2)](#windows-subsystem-for-linux-2-wsl-2)
  - [Windows Subsystem for Linux 1 (WSL 1)](#windows-subsystem-for-linux-1-wsl-1)
  - [Bekannte Windows-Fehler](#bekannte-windows-fehler)
    - [Allgemein](#allgemein)
    - [Git-Fehler (bei WSL1)](#git-fehler-bei-wsl1)
    - [```[RuntimeException]```](#runtimeexception)
    - [yarn lint](#yarn-lint)
    - [Veränderungen in js, vue etc. erscheinen nicht](#veränderungen-in-js-vue-etc-erscheinen-nicht)

## Linux

In aktuellen Linux-Distributionen kannst du Docker wahrscheinlich einfach über den Paketmanager installieren. Stelle dabei nur sicher, dass Docker Compose mindestens in Version 1.6.0 verfügbar ist. Unter Debian oder Ubuntu sollte die Installation zum Beispiel über folgende Kommandozeile klappen:
```
sudo apt install docker.io docker-compose
```

Falls Docker oder Docker Compose nicht in einer aktuellen Version im Paketmanager verfügbar sind, folge bitte den Anleitungen für die Installation von 
[Docker CE](https://docs.docker.com/engine/installation/) und 
[Docker Compose](https://docs.docker.com/compose/install/).

Stelle sicher, dass dein Benutzer in der `docker`-Gruppe ist. Ansonsten wird es zu Rechtefehlern kommen, wenn du später versuchst einen Docker-Container zu starten:
```
sudo usermod -aG docker $USER
```
Dann logge dich entweder noch einmal neu ein oder lade die Gruppen neu oder führe (in jedem Terminal) aus: `su - $USER`

Jetzt solltest du dich ohne Fehler verbinden können. Teste das, indem du folgendes ausführst:
```
docker info
```

Du kannst dir jetzt den [Quellcode holen und ausführen](running-the-code.md).

## OSX Yosemite 10.10.3 oder darüber

Installiere [Docker for Mac](https://docs.docker.com/docker-for-mac/install/) ([direct link](https://download.docker.com/mac/stable/Docker.dmg)).

Danach kannst du dir den [Quellcode holen und ausführen](running-the-code.md).

## Windows 

Wir empfehlen, es unter wsl (Windows Subsystem for Linux) zu installieren. Auf den Ordner kann mit dieser Zeile zugegriffen werden:
'''
\\wsl$
'''

### Windows Subsystem for Linux 2 (WSL 2)

* Installiere Ubuntu über den [Microsoft Store](https://www.microsoft.com/de-de/store/apps/windows) und starte es
* Installiere das Windows Terminal über den Microsoft Store und starte es
* Öffne einen neuen Tab im Windows Terminal mit Ubuntu
* Füge folgendes in die Datei `~/.bashrc` mit einem Texteditor ein (z.B. mit nano)
```
export DOCKER_HOST=tcp://localhost:2375
export DOCKER_BUILDKIT=1
```
* Prüfe in den Docker-Einstellungen -> Resources -> WSL Integration, dass deine Umgebung aktiv ist.

### Windows Subsystem for Linux 1 (WSL 1)

Installiere [Docker for Windows](https://docs.docker.com/docker-for-windows/install/) ([Direktlink](https://download.docker.com/win/stable/Docker%20Desktop%20Installer.exe)) und
[Git for Windows](https://git-scm.com/download/win).

Wenn Du Windows 10 Home verwendest, stelle sicher, dass Du alle [Systemanforderungen](https://docs.docker.com/docker-for-windows/install-windows-home/#system-requirements) erfüllst.  Dann installiere sowohl [Docker Desktop on Windows Home](https://docs.docker.com/docker-for-windows/install-windows-home/) und [Git for Windows](https://git-scm.com/download/win). 

Es ist wichtig, Docker-Zugriff auf C: zu gewähren (in der grafischen Docker-Oberfläche: Einstellungen -> Ressourcen -> Filesharing -> C markieren, anwenden und neu starten).

Du kannst deinen in der Kommando-Shell (z.B. cmd oder powershell) mit dem Befehl ```docker --version``` testen. Wenn es etwas anzeigt, kannst du loslegen.

Starte jetzt dein Windows neu.

Es gibt eine grafische Benutzeroberfläche zur Verwaltung des Repos, die für Git-Anfänger empfohlen wird. Aber Du kannst die Git Bash-Shell genau wie unter Linux benutzen, um sie zu klonen:

```
git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing
```

Nach diesem Befehl befinden sich Deine Dateien im Ordner ````%UserProfile%\foodsharing```

Um die Container zu starten, verwende die Git Bash-Shell:
```
cd foodsharing
./scripts/start
```

Wenn Du das Startskript zum ersten Mal ausführst, was sehr viel Zeit in Anspruch nimmt, musst du wahrscheinlich der Windows-Firewall das OK geben, damit Docker funktioniert. 

### Bekannte Windows-Fehler

#### Allgemein

Wenn etwas nicht in Ordnung ist, überprüfe bitte in Deinem Task-Manager unter "Leistung", ob die Virtualisierung aktiviert ist und behebe gegebenenfalls Fehler.

#### Git-Fehler (bei WSL1)
 
 Wenn Git nicht gut arbeitet, mach bitte folgendes:
 ```
 cd foodsharing/bin
 tr -d '\15' < console > console
``` 
Stell sicher, dass du die `Konsole'-Datei nicht committest und besprich vielleicht weitere Schritte mit dem Team. 
 
#### ```[RuntimeException]```

Wenn du eine ```[RuntimeException]```, bekommst, lass ```./scripts/start``` noch einmal und wieder und wieder laufen, bis alles fertig ist.

#### yarn lint

Es gibt einen bekannten Fehler bezüglich yarn, siehe: https://github.com/yarnpkg/yarn/issues/7187 and https://github.com/yarnpkg/yarn/issues/7732 und https://github.com/yarnpkg/yarn/issues/7551

#### Veränderungen in js, vue etc. erscheinen nicht

Damit der webpack-dev-server Änderungen erkennt, musst du diesen watchOptions-Block zu ```client/serve.config.js``` hinzufügen:
```
[...]
module.exports = {
  [...]
  devServer: {
    watchOptions: {
      poll: true
    },
    [...]
```

Hinweis: Bitte achte darauf, diese Datei nicht nachträglich mit Ihren Änderungen zu übertragen.
