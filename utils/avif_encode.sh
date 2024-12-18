#!/bin/bash
#
# Encodes all PNG and JPG files in the directory passed as argument to AVIF format.

set -Eeuo pipefail
trap 'echo "Error on line $LINENO: $BASH_COMMAND"' ERR

encode_file() {
    local input_file="$1"
    local dir="$2"
    local relative_path=${input_file#"$dir/"}
    local avif_file="$dir/avif/${relative_path%.*}.avif"

    if [ -f "$avif_file" ]; then
        echo "Skipping $input_file, $avif_file already exists"
        return
    fi

    mkdir -p "$dir/avif/$(dirname "$relative_path")"
    echo "Encoding $input_file to $avif_file"
    avifenc -q 70 -j all -s 0 "$input_file" "$avif_file"
}

export -f encode_file

main() {
    local dir="$1"
    local files=()

    if [ -z "$dir" ]; then
        echo "Usage: $0 <directory>"
        exit 1
    fi
    if [ ! -d "$dir" ]; then
        echo "Error: $dir is not a directory"
        exit 1
    fi
    if [ ! -d "$dir/avif" ]; then
        mkdir "$dir/avif" # saved in a separate dir for now
    fi

    while IFS= read -r -d $'\0' file; do
        files+=("$file")
    done < <(find "$dir" -type f \( -iname "*.png" -o -iname "*.jpg" \) -print0)

    find "$dir" -type f \( -iname "*.png" -o -iname "*.jpg" \) -print0 | \
        parallel -0 -j 2 encode_file {} "$dir"
}

main "$@"