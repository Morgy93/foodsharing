# Running the code

We use Docker and Docker Compose.

You can use the Docker Compose setup if you are using one of the following:

- Linux (64bit)
- OSX Yosemite 10.10.3 or higher
- Windows 10 Pro or higher

If you are not using one of those, then try the Vagrant + Docker Compose setup.

## Linux

Install
[Docker CE](https://docs.docker.com/engine/installation/).

And make sure you have 
[Docker Compose](https://docs.docker.com/compose/install/) (at least version 1.6.0)
installed too (often comes with Docker).

The current version of Fedora 31 switched to using cgroupsV2 by default, which is not yet supported.

To disable v2 cgroups, run: 

```
sudo grubby --update-kernel=ALL --args="systemd.unified_cgroup_hierarchy=0"
```
And restart your machine.


If you cannot connect to Docker with your local user, then you may want to add yourself to the Docker group:

```
sudo usermod -aG docker $USER

# then either log in again to reload the groups
# or run (for each shell...)
su - $USER

# should now be able to connect without errors
docker info
```

Then:

```
git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing
cd foodsharing
./scripts/start
```

## OSX Yosemite 10.10.3 or higher

Install [Docker for Mac](https://docs.docker.com/docker-for-mac/install/) ([direct link](https://download.docker.com/mac/stable/Docker.dmg)).

Then:

```
git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing
cd foodsharing
./scripts/start
```

## Windows 

If you are using Windows 10 Pro or higher go with this link:

Install [Docker for Windows](https://docs.docker.com/docker-for-windows/install/) ([direct link](https://download.docker.com/win/stable/Docker%20for%20Windows%20Installer.exe)) and
[Git for Windows](https://git-scm.com/download/win).

If you are using Windows 10 Home or lower follow this instruction:

Install [Docker Toolbox for Windows] (https://docs.docker.com/toolbox/toolbox_install_windows/) and [Git for Windows](https://git-scm.com/download/win).

There is a graphical user interface to administrate the repo, which is recommended for Git beginners.

But you can use the Git Bash shell just like in Linux to clone it:

```
git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing
```

To start the containers, use the Git Bash shell:
```
cd foodsharing
./scripts/start
```

### Known Issues on Windows

 - Changes in js, vue etc. aren't showing up

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

## Vagrant

If you cannot use any of the above methods, then this should work with every common operation system.

However, we are less familiar with this solution, so we may be less able to support you.

Install
[VirtualBox](https://www.virtualbox.org/wiki/Downloads) and
[Vagrant](https://www.vagrantup.com/downloads.html).

Then:

```
git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing
cd foodsharing
vagrant up
```

### Daily work

`vagrant up` starts the machine and foodsharing project.

`vagrant halt` stops the virtual machine.

`vagrant ssh` connects to the virtual machine.

Once connected to the virtual machine, go to /vagrant with `cd /vagrant`.
This is where the foodsharing folder is mounted in the VM.
From there on, you can run all scripts with `./scripts/NAME`.

Note:
`./scripts/start` will always be executed, when you start the virtual machine with `vagrant up`.

There is a known bug when running VirtualBox + nginx that nginx serves files from a memory cache. If you encounter this problem, then it can probably be fixed by emptying the memory cache with ``sync; sudo sh -c "/bin/echo 3 > /proc/sys/vm/drop_caches"`` or even running this every second with ``watch -n 1 'sync; sudo sh -c "/bin/echo 3 > /proc/sys/vm/drop_caches"'``.
