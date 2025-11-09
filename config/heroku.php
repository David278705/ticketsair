<?php

/**
 * Parse DATABASE_URL from Heroku and set individual DB_ environment variables
 */

if (getenv('DATABASE_URL')) {
    $url = parse_url(getenv('DATABASE_URL'));

    putenv("DB_CONNECTION=pgsql");
    putenv("DB_HOST=" . ($url['host'] ?? ''));
    putenv("DB_PORT=" . ($url['port'] ?? '5432'));
    putenv("DB_DATABASE=" . ltrim($url['path'] ?? '', '/'));
    putenv("DB_USERNAME=" . ($url['user'] ?? ''));
    putenv("DB_PASSWORD=" . ($url['pass'] ?? ''));
}
