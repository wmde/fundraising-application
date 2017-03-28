Vagrant.configure(2) do |config|
  config.vm.box = "bento/ubuntu-16.04"

  config.vm.box_check_update = false

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  # config.vm.network "forwarded_port", guest: 80, host: 8080
  config.vm.network "forwarded_port", guest: 8080, host: 31337

  # Share an additional folder to the guest VM. The first argument is
  # the path on the host to the actual folder. The second argument is
  # the path on the guest to mount the folder. And the optional third
  # argument is a set of non-required options.
  # config.vm.synced_folder "../data", "/vagrant_data"

  # Provider-specific configuration so you can fine-tune various
  # backing providers for Vagrant. These expose provider-specific options.
  # Example for VirtualBox:
  #
  config.vm.provider "virtualbox" do |vb|
     vb.memory = "1024"
     vb.cpus = 4
  end

  config.vm.provision "install_packages", type: "shell", path: "build/vagrant/install_packages.sh"
  config.vm.provision "install_composer", type: "shell", path: "build/vagrant/install_composer.sh"
  config.vm.provision "install_konto_check", type: "shell", path: "build/vagrant/installKontoCheck.sh"
  config.vm.provision "configure_app", type: "shell", path: "build/vagrant/configure_app.sh", env: { WIKI_PASSWD: ENV["WIKI_PASSWD"] }
  config.vm.provision "install_app", type: "shell", path: "build/vagrant/install_app.sh"
  config.vm.provision "configure_db", type: "shell", path: "build/vagrant/configureForMysql.sh", env: { DB_PASSWD: ENV["DB_PASSWD"] }

end
