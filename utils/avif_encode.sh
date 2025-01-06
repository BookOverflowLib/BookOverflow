#!/bin/bash
#
# Encodes all PNG and JPG files from source directory to AVIF format in destination directory.

set -Eeuo pipefail
trap 'echo "Error on line $LINENO: $BASH_COMMAND"' ERR

encode_file() {
    local input_file="$1"
    local src_dir="$2"
    local dest_dir="$3"
    local relative_path=${input_file#"$src_dir/"}
    local avif_file="$dest_dir/${relative_path%.*}.avif"

    if [ -f "$avif_file" ]; then
        echo "Skipping $input_file, $avif_file already exists"
        return
    fi

    mkdir -p "$(dirname "$avif_file")"
    echo "Encoding $input_file to $avif_file"
    avifenc -q 70 -j all -s 0 "$input_file" "$avif_file"
}

export -f encode_file

main() {
    local src_dir="$1"
    local dest_dir="$2"
    local files=()

    if [ -z "$src_dir" ] || [ -z "$dest_dir" ]; then
        echo "Usage: $0 <source_directory> <destination_directory>"
        exit 1
    fi
    if [ ! -d "$src_dir" ]; then
        echo "Error: $src_dir is not a directory"
        exit 1
    fi
    
    mkdir -p "$dest_dir"

    while IFS= read -r -d $'\0' file; do
        files+=("$file")
    done < <(find "$src_dir" -type f \( -iname "*.png" -o -iname "*.jpg" \) -print0)

    # encodes pngs and jpgs
    find "$src_dir" -type f \( -iname "*.png" -o -iname "*.jpg" \) -print0 | \
        parallel -0 -j 2 encode_file {} "$src_dir" "$dest_dir"
    # copy over remaining files (icos, svgs, ...)
    rsync -av --exclude='*.png' --exclude='*.jpg' "$src_dir/" "$dest_dir/"
}

main "$@"