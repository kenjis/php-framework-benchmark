# -*- mode: ruby -*-
# vi: set ft=ruby :

$project_dir = "/srv/www"
Vagrant.require_version ">= 1.7.2"

ENV['VAGRANT_DEFAULT_PROVIDER'] = 'docker'

VAGRANTFILE_API_VERSION = "2"

if ! ENV['RAD_ENV']
    ENV['RAD_ENV'] = 'development'
end

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
    config.ssh.username = 'radphp'
    config.ssh.password = 'radphp'

    config.vm.define 'radphp', primary: true do |radphp|
        radphp.vm.synced_folder '.', "#{$project_dir}"
        radphp.vm.provision "shell", path: "./bin/provision.sh"
        radphp.vm.provider 'docker' do |d|
            d.name = "radphp-" + ENV['RAD_ENV']
            d.image = "radly/radphp-lepp"
            d.ports = ["80:80", "443:443", "8080:8080", "5432:5432"]
            d.has_ssh = true
            d.env = {
                'RAD_ENV' => ENV['RAD_ENV']
            }
        end
    end
end

