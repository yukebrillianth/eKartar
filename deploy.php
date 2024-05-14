<?php

namespace Deployer;

require 'recipe/laravel.php';
require 'contrib/npm.php';

// Config

set('bin/php', function () {
    return '/usr/bin/php'; // change
});
set('application', 'eKartar');
set('repository', 'https://github.com/yukebrillianth/eKartar.git');
set('keep_releases', 5);
set('ssh_multiplexing', true);

add('shared_files', ['.env']);
add('shared_dirs', ['storage']);
add('writable_dirs', []);

set('composer_options', '--verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader');
set('writable_mode', 'chmod');
set('writable_chmod_mode', '0775');

// Hosts

host('production')
    ->setHostname("nvdc1.duckdns.org")
    ->set('remote_user', 'deployer')
    ->set('port', 64000)
    ->set('branch', 'main')
    ->set('deploy_path', '/var/www/html/ekartar');

// Hooks

task('deploy:secrets', function () {
    file_put_contents(__DIR__ . '/.env', getenv('DOT_ENV'));
    upload('.env', get('deploy_path') . '/shared');
});

// NVM source location
set('nvm', 'source $HOME/.nvm/nvm.sh');

// Must be called in every run command related to npm
// Note: do not separate run command, as it is accounted as different shell session
set('use_nvm', function () {
    return '{{nvm}} && node --version && nvm use --lts --latest-npm';
});


task('npm:build:prod', function () {
    run('{{use_nvm}} && cd {{release_path}} && npm run build');
});

desc('Build assets');
task('deploy:build', [
    'npm:install',
    'npm:build:prod'
]);

task('db:migrate', artisan('migrate:install'));

task('backup:run', artisan('backup:run'));
task('backup:restore', artisan('backup:restore', ['--disk=b2', '--connection=pgsql']));

task('icon:cache', artisan('icons:cache'));

task('deploy', [
    'deploy:prepare',
    'deploy:secrets',       // Deploy secrets
    'deploy:vendors',
    'deploy:shared',
    'deploy:build',
    'artisan:storage:link',
    'icon:cache',
    'artisan:view:cache',
    'artisan:config:cache',
    'artisan:route:cache',
    'artisan:event:cache',
    'artisan:optimize',
    'deploy:publish',
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release. Uncomment below code if you want to migrate after deploy
// before('deploy:symlink', 'artisan:migrate');
after('deploy:cleanup', 'artisan:cache:clear');
after('deploy:cleanup', 'artisan:optimize');
// handle queue restarts
