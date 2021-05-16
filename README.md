# TelephoneNumberGrader

A number grading script written for the telecoms sector. The script takes a UK phone number, or list of numbers, and returns a value based on patterns existing within the number. This is primarily for use by VoIP companies selling individual numbers.

## Requirements

- `php7`

## Installation

```
git clone https://github.com/EmeraldClaws/TelephoneNumberGrader.git
cd TelephoneNumberGrader/
```

## Running
##### Analysing a single number
```
$ ./run.php +441613066000
number,value,subValue,reasons
+441613066000,3,2,"Repeated digits"
```
##### Analysing multiple numbers from the command line
```
$ ./run.php 01613066000,01613066001
number,value,subValue,reasons
01613066000,3,2,"Repeated digits"
01613066001,0,0,""
```
##### Analysing a file containing '\n' separated numbers
```
$ ./run.php file.ext
number,value,subValue,reasons
01613066000,3,2,"Repeated digits"
01613066001,0,0,""

$ cat file.ext 
01613066000
01613066001

```

##### Accessing help
```
$ ./run
```

## Development

This repository is not under active development, though changes may be made. If you've spotted a bug, please add it to the bug tracker or send a pull request! 

If you have a cool idea for another value, send a pull request and we can go from there!

## License

This work is licenced under GPLv3
