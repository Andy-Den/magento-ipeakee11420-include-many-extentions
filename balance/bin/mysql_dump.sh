#!/bin/bash
# bash mysql_dump.sh -h HOST -u USER -p 'PASSWORD' -d 'DATABASE' -f 'NAME_OF_THE_DUMP_FILE'

# Dump database structure only.
dump_structure() {
    echo "Dump structure"
    mysqldump --host=${HOST} --user=${USER} --password=${PASSWORD} --single-transaction --no-data ${DATABASE} > ${DB_FILE}
}

# Dump all the data.
dump_content() {
    echo "Dump content"
    mysqldump --host=${HOST} --user=${USER} --password=${PASSWORD} --single-transaction ${DATABASE} ${IGNORED_TABLES_STRING} >> ${DB_FILE}
}

# Initialize variables.
HOST=127.0.0.1
USER=root
PASSWORD=
DATABASE=
DB_FILE=dump.sql

# Pass arguments.
while :
do
    case "$1" in
      -h | --host)
      HOST="$2"
      shift 2
      ;;
#      -h | --help)
#	  display_help  # Call your function
#	  # no shifting needed here, we're done.
#	  exit 0
#	  ;;
      -u | --user)
      USER="$2"
      shift 2
      ;;
      -p | --password)
      PASSWORD="$2"
      shift 2
      ;;
      -d | --database)
      DATABASE="$2"
      shift 2
      ;;
      -f | --file)
      DB_FILE="$2"
      shift 2
      ;;
#      -v | --verbose)
#      #  It's better to assign a string, than a number like "verbose=1"
#	  #  because if you're debugging the script with "bash -x" code like this:
#	  #
#	  #    if [ "$verbose" ] ...
#	  #
#	  #  You will see:
#	  #
#	  #    if [ "verbose" ] ...
#	  #
#          #  Instead of cryptic
#	  #
#	  #    if [ "1" ] ...
#	  #
#	  verbose="verbose"
#	  shift
#	  ;;
      --) # End of all options
      shift
      break;
      -*
      echo "Error: Unknown option: $1" >&2
      exit 1
      ;;
      *)  # No more options
      break
      ;;
    esac
done

# Tables with empty content.
EXCLUDED_TABLES=(
    log_customer
    log_quote
    log_summary
    log_summary_type
    log_url
    log_url_info
    log_visitor
    log_visitor_info
    log_visitor_online
)
IGNORED_TABLES_STRING=''
for TABLE in "${EXCLUDED_TABLES[@]}"
do :
   IGNORED_TABLES_STRING+=" --ignore-table=${DATABASE}.${TABLE}"
done

dump_structure
dump_content