#!/bin/bash
 
# importa tutti i file .sql presenti nella cartella db (sul server di tecweb)
# mi serve per testare il delete user


# Path configurations
ENV_FILE="../.env"
DB_DIR="../db"

# Check if .env file exists
if [ ! -f "$ENV_FILE" ]; then
    echo "Error: .env file not found at $ENV_FILE"
    exit 1
fi

# Read database variables from .env
export "$(grep -E '^(DB_HOST|DB_DATABASE|DB_USERNAME|DB_PASSWORD)=' "$ENV_FILE" | xargs)"

# Check if all required variables are set
if [ -z "$DB_HOST" ] || [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ] || [ -z "$DB_PASSWORD" ]; then
    echo "Error: Missing required database configuration in .env"
    exit 1
fi

# Check if database directory exists
if [ ! -d "$DB_DIR" ]; then
    echo "Error: Database directory $DB_DIR not found"
    exit 1
fi

# Enable case-insensitive globbing and nullglob
shopt -s nullglob

# Get SQL files sorted alphabetically
SQL_FILES=("$DB_DIR"/*.sql)

# Disable nullglob
shopt -u nullglob

# Check if any SQL files exist
if [ ${#SQL_FILES[@]} -eq 0 ]; then
    echo "Error: No .sql files found in $DB_DIR"
    exit 1
fi

# Import each SQL file
for FILE in "${SQL_FILES[@]}"; do
    echo "Importing $FILE..."
    if ! mysql --user="$DB_USERNAME" --password="$DB_PASSWORD" "$DB_DATABASE" < "$FILE"; then
        echo "Error: Failed to import $FILE"
        exit 1
    fi
done

echo "Successfully imported all SQL files"