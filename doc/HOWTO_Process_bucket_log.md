# How to convert the bucket log to CSV

The file `var/bucket.log` contains the names of the "testing buckets" for each donation and membership application. This documentation shows how to use the command line program [`jq`](https://stedolan.github.io/jq/) to convert the nested JSON entries into a CSV file.

<!-- toc -->

* [Example log entry](#example-log-entry)
* [Selecting specific fields from the input](#selecting-specific-fields-from-the-input)
* [Formatting the date](#formatting-the-date)
* [Filtering entries](#filtering-entries)
* [Processing the CSV output](#processing-the-csv-output)
    * [Convert to semicolon-separated format](#convert-to-semicolon-separated-format)
    * [Convert to comma-separated format](#convert-to-comma-separated-format)
    * [Add headers to the export](#add-headers-to-the-export)
    * [Add bucket information to another CSV export file](#add-bucket-information-to-another-csv-export-file)

<!-- tocstop -->

## Example log entry

```JSON
{
  "date": "2018-08-28T10:15:59.000+00:00",
  "eventName": "donationCreated",
  "metadata": {
    "id": 18
  },
  "buckets": {
    "donation_address": "optional",
    "skins": "cat17"
  }
}
```

## Selecting specific fields from the input

    jq -r '[.date, .eventName, .metadata.id, .buckets.donation_address] | @csv' var/buckets.log

The first part of the expression selects specific fields from each JSON object in the log file and writes them to an array structure. Each field name needs to start with a dot, you can reach properties nested in objects by separating the property with a dot. You can put the fields in any order you like in the array.

The part `| @csv` converts the array into a CSV string. The `-r` option of `jq` avoids the quoting of the CSV string.

## Formatting the date

The log file contains dates formatted in [RFC 3339](https://www.ietf.org/rfc/rfc3339.txt) format, including microseconds and the timezone offset. To cut off those parts and replace the `T` separator between date and time, use the following command:

    jq -r '[(.date |split(".")[0]|gsub("T"; " ")), .eventName, .metadata.id, .buckets.donation_address] | @csv' var/buckets.log

## Filtering entries
If you want to output specific log entries and drop the others, use the `select` filter like this:

    jq -r 'select( .eventName == "donationCreated") | [(.date |split(".")[0]|gsub("T"; " ")), .eventName, .metadata.id, .buckets.donation_address] | @csv' var/buckets.log

## Processing the CSV output

Use the [`xsv`](https://github.com/BurntSushi/xsv) tool to change the generated CSV or join it with other CSV.

### Convert to semicolon-separated format
Convert the delimiter from comma to semicolon:

    xsv fmt -t ';' buckets.csv

### Convert to comma-separated format
Convert the delimiter from semicolon to comma:

    xsv fmt -d ';' buckets.csv

### Add headers to the export

    echo "date,eventname,donation_id,bucket" | xsv cat rows - buckets.csv

The number of columns you specify in the `echo` string must match the number of columns in the CSV file.

### Add bucket information to another CSV export file

For this to work, all CSV files must have a header row so you can reference the join columns

    xsv join --left id donations.csv donation_id buckets.csv

The `--left` option will output rows from `donations.csv` even when there is no matching donation ID in `buckets.csv`.

If both files are in semicolon-separated format, use the flag `-d ';'`
