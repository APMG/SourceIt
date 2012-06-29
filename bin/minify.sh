#!/bin/bash

# A simple script for minification, this will work for CSS files as well but you need to modify it of course

if [ -f public_html/sourceit.min.js ]
then
    rm public_html/sourceit.min.js
fi

# define the files to be minified
JS_FILES=("mustache.0.4.0-dev.js" "jquery.easing.1.3.js" "jquery.easing.compatibility.js" "user.js" "article.js" "sharing.js" "accuracy.js" "selection.js" "template.js" "comment.js" "popup.js" "sourceit.js" "script.js") 
ELEMENTS=${#JS_FILES[@]}

# minify each js file in the list specified
cd bin/yuicompressor-2.4.7/build
for (( i=0;i<$ELEMENTS;i++)); do
	JS_SOURCE_FILE="../../../public_html/js/"${JS_FILES[${i}]}
	JS_MIN_FILE="../../../public_html/js/min/"${JS_FILES[${i}]}
	java -cp yuicompressor-2.4.7.jar com.yahoo.platform.yui.compressor.YUICompressor $JS_SOURCE_FILE > ${JS_MIN_FILE%".js"}".min.js" 
done

# move back to the base directory
cd ../../..

# define all js files that should be combined
# NOTES: 
#	- the order of these files is critical...
#	- make sure you add ../ to all files that was not minimized by us...
JS_FILES=("../jquery-1.7.1.min.js" "../underscore-min.1.2.1.js" "mustache.0.4.0-dev.min.js" "jquery.easing.1.3.min.js" "jquery.easing.compatibility.min.js" "../jquery-ui.min.js" "../jquery.hoverIntent.min.js" "user.min.js" "article.min.js" "sharing.min.js" "accuracy.min.js" "selection.min.js" "template.min.js" "comment.min.js" "popup.min.js" "sourceit.min.js" "script.min.js")
ELEMENTS=${#JS_FILES[@]}

# echo each element in JS_FILES to a combine string... 
combine=
for (( i=0;i<$ELEMENTS;i++)); do
	combine=$combine" public_html/js/min/"${JS_FILES[${i}]}
done

# combine all minified files to one file using your combined string
cat $combine > public_html/js/sourceit.min.js