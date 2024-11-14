# Trouve le premier fichier d'un sous dossier et applique son mtime a son parent
# Utile en cas d'erreur
find . -type d -exec sh -c '
  for dir; do
    first_file=$(find "$dir" -type f | head -n 1)
    if [ -n "$first_file" ]; then
      file_mtime=$(stat -c %y "$first_file")
      touch -d "$file_mtime" "$dir"
    fi
  done
' sh {} +
