find . -type f -name '*.zip' -exec sh -c '                                             root@CT135-Sites
  for file; do
    parent_dir=$(dirname "$file")
    parent_mtime=$(stat -c %y "$parent_dir")
    rm -f "$file"
    touch -d "$parent_mtime" "$parent_dir"
  done
' sh {} +
