#! /bin/bash
root_dir=.

function travel_dir(){
for element in `ls $1`;
do
   dir_or_file=$1"/"$element 
   if [ -d $dir_or_file ];then
        travel_dir $dir_or_file;
   elif [ -f $dir_or_file ]; then
	process_file $dir_or_file;	
   fi
done
}

function process_file(){
   file=$1;
   # suffix
   if [ ${file##*.} != "md" ];then
	return;
   fi

   l=0;
   code_lock=0
   cat $file | while read line;
   do
	let l+=1;
	#code segemnt
	if [[ $code_lock == '0' && ${line:0:3} == '```' ]];then
	    code_lock=1;
	    continue;
	fi	
	if [[ $code_lock == '1' && ${line:0:3} == '```' ]];then
	    code_lock=0;
	    continue;
	elif [[ $code_lock == '1' && ${line:0:3} != '```' ]];then
	    continue;
	fi	

	# headline
	i=0;
	while [[ ${line:i:1} == '#' ]];
	do
	    let i+=1;
	done
	if [[ i -gt 0 && ${line:i:1} != ' ' ]];then
	    j=i;
	    prefix=""
	    while [[ j -gt 0 ]];
	    do
		prefix+='#';
		let j-=1;
  	    done
	    line=$prefix' '${line:i};
	fi

	# newlines
        len=${#line};
	while [[ ${line:$len-1:1} == ' ' ]];
	do
	    let len-=1;
	done
	line=${line:0:len};
        line=$line"  ";
	
	# replace
	if [[ $line != '  ' ]];then
	    echo $line;
	    sed -i "${l}c $line" $file;
	fi
   done
}

travel_dir $root_dir;
