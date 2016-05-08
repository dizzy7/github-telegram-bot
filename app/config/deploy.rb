logger.level = Logger::MAX_LEVEL

set :application, "set your application name here"
set :domain,      "dizzy.name"
set :deploy_to,   "/var/www/gh.dizzy.name"
set :app_path,    "app"
set :var_path,    "var"
set :symfony_console, "bin/console"
set :interactive_mode, false

#set :repository,  "https://github.com/dizzy7/github-watch.git"
#set :scm,         :git
set :user,        "www-data"
set :use_sudo,    false

set :repository, "."
set :scm, :none
set :deploy_via, :copy

set :model_manager, "doctrine"

role :web,        domain                         # Your HTTP server, Apache/etc
role :app,        domain, :primary => true       # This may be the same as your `Web` server

set  :keep_releases,  5
after "deploy",           "deploy:cleanup"

set :shared_files,      ["app/config/parameters.yml"]
set :shared_children,     [var_path + "/logs", "vendor"]

set :use_composer, true
set :composer_options,    "--optimize-autoloader"
set :dump_assetic_assets, false
set :writable_dirs,       ["var/cache", "var/logs"]

set :migrations_dir,      "src/AppBundle/Migrations"
after "symfony:project:clear_controllers", "symfony:doctrine:migrations:migrate"
before "deploy:rollback:revision", "app:rollback_migrations"

set :branch do
  default_tag = `git tag`.split("\n").last

  tag = Capistrano::CLI.ui.ask "Tag to deploy (make sure to push the tag first): [#{default_tag}] "
  tag = default_tag if tag.empty?
  tag
end

desc "Rollback database migrations (use when rollback code)"
  task :rollback_migrations do
    capifony_pretty_print "--> Rollback database migrations"
    if previous_release
      if migrations_dir
        prev_migrations_dir = File.join(previous_release, migrations_dir)
        prev_migrations = capture("#{try_sudo} ls -x #{prev_migrations_dir}").split.sort
        latest_prev_migration = 0
        if prev_migrations.any?
          latest_prev_migration = prev_migrations.last.gsub(/\D/, "")
        end

        curr_migrations_dir = File.join(current_path, migrations_dir)
        curr_migrations = capture("#{try_sudo} ls -x #{curr_migrations_dir}").split.sort
        latest_curr_migration = 0
        if curr_migrations.any?
          latest_curr_migration = curr_migrations.last.gsub(/\D/, "")
        end

        if latest_curr_migration != latest_prev_migration
          run "#{try_sudo} sh -c 'cd #{latest_release} && #{php_bin} #{symfony_console} doctrine:migrations:migrate #{latest_prev_migration} #{console_options}'"
        end
        capifony_puts_ok
      end
    else
      logger.important "no previous release to rollback to, rollback of migrations skipped"
    end
  end
