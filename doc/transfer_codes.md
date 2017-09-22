# Bank Transfer codes

## Goals
* Allow for roughly 50 million different codes.
* Make the transfer code more resilient against OCR mistakes by excluding ambiguous characters and adding a checksum.
* Detect user mistakes that occurred while filling out the bank transfer form by adding a checksum. 

## Format

TODO

## Checksum algorithm

TODO

## Background information
### Why use bit-shifted MD5 instead of CRC32?
Both algorithms return a 32 bit integer. However, there are two CRC32 implementations, `CRC32` and `CRC32b` which give different results. To make the algorithm description more unambiguous we went with bit-shifted md5. 