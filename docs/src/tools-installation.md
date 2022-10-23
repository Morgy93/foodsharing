# Tools installation <!-- omit in toc -->

[Klicke hier für die **Deutsche Version**.](tools-installation_DE.md)

We use [Docker](https://www.docker.com/) containers and [Docker Compose](https://docs.docker.com/compose/) for our build setup. Hence, everything is build and run withing containers and you only need to have Docker and [Git](https://git-scm.com/) on your machine. There's no need to install additional development tools, as all the necessary tools are all bundled in the development containers.

You can use the Docker Compose setup, if you are using one of the following systems:

- Linux (64bit)
- OSX Yosemite 10.10.3 or higher
- Windows 10 Pro or higher

Descriptions for the different operating system follow:

- [Linux](#linux)
- [OSX Yosemite 10.10.3 or higher](#osx-yosemite-10103-or-higher)
- [Windows](#windows)
  - [Windows Subsystem for Linux 2 (WSL 2)](#windows-subsystem-for-linux-2-wsl-2)
  - [Windows Subsystem for Linux 1 (WSL 1)](#windows-subsystem-for-linux-1-wsl-1)
  - [Known Issues on Windows](#known-issues-on-windows)
    - [General](#general)
    - [git trouble (on WSL1)](#git-trouble-on-wsl1)
    - [yarn lint](#yarn-lint)
    - [Changes in js, vue etc. aren't showing up](#changes-in-js-vue-etc-arent-showing-up)

### Linux

In a recent Linux distribution you can probably install Docker just via the package manager of the distribution. Please note that at least version 1.6.0 of Docker Compose is needed. For Debian or Ubuntu the following command line would work for example:
```
sudo apt install docker.io docker-compose
```

In case that Docker or Docker Compose are not available in a recent version via the package manager of your distribution, please follow the documentation for the 
[Docker CE Installation](https://docs.docker.com/engine/installation/)
and the 
[Docker Compose Installation](https://docs.docker.com/compose/install/).

Make sure that your user is in the `docker` group. Otherwise, there would be access errors, when trying to run a docker container:

```
sudo usermod -aG docker $USER
```
Either logout and login again to reload the groups or run (for each shell...)
`su - $USER`

You should now be able to connect to Docker without errors. Try that by executing:
```
docker info
```

You can now continue with [getting & running the code](running-the-code.md).

### OSX Yosemite 10.10.3 or higher

Install [Docker for Mac](https://docs.docker.com/docker-for-mac/install/) ([direct link](https://download.docker.com/mac/stable/Docker.dmg)).

You can afterwards continue with [getting & running the code](running-the-code.md).

### Windows 

It should be setup unter the wsl (Windows Subsystem for Linux) environment. The folder can be accessed with:
```
\\wsl$
```

#### Windows Subsystem for Linux 2 (WSL 2)

* Install Ubuntu via the
  [Microsoft Store](https://www.microsoft.com/de-de/store/apps/windows)
  and start it
* Install Windows Terminal via the Microsoft Store and start it
* Open a new tab in Windows Terminal with Ubuntu
* Add the following to `~/.bashrc` with a text editor (e.g. nano)
```
export DOCKER_HOST=tcp://localhost:2375
export DOCKER_BUILDKIT=1
```
* Check in that your environment is active in: Docker Settings -> Resources -> WSL Integration

You can now continue with [getting & running the code](running-the-code.md).

#### Windows Subsystem for Linux 1 (WSL 1)

Install [Docker for Windows](https://docs.docker.com/docker-for-windows/install/) ([direct link](https://download.docker.com/win/stable/Docker%20Desktop%20Installer.exe)) and
[Git for Windows](https://git-scm.com/download/win).

If you are using Windows 10 Home, make sure you fulfill all [system requirements](https://docs.docker.com/docker-for-windows/install-windows-home/#system-requirements)
and then install both [Docker Desktop on Windows Home](https://docs.docker.com/docker-for-windows/install-windows-home/) and [Git for Windows](https://git-scm.com/download/win). 

It is important to grant docker access to C: (in the graphical docker interface: settings -> resources -> filesharing -> mark C, apply and restart)

You can test your docker in the command shell (e.g. cmd or powershell) with the command ```docker --version```. If it shows something, you're good to go.

Restart your Windows now.

There is a graphical user interface to administrate the repo, which is recommended for Git beginners.

But you can use the Git Bash shell just like in Linux to clone it:

```
git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing
```

After this command, your files will be found in the folder ```%UserProfile%\foodsharing```

To start the containers, use the Git Bash shell:
```
cd foodsharing
./scripts/start
```

The first time you run the start script, which takes a lot of time, you probably have to give the windows firewall the OK to let Docker work.

#### Known Issues on Windows

##### General

If something is wrong, please check in your task manager under "performance" if the virtualisation is activated and troubleshoot if necessary.

##### git trouble (on WSL1)
 
If git does not working well, please do:
```
cd foodsharing/bin
tr -d '\15' < console > console
``` 
Make sure not to commit the `console` file and maybe discuss further steps with the team. 
 
 - ```[RuntimeException]```

If you get a ```[RuntimeException]```, let ```./scripts/start``` run again and again and maybe even again until it's done.

##### yarn lint

There is a known bug concerning yarn, see: https://github.com/yarnpkg/yarn/issues/7187 and https://github.com/yarnpkg/yarn/issues/7732 and https://github.com/yarnpkg/yarn/issues/7551

 ##### Changes in js, vue etc. aren't showing up

In order to have the webpack-dev-server recognize changes you have to add this watchOptions block to ```client/serve.config.js```
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

Note: Please make sure not to commit this file afterwards with your changes.
