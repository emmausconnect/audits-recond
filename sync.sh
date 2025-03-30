#!/bin/bash

# File to copy
source_file="index.php"
source_search="search.php"

# Array of ignored folder names
ignored_folders=("vendor" "another_folder_to_ignore")

# Loop through all first-level directories
for dir in */; do
  # Remove the trailing slash from the directory name
  dir_name=$(basename "$dir")

  # Check if the directory is in the ignored folders array
  if [[ " ${ignored_folders[@]} " =~ " ${dir_name} " ]]; then
    echo "Skipping ignored folder: $dir_name"
    continue
  fi

  # Check if it is a directory
  if [ -d "$dir" ]; then
    # Copy the file to the directory and rename it to index.php
    cp "$source_file" "$dir/index.php"
    cp "$source_search" "$dir/search.php"
    echo "Copied $source_file && $source_search to $dir"
  fi
done
