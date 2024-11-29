#!/bin/bash

# File to copy
source_file="index-to-copy.php"
source_search="search.php"

# Loop through all first-level directories
for dir in */; do
  # Check if it is a directory
  if [ -d "$dir" ]; then
    # Copy the file to the directory and rename it to index.php
    cp "$source_file" "$dir/index.php"
    cp "$source_search" "$dir/search.php"
    echo "Copied $source_file to $dir/index.php"
  fi
done
