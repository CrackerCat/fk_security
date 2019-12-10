#! /bin/bash
dir=./
for file in $dir/*; 
do
    if [ -d $file ];
    then
	echo $file;
    fi
done
