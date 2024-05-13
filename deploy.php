<?php

namespace Deployer;

require 'recipe/laravel.php';
require 'contrib/php-fpm.php';
require 'contrib/npm.php';

// Config

set('bin/php', function () {
    return '/usr/bin/php'; // change
});
set('application', 'eKartar');
set('repository', 'https://github.com/yukebrillianth/eKartar.git');
set('keep_releases', 5);

add('shared_files', ['.env']);
add('shared_dirs', ['storage']);
add('writable_dirs', [
    'bootstrap/cache',
    'storage',
    'storage/app',
    'storage/app/public',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
]);

set('composer_options', '--verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader');

// Hosts

host('production')
    ->setHostname("nvdc1.duckdns.org")
    ->set('remote_user', 'yukebrillianth')
    ->set('port', 64000)
    ->set('branch', 'main')
    ->set('deploy_path', '~/eKartar');

// Hooks

task('deploy:secrets', function () {
    file_put_contents(__DIR__ . '/.env', getenv('DOT_ENV'));
    upload('.env', get('deploy_path') . '/shared');
});

desc('Build assets');
task('deploy:build', [
    'npm:install',
]);

task('icon:cache', [
    '{{bin/php}} artisan icons:cache',
]);

task('deploy', [
    'deploy:prepare',
    'deploy:secrets',       // Deploy secrets
    'deploy:vendors',
    'deploy:shared',
    'deploy:build',
    'artisan:storage:link',
    // 'icons:cache',
    'artisan:view:cache',
    'artisan:config:cache',
    'artisan:route:cache',
    'artisan:event:cache',
    'artisan:optimize',
    'php-fpm:reload',
    'deploy:publish',
    'deploy:unlock',
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release. Uncomment below code if you want to migrate after deploy
before('deploy:symlink', 'artisan:migrate');
after('deploy:cleanup', 'artisan:cache:clear');
after('deploy:cleanup', 'artisan:optimize');
// handle queue restarts