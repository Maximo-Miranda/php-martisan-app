#!/bin/bash
set -e

# Create test database if it doesn't exist
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" <<-EOSQL
    SELECT 'CREATE DATABASE php_martina_app_test'
    WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'php_martina_app_test')\gexec
EOSQL

