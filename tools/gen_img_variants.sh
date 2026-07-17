#!/bin/bash
# Pre-generate responsive width variants (160/480/768/1024) next to every post photo.
# Idempotent: skips existing variants and targets >= the source width. Re-run after
# uploading photos for a new post (or add it to the publish routine).
set -u
SRC="$HOME/chilove/public/assets/img/posts"
made=0 skipped=0 failed=0
shopt -s nullglob
for f in "$SRC"/*/*.webp "$SRC"/*/*.jpg "$SRC"/*/*.jpeg "$SRC"/*/*.png; do
  name="$(basename "$f")"; stem="${name%.*}"; ext="${name##*.}"
  case "$stem" in *-160w|*-480w|*-768w|*-1024w) continue ;; esac
  w="$(identify -format '%w' "$f" 2>/dev/null)" || { echo "cannot read: $f"; failed=$((failed+1)); continue; }
  for t in 160 480 768 1024; do
    out="${f%/*}/${stem}-${t}w.${ext}"
    if [ -e "$out" ]; then skipped=$((skipped+1)); continue; fi
    if [ "$w" -gt "$t" ]; then
      if convert "$f" -resize "${t}x" -quality 80 "$out" 2>/dev/null && [ -s "$out" ]; then
        made=$((made+1))
      else
        rm -f "$out"; echo "convert failed: $f -> ${t}w"; failed=$((failed+1))
      fi
    fi
  done
done
echo "variants made=$made skipped_existing=$skipped failed=$failed"
[ "$failed" -eq 0 ]
