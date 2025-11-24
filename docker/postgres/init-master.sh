#!/bin/bash
set -e

# PostgreSQL Master Initialization Script
# =======================================

echo "Initializing PostgreSQL Master..."

# Create replication user
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
    -- Create replication user
    CREATE USER ${POSTGRES_REPLICATION_USER:-replicator} WITH REPLICATION ENCRYPTED PASSWORD '${POSTGRES_REPLICATION_PASSWORD:-replication_password}';

    -- Create replication slot for slave
    SELECT pg_create_physical_replication_slot('replica_slot');

    -- Grant necessary permissions
    GRANT ALL PRIVILEGES ON DATABASE ${POSTGRES_DB:-laravel} TO ${POSTGRES_USER:-postgres};
EOSQL

# Create archive directory
mkdir -p /var/lib/postgresql/data/archive
chown postgres:postgres /var/lib/postgresql/data/archive

echo "PostgreSQL Master initialization completed!"
