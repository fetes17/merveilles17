home="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"


reduce() {
  # -interlace none (?)
  # -unsharp 0.25x0.08+8.3+0.045 (?)
  # -filter Triangle -define filter:support=2 -thumbnail $2 -dither None -posterize 136
  magick $1 -strip +dither -geometry $2 -define jpeg:fancy-upsampling=off -define png:compression-filter=5 -define png:compression-level=9 -define png:compression-strategy=1 -define png:exclude-chunk=all -colorspace sRGB -interlace Plane -quality 90% $3
}

list() {
  for srcfile in $home/../xml/*.xml
  do
    srcname="${srcfile##*/}"
    srcname="${srcname%.*}"
    dstfile="$home/$srcname.jpg"
    if test -f "$dstfile"; then
      # echo "$dstfile exists"
      continue
    fi
    cp $home/0.jpg $dstfile
  done
}

vignettes() {
  for srcfile in $home/*.jpg
  do
    srcname="${srcfile##*/}"
    srcname="${srcname%.*}"
    if [ "$srcname" = 0 ]; then
      continue
    fi
    dstfile="$home/S/$srcname,S.jpg"
    if [ "$srcfile" -ot "$dstfile" ]; then
      continue
    fi
    echo $dstfile
    reduce $srcfile 500x500 $dstfile
  done

  for srcfile in $home/S/*.jpg
  do
    srcname="${srcfile##*/}"
    srcname="${srcname%,S.*}"
    dstfile=$home/$srcname.jpg
    if [ -f $dstfile ]; then
      continue
    fi
    echo suppression de $srcfile
    rm $srcfile
  done
}

vignettes
