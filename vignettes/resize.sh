


reduce() {
  # -interlace none (?)
  # -unsharp 0.25x0.08+8.3+0.045 (?)
  # -filter Triangle -define filter:support=2 -thumbnail $2 -dither None -posterize 136
  magick $1 -strip +dither -geometry $2 -define jpeg:fancy-upsampling=off -define png:compression-filter=5 -define png:compression-level=9 -define png:compression-strategy=1 -define png:exclude-chunk=all -colorspace sRGB -interlace Plane -quality 90% $3
}

list() {
  for srcfile in ../xml/*.xml
  do
    srcname="${srcfile##*/}"
    srcname="${srcname%.*}"
    cp 0.jpg $srcname.jpg
  done

}

