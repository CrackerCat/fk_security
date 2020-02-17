# Set the minimum version of CMake that can be used
# To find the cmake version run
# $ cmake --version
cmake_minimum_required(VERSION 2.8)

# Set the project name
project (pwn_test)

set(CMAKE_C_FLAGS "${CMAKE_C_FLAGS} -g -O0")

include_directories()

link_directories()


#create a sources variable with a link to all c files to compile
file(GLOB SOURCES RELATIVE ${CMAKE_CURRENT_SOURCE_DIR}/ *.c )

# Add executable
foreach( sourcefile IN LISTS SOURCES)
  string( REPLACE ".c" "" program ${sourcefile} )
  add_executable( ${program}  ${sourcefile} )
endforeach( sourcefile )
