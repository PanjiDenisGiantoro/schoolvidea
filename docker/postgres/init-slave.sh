#!/bin/bash
set -e

# PostgreSQL Slave Initialization Script
# ======================================

echo "Initializing PostgreSQL Slave..."

# Wait for master to be ready
until pg_isready -h postgres-master -p 5432 -U ${POSTGRES_USER:-postgres}; do
    echo "Waiting for PostgreSQL Master to be ready..."
    sleep 2
done

echo "Master is ready. Starting base backup..."

# Stop PostgreSQL if running
pg_ctl -D /var/lib/postgresql/data stop -m fast 2>/dev/null || true

# Clean data directory
rm -rf /var/lib/postgresql/data/*

# Perform base backup from master
PGPASSWORD=${POSTGRES_REPLICATION_PASSWORD:-replication_password} pg_basebackup \
    -h postgres-master \
    -p 5432 \
    -U ${POSTGRES_REPLICATION_USER:-replicator} \
    -D /var/lib/postgresql/data \
    -Fp -Xs -P -R

# Create standby signal file
touch /var/lib/postgresql/data/standby.signal

# Set correct permissions
chown -R postgres:postgres /var/lib/postgresql/data
chmod 700 /var/lib/postgresql/data

echo "PostgreSQL Slave initialization completed!"
