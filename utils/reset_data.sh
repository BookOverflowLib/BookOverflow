#!/bin/bash
#
# Deletes all db data. Use with caution!

SCRIPT_DIR=$(dirname "$(realpath "$0")")
#echo "Script path: $SCRIPT_PATH"
#ls "$SCRIPT_DIR/../data"
sudo bash -c "rm -rf $SCRIPT_DIR/../data/*"
#ls "$SCRIPT_DIR/../data"